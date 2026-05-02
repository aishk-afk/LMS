<?php
session_start();
$userId = $_GET['user_id'] ?? $_SESSION['user_id'] ?? '';
$role = $_SESSION['user_role'] ?? 'member';
$is_admin = (strtolower($role) === 'admin');
// 1. Database Connection & Logic
include 'db_config.php';

// Get the ID from URL and sanitize it
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    die("Invalid Book ID.");
}

// Optimized Query: Fetches Book details, Publisher name, and Genre name
$query = "SELECT b.*, 
          p.publisher_name, 
          g.genre_name,
          (SELECT COUNT(*) FROM Book_Copy WHERE Book_book_id = b.book_id) as total_copies,
          (SELECT COUNT(*) FROM Book_Copy WHERE Book_book_id = b.book_id AND status = 'Available') as available_copies
          FROM Book b
          LEFT JOIN Publisher p ON b.Publisher_publisher_id = p.publisher_id
          LEFT JOIN Genre g ON b.Genre_genre_id = g.genre_id
          WHERE b.book_id = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

if (!$row) {
    die("Book not found in the library database.");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($row['title']); ?> - Library Hub</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .details-container {
            display: flex;
            gap: 50px;
            max-width: 1200px;
            margin: 40px auto;
            padding: 20px;
            font-family: 'Inter', sans-serif;
        }

        /* Fixed Scaling for Image */
        .book-visual {
            flex: 0 0 320px;
        }

        .book-visual img {
            width: 100%;
            height: auto;
            border-radius: 16px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
            display: block;
        }

        .book-content {
            flex: 1;
        }

        .tag-row {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
        }

        .badge {
            padding: 5px 14px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge-genre {
            background: #EEF2FF;
            color: #4F46E5;
        }

        .badge-ref {
            background: #E0F2FE;
            color: #0369A1;
        }

        .book-title {
            font-size: 32px;
            font-weight: 800;
            color: #111827;
            margin: 0 0 8px 0;
        }

        .book-edition {
            color: #6B7280;
            font-size: 18px;
            margin-bottom: 12px;
        }

        .book-author {
            font-size: 16px;
            color: #4B5563;
            margin-bottom: 24px;
        }

        .book-author span {
            color: #111827;
            font-weight: 600;
        }

        .book-desc {
            line-height: 1.7;
            color: #4B5563;
            margin-bottom: 32px;
            font-size: 15px;
        }

        /* Stats Grid - Matches Figma */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 24px;
            background: #F9FAFB;
            padding: 24px;
            border-radius: 16px;
        }

        .stat-item label {
            display: block;
            font-size: 10px;
            font-weight: 700;
            color: #9CA3AF;
            text-transform: uppercase;
            margin-bottom: 4px;
        }

        .stat-item div {
            font-size: 14px;
            font-weight: 600;
            color: #111827;
        }

        .status-pill {
            color:
                <?php echo ($row['available_copies'] > 0) ? '#059669' : '#DC2626'; ?>
            ;
        }

        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            text-decoration: none;
            color: #6B7280;
            font-size: 14px;
        }
    </style>
</head>

<body>

    <div class="details-container">
        <div class="book-visual">
            <?php if ($is_admin): ?>
                <a href="admin_catalog.php" class="back-link">← Back to Catalog</a>
            <?php else: ?>
                <a href="member_catalog.php" class="back-link">← Back to Catalog</a>
            <?php endif; ?>
            <img src="<?php echo htmlspecialchars($row['image_url']); ?>" alt="Cover">
        </div>

        <div class="book-content">
            <div class="tag-row">
                <span class="badge badge-genre"><?php echo htmlspecialchars($row['genre_name'] ?? 'General'); ?></span>
            </div>

            <h1 class="book-title"><?php echo htmlspecialchars($row['title']); ?></h1>
            <div class="book-edition"><?php echo htmlspecialchars($row['edition'] ?? 'Standard Edition'); ?></div>

            <p class="book-author">By <span>James Dashner</span></p>

            <p class="book-desc"><?php echo nl2br(htmlspecialchars($row['description'])); ?></p>

            <div class="stats-grid">
                <div class="stat-item">
                    <label>Publisher</label>
                    <div><?php echo htmlspecialchars($row['publisher_name'] ?? 'N/A'); ?></div>
                </div>
                <div class="stat-item">
                    <label>Publication Year</label>
                    <div>
                        <?php
                        if (!empty($row['publication_date']) && $row['publication_date'] != '0000-00-00') {
                            // This converts "2009-01-01" back to just "2009"
                            echo date('Y', strtotime($row['publication_date']));
                        } else {
                            echo "N/A";
                        }
                        ?>
                    </div>
                </div>
                <div class="stat-item">
                    <label>ISBN</label>
                    <div><?php echo htmlspecialchars($row['ISBN']); ?></div>
                </div>
                <div class="stat-item">
                    <label>Edition</label>
                    <div><?php echo htmlspecialchars($row['edition'] ?? '1st'); ?></div>
                </div>
                <div class="stat-item">
                    <label>Copies</label>
                    <div><?php echo $row['available_copies'] . ' / ' . $row['total_copies']; ?></div>
                </div>
                <div class="stat-item">
                    <label>Status</label>
                    <div class="status-pill">
                        <?php echo ($row['available_copies'] > 0) ? 'Available' : 'Not Available'; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>

</html>
