<?php
header('Content-Type: application/json');
error_reporting(0);
include 'db_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // 1. Basic Data
        $book_id = $_POST['book_id'] ?? null; // Check if we are editing
        $isbn = $_POST['isbn'] ?? '';
        $title = $_POST['title'] ?? '';
        $author = $_POST['author'] ?? '';
        $description = $_POST['description'] ?? '';
        $image_url = $_POST['image_url'] ?? '';
        $edition = $_POST['edition'] ?? '';
        $price = (float) ($_POST['price'] ?? 0);
        $num_copies = (int) ($_POST['copies'] ?? 1);

        // 2. Date Handling (Year to YYYY-MM-DD)
        $raw_pub_date = $_POST['pub_date'] ?? '';
        if (preg_match('/^\d{4}$/', $raw_pub_date)) {
            $pub_date = $raw_pub_date;
        } elseif ($raw_pub_date && strtotime($raw_pub_date) !== false) {
            $pub_date = date('Y', strtotime($raw_pub_date));
        } else {
            $pub_date = null;
        }

        // 3. PUBLISHER LOGIC
        $pub_name = trim($_POST['publisher_name'] ?? '');
        if ($pub_name === '') {
            $pub_name = 'Unknown Publisher';
        }
        $stmt_pub = $conn->prepare("SELECT publisher_id FROM Publisher WHERE publisher_name = ?");
        $stmt_pub->bind_param("s", $pub_name);
        $stmt_pub->execute();
        $res_pub = $stmt_pub->get_result();

        if ($row_pub = $res_pub->fetch_assoc()) {
            $publisher_id = $row_pub['publisher_id'];
        } else {
            $stmt_ins_pub = $conn->prepare("INSERT INTO Publisher (publisher_name) VALUES (?)");
            $stmt_ins_pub->bind_param("s", $pub_name);
            $stmt_ins_pub->execute();
            $publisher_id = $conn->insert_id;
        }

        // 4. AUTHOR LOGIC
        $author_full = trim($_POST['author'] ?? '');
        if ($author_full === '') {
            $author_full = 'Unknown Author';
        }

        $author_parts = preg_split('/\s+/', $author_full);
        if (count($author_parts) === 1) {
            $first_name = $author_parts[0];
            $last_name = 'Unknown';
        } else {
            $last_name = array_pop($author_parts);
            $first_name = implode(' ', $author_parts);
        }

        $stmt_author = $conn->prepare("SELECT author_id FROM author WHERE first_name = ? AND last_name = ?");
        $stmt_author->bind_param("ss", $first_name, $last_name);
        $stmt_author->execute();
        $res_author = $stmt_author->get_result();

        if ($row_author = $res_author->fetch_assoc()) {
            $author_id = $row_author['author_id'];
        } else {
            $stmt_ins_author = $conn->prepare("INSERT INTO author (first_name, last_name) VALUES (?, ?)");
            $stmt_ins_author->bind_param("ss", $first_name, $last_name);
            $stmt_ins_author->execute();
            $author_id = $conn->insert_id;
            $stmt_ins_author->close();
        }
        $stmt_author->close();

        // 5. Other Foreign Keys
        $admin_id = 1;
        $genre_id = (int) ($_POST['genre_id'] ?? 1);

        if ($book_id) {
            // --- UPDATE LOGIC ---
            $sql = "UPDATE Book SET 
                    ISBN = ?, title = ?, description = ?, image_url = ?, 
                    publication_date = ?, edition = ?, price = ?, 
                    Genre_genre_id = ?, Publisher_publisher_id = ? 
                    WHERE book_id = ?";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param(
                "ssssssdiii",
                $isbn,
                $title,
                $description,
                $image_url,
                $pub_date,
                $edition,
                $price,
                $genre_id,
                $publisher_id,
                $book_id
            );

            if ($stmt->execute()) {
                // Update author assignment
                $stmt_del = $conn->prepare("DELETE FROM book_author_assignment WHERE Book_book_id = ?");
                $stmt_del->bind_param("i", $book_id);
                $stmt_del->execute();
                $stmt_del->close();

                $stmt_assign = $conn->prepare("INSERT INTO book_author_assignment (Book_book_id, Author_author_id) VALUES (?, ?)");
                $stmt_assign->bind_param("ii", $book_id, $author_id);
                $stmt_assign->execute();
                $stmt_assign->close();

                // If copies were changed, add only extra copies when increasing the total count
                $newCopyCount = intval($_POST['copies'] ?? 0);
                if ($newCopyCount > 0) {
                    $currentCopiesResult = $conn->query("SELECT COUNT(*) AS total_copies FROM Book_Copy WHERE Book_book_id = " . intval($book_id));
                    $currentCopiesRow = $currentCopiesResult->fetch_assoc();
                    $currentCopies = intval($currentCopiesRow['total_copies'] ?? 0);

                    if ($newCopyCount > $currentCopies) {
                        $copiesToAdd = $newCopyCount - $currentCopies;
                        $stmt_copy = $conn->prepare("INSERT INTO Book_Copy (Book_book_id, status, `condition`) VALUES (?, 'Available', 'New')");
                        $stmt_copy->bind_param("i", $book_id);
                        for ($i = 0; $i < $copiesToAdd; $i++) {
                            $stmt_copy->execute();
                        }
                        $stmt_copy->close();
                    }
                }

                echo json_encode(["status" => "success", "message" => "Book updated"]);
            } else {
                throw new Exception("Update failed: " . $stmt->error);
            }

        } else {
            // --- INSERT LOGIC ---
            $sql = "INSERT INTO Book (ISBN, title, description, image_url, publication_date, edition, price, Admin_user_id, Genre_genre_id, Publisher_publisher_id) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param(
                "ssssssdiii",
                $isbn,
                $title,
                $description,
                $image_url,
                $pub_date,
                $edition,
                $price,
                $admin_id,
                $genre_id,
                $publisher_id
            );

            if ($stmt->execute()) {
                $new_book_id = $conn->insert_id;
                $numCopies = intval($_POST['copies'] ?? 0);

                $stmt_assign = $conn->prepare("INSERT INTO book_author_assignment (Book_book_id, Author_author_id) VALUES (?, ?)");
                $stmt_assign->bind_param("ii", $new_book_id, $author_id);
                $stmt_assign->execute();
                $stmt_assign->close();

                // Only run if there is at least one copy to add
                if ($numCopies > 0) {
                    // 1. Prepare the statement ONCE outside the loop
                    $stmt_copy = $conn->prepare("INSERT INTO Book_Copy (Book_book_id, status, `condition`) VALUES (?, 'Available', 'New')");

                    // 2. Bind the variable ONCE outside the loop
                    $stmt_copy->bind_param("i", $new_book_id);

                    // 3. Loop only the execution
                    for ($i = 0; $i < $numCopies; $i++) {
                        $stmt_copy->execute();
                    }

                    // 4. Close the statement to free up resources
                    $stmt_copy->close();
                }

                echo json_encode(["status" => "success", "message" => "Book added"]);
            } else {
                throw new Exception("Insert failed: " . $stmt->error);
            }
        }
    } catch (Exception $e) {
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
}
?>