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
        $description = $_POST['description'] ?? '';
        $image_url = $_POST['image_url'] ?? '';
        $edition = $_POST['edition'] ?? '';
        $price = (float) ($_POST['price'] ?? 0);
        $num_copies = (int) ($_POST['copies'] ?? 1);

        // 2. Date Handling (Year to YYYY-MM-DD)
        $raw_pub_date = $_POST['pub_date'] ?? '';
        if (strlen($raw_pub_date) == 4 && is_numeric($raw_pub_date)) {
            $pub_date = $raw_pub_date . "-01-01";
        } else {
            $pub_date = $raw_pub_date;
        }

        // 3. PUBLISHER LOGIC
        $pub_name = $_POST['publisher_name'] ?? 'Unknown Publisher';
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

        // 4. Other Foreign Keys
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
                // Create Copies
                // Create Copies
                $numCopies = intval($_POST['copies']); // If this is 0, the loop below won't run

                for ($i = 0; $i < $numCopies; $i++) {
                    // FIX: Changed $newBookId to $new_book_id to match your code above
                    $stmt_copy = $conn->prepare("INSERT INTO Book_Copy (Book_book_id, status, `condition`) VALUES (?, 'Available', 'New')");
                    $stmt_copy->bind_param("i", $new_book_id);
                    $stmt_copy->execute();
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