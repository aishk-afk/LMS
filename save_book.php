<?php
header('Content-Type: application/json');
$conn = new mysqli("localhost", "root", "", "lms_db");

if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Connection failed"]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Collect data (Removed call_num)
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

    // 2. Foreign Key Placeholders
    $pub_id = "PUB-001";
    $admin_id = "ADM-001";
    $genre_id = "G01";
    $category_id = "C01";

    // 3. THE SQL (Exactly 14 columns now)
    $sql = "INSERT INTO book (
        book_id, ISBN, title, edition, description, 
        image_url, publication_date, publisher_name, price, 
        copies, Publisher_publisher_id, Admin_user_id, Genre_genre_id, Category_category_id
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        echo json_encode(["status" => "error", "message" => "SQL Prepare Error: " . $conn->error]);
        exit;
    }

    // 4. BIND PARAM (14 types: 8 strings, 1 double, 5 strings)
    // s = string, d = double, i = integer
    $stmt->bind_param(
        "ssssssssdissss",
        $book_id,
        $isbn,
        $title,
        $edition,
        $description,
        $image_url,
        $pub_date,
        $pub_name,
        $price,
        $copies,
        $pub_id,
        $admin_id,
        $genre_id,
        $category_id
    );

    if ($stmt->execute()) {
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Database Error: " . $stmt->error]);
    }
    $stmt->close();
    $conn->close();
}
