<?php
include 'db_config.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $conn->prepare("SELECT b.*, 
                            COALESCE(NULLIF(p.publisher_name, ''), CONCAT('Publisher #', b.Publisher_publisher_id)) AS publisher_name, 
                            COALESCE((SELECT CONCAT(a.first_name, ' ', a.last_name)
                                       FROM author a
                                       JOIN book_author_assignment baa ON a.author_id = baa.Author_author_id
                                       WHERE baa.Book_book_id = b.book_id LIMIT 1), '') AS author_name,
                            COALESCE((SELECT COUNT(*) FROM Book_Copy WHERE Book_book_id = b.book_id), 0) AS copies 
                            FROM Book b 
                            LEFT JOIN Publisher p ON b.Publisher_publisher_id = p.publisher_id 
                            WHERE b.book_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($book = $result->fetch_assoc()) {
        echo json_encode($book);
    } else {
        echo json_encode(['error' => 'Book not found']);
    }
}
?>