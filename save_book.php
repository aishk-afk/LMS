<?php
header('Content-Type: application/json');
include 'db_config.php'; // Use your config file

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $book_id = "BK-" . rand(10000, 99999);
    $isbn = $_POST['isbn'] ?? '';
    $title = $_POST['title'] ?? '';
    $edition = $_POST['edition'] ?? '';
    $description = $_POST['description'] ?? '';
    $image_url = $_POST['image_url'] ?? '';
    $pub_date = $_POST['pub_date'] ?? '';
    $pub_name = $_POST['publisher_name'] ?? '';
    $price = (float) ($_POST['price'] ?? 0);
    $copies = (int) ($_POST['copies'] ?? 1);
    
    // Foreign Key Placeholders (Make sure these IDs exist in their tables!)
    $pub_id = "Pub-001"; 
    $admin_id = "ADMIN001";
    $genre_id = $_POST['genre_id'] ?? 'G01'; 

    $sql = "INSERT INTO book (
        book_id, ISBN, title, edition, description, 
        image_url, publication_date, price, 
        copies, Publisher_publisher_id, Admin_user_id, Genre_genre_id
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);

    // BIND PARAM (13 types: 8 s, 1 d, 4 s)
    $stmt->bind_param(
        "sssssssdisss", 
        $book_id, $isbn, $title, $edition, $description, 
        $image_url, $pub_date, $price, 
        $copies, $pub_id, $admin_id, $genre_id
    );

    if ($stmt->execute()) {
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => $stmt->error]);
    }
}
?>