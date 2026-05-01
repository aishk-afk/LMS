<?php
include 'db_config.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM Book WHERE book_id = ?");
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