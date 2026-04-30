<?php
header('Content-Type: application/json');
error_reporting(0); // Prevents random text from breaking your JSON response
include 'db_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // 1. Get data from the form
        $isbn = $_POST['isbn'] ?? '';
        $title = $_POST['title'] ?? '';
        $description = $_POST['description'] ?? '';
        $image_url = $_POST['image_url'] ?? '';

        // Get the raw input from the form
        $raw_pub_date = $_POST['pub_date'] ?? '';

        // Check if it's just a 4-digit year (e.g., 2009)
        if (strlen($raw_pub_date) == 4 && is_numeric($raw_pub_date)) {
            // Append January 1st to make it a valid YYYY-MM-DD
            $pub_date = $raw_pub_date . "-01-01";
        } else {
            $pub_date = $raw_pub_date;
        }

        // Now use $pub_date in your $stmt->bind_param(...)
        $edition = $_POST['edition'] ?? '';
        $price = (float) ($_POST['price'] ?? 0);

        // 2. Foreign Keys (These must match the ones you just inserted in phpMyAdmin)
        $admin_id = 1;
        $publisher_id = 1;
        $genre_id = (int) ($_POST['genre_id'] ?? 1);

        // 3. The SQL 
        $sql = "INSERT INTO Book (ISBN, title, description, image_url, publication_date, edition, price, Admin_user_id, Genre_genre_id, Publisher_publisher_id) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        if (!$stmt)
            throw new Exception("Prepare failed: " . $conn->error);

        // 4. Bind 10 parameters: 7 strings(s), 1 double(d), 2 integers(i)
        // types: s s s s s s d i i i
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
            $num_copies = (int) ($_POST['copies'] ?? 1);

            // 5. Create the physical copies in Book_Copy table
            for ($i = 0; $i < $num_copies; $i++) {
                $conn->query("INSERT INTO Book_Copy (Book_book_id, status, `condition`) VALUES ($new_book_id, 'Available', 'New')");
            }
            echo json_encode(["status" => "success"]);
        } else {
            throw new Exception("Insert failed: " . $stmt->error);
        }
    } catch (Exception $e) {
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
}
?>