<?php
include 'db_config.php';
$res = $conn->query('SELECT book_id, title, author FROM book LIMIT 10');
$rows = [];
while ($row = $res->fetch_assoc()) {$rows[] = $row;}
echo json_encode($rows, JSON_PRETTY_PRINT);
?>
