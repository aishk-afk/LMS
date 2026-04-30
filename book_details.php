<?php
include 'db_config.php';

$book_id = isset($_GET['id']) ? $_GET['id'] : die("Book ID not specified.");

$sql = "SELECT book.*, 
               publisher.publisher_name,
               genre.genre_name
        FROM book 
        LEFT JOIN publisher ON book.Publisher_publisher_id = publisher.publisher_id
        LEFT JOIN genre ON book.Genre_genre_id = genre.genre_id
        WHERE book.book_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $book_id);
$stmt->execute();
$result = $stmt->get_result();
$book = $result->fetch_assoc();

if (!$book) { die("Book not found."); }
?>