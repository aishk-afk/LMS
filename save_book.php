<?php
header('Content-Type: application/json');
error_reporting(0); 
include 'db_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // 1. Basic Data
        $isbn = $_POST['isbn'] ?? '';
        $title = $_POST['title'] ?? '';
        $description = $_POST['description'] ?? '';
        $image_url = $_POST['image_url'] ?? '';
        $edition = $_POST['edition'] ?? '';
        $price = (float) ($_POST['price'] ?? 0);

        // 2. Date Handling (Year to YYYY-MM-DD)
        $raw_pub_date = $_POST['pub_date'] ?? '';
        if (strlen($raw_pub_date) == 4 && is_numeric($raw_pub_date)) {
            $pub_date = $raw_pub_date . "-01-01";
        } else {
            $pub_date = $raw_pub_date;
        }

        // 3. PUBLISHER LOGIC (Fixing the ID issue)
        $pub_name = $_POST['publisher_name'] ?? 'Unknown Publisher';
        
        // Check if publisher exists
        $stmt_pub = $conn->prepare("SELECT publisher_id FROM Publisher WHERE publisher_name = ?");
        $stmt_pub->bind_param("s", $pub_name);
        $stmt_pub->execute();
        $res_pub = $stmt_pub->get_result();

        if ($row_pub = $res_pub->fetch_assoc()) {
            $publisher_id = $row_pub['publisher_id'];
        } else {
            // Insert new publisher
            $stmt_ins_pub = $conn->prepare("INSERT INTO Publisher (publisher_name) VALUES (?)");
            $stmt_ins_pub->bind_param("s", $pub_name);
            $stmt_ins_pub->execute();
            $publisher_id = $conn->insert_id;
        }

        // 4. Other Foreign Keys
        $admin_id = 1; 
        $genre_id = (int) ($_POST['genre_id'] ?? 1);

        // 5. Insert Book
        $sql = "INSERT INTO Book (ISBN, title, description, image_url, publication_date, edition, price, Admin_user_id, Genre_genre_id, Publisher_publisher_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        if (!$stmt) throw new Exception("Prepare failed: " . $conn->error);

        $stmt->bind_param("ssssssdiii", 
            $isbn, $title, $description, $image_url, 
            $pub_date, $edition, $price, 
            $admin_id, $genre_id, $publisher_id
        );

        if ($stmt->execute()) {
            $new_book_id = $conn->insert_id;
            $num_copies = (int) ($_POST['copies'] ?? 1);

            // 6. Create Copies
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