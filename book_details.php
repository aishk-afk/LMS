<?php
include 'db_config.php'; // Use your shared connection file

$book_id = isset($_GET['id']) ? $_GET['id'] : die("Book ID not specified.");

$sql = "SELECT book.*, publisher.publisher_name 
        FROM book 
        JOIN publisher ON book.Publisher_publisher_id = publisher.publisher_id 
        WHERE book.book_id = ?";
        
// Fetch full book details
$stmt = $conn->prepare("SELECT * FROM book WHERE book_id = ?");
$stmt->bind_param("i", $book_id);
$stmt->execute();
$result = $stmt->get_result();
$book = $result->fetch_assoc();

if (!$book) { die("Book not found."); }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $book['title']; ?> - Details</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="css/sidebar.css">
    <style>
        .details-container { padding: 40px; display: flex; gap: 40px; background: #fff; border-radius: 15px; margin-top: 20px; }
        .details-left img { width: 300px; border-radius: 10px; box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
        .details-right { flex: 1; }
        .meta-grid { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; background: #f8fafc; padding: 20px; border-radius: 10px; margin-top: 30px; }
        .meta-item label { display: block; font-size: 0.75rem; color: #94a3b8; text-transform: uppercase; font-weight: 700; }
        .meta-item span { font-weight: 600; color: #1e293b; }
        .back-link { text-decoration: none; color: #64748b; display: flex; align-items: center; gap: 8px; margin-bottom: 20px; }
    </style>
</head>
<body class="admin-container">
    <?php include 'sidebar.php'; // It is cleaner to put your sidebar in a separate file and include it ?>
    
    <main class="main-content">
        <a href="admin_catalog.php" class="back-link">← Back to Catalog</a>
        
        <div class="details-container">
            <div class="details-left">
                <img src="<?php echo $book['image_url']; ?>" alt="Cover">
            </div>
            
            <div class="details-right">
                <span class="badge" style="background:#e0e7ff; color:#4338ca; padding:4px 12px; border-radius:20px; font-size:0.8rem;">
                    <?php echo $book['Genre_genre_id']; ?>
                </span>
                <h1 style="font-size: 2.5rem; margin: 15px 0 5px;"><?php echo $book['title']; ?></h1>
                <p style="color: #64748b; font-size: 1.1rem;">By <?php echo $book['author']; ?></p>
                
                <p style="margin-top: 25px; line-height: 1.6; color: #475569;">
                    <?php echo $book['description']; ?>
                </p>

                <div class="meta-grid">
                    <div class="meta-item"><label>Publisher</label><span><?php echo $book['publisher_name']; ?></span></div>
                    <div class="meta-item"><label>Copyright Year</label><span><?php echo $book['pub_date']; ?></span></div>
                    <div class="meta-item"><label>ISBN</label><span><?php echo $book['isbn']; ?></span></div>
                    <div class="meta-item"><label>Edition</label><span><?php echo $book['edition']; ?></span></div>
                    <div class="meta-item"><label>Status</label><span><?php echo ($book['copies'] > 0) ? 'Available' : 'Unavailable'; ?></span></div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>