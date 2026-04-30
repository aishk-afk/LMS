<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Catalog - Member</title>
    <link rel="stylesheet" href="css/layout.css">
    <link rel="stylesheet" href="css/member.css">
    <link rel="stylesheet" href="css/catalog.css">
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/uicons-regular-rounded/css/uicons-regular-rounded.css'>
</head>

<body>
    <div class="app-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <img src="applogo(2).png" alt="Logo" class="logo-icon">
                <h2 class="brand-name">Learning Library Management Hub</h2>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li class="nav-item"><a href="member_dashboard.html"><i class="fi fi-rr-home"></i> Dashboard</a>
                    </li>
                    <li class="nav-item active"><a href="#"><i class="fi fi-rr-search"></i> Catalog</a></li>
                    <li class="nav-item"><a href="member_account.html"><i class="fi fi-rr-user"></i> Account</a></li>
                </ul>
            </nav>
            <div class="sidebar-footer">
                <div class="user-info">
                    <strong>Jane Doe</strong><br><small>Student</small>
                </div>
                <a href="index.html" class="logout-link"><i class="fi fi-rr-exit"></i> Logout</a>
            </div>
        </aside>

        <main class="main-content">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <div class="header-left">
                    <h1 style="font-size: 1.8rem; color: #1e3a8a; margin: 0;">Library Catalog</h1>
                    <p style="color: #64748b; margin: 5px 0 0 0;">Manage and monitor your library collection.</p>
                </div>
                <button onclick="openModal()" style="background: #1e3a8a; color: white; padding: 12px 24px; border: none; border-radius: 50px; cursor: pointer; font-weight: 600; display: flex; align-items: center; gap: 8px;">
                    <i class="fi fi-rr-plus"></i> Add New Material
                </button>
            </div>

            <div class="filter-bar" style="display: flex; gap: 12px; align-items: center; background: white; padding: 10px 20px; border-radius: 50px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); margin-bottom: 30px;">
                <div style="flex-grow: 1; position: relative;">
                    <i class="fi fi-rr-search" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #94a3b8;"></i>
                    <input type="text" id="catalogSearch" placeholder="Search by title, author, or keyword..."
                        style="width: 100%; padding: 12px 12px 12px 45px; border: 1px solid #e2e8f0; border-radius: 50px; outline: none;">
                </div>

                <div style="display: flex; align-items: center; border-left: 1px solid #e2e8f0; padding-left: 15px;">
                    <i class="fi fi-rr-filter" style="color: #94a3b8; margin-right: 8px;"></i>
                    <select id="filterGenre" style="border: none; color: #64748b; background: white; cursor: pointer; outline: none;">
                        <option value="all">All Genres</option>
                        <?php
                        $genres_result->data_seek(0);
                        while ($g = $genres_result->fetch_assoc()): ?>
                            <option value="<?php echo htmlspecialchars($g['genre_name']); ?>"><?php echo htmlspecialchars($g['genre_name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <select id="filterStatus" style="padding: 8px 15px; border: 1px solid #e2e8f0; border-radius: 50px; color: #64748b; background: white; cursor: pointer; outline: none;">
                    <option value="all">All Status</option>
                    <option value="Available">Available</option>
                    <option value="Unavailable">Unavailable</option>
                </select>
            </div>

            <div class="book-grid">
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()):
                        $isAvailable = ($row['copies'] > 0); ?>
                        <div class="book-card" data-genre="<?php echo htmlspecialchars($row['genre_name'] ?? 'General'); ?>" onclick="window.location.href='book_details.php?id=<?php echo $row['book_id']; ?>'">
                            <div class="book-image" style="height: 260px; position: relative; border-radius: 14px; overflow: hidden;">
                                <img src="<?php echo htmlspecialchars($row['image_url']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                <span class="badge <?php echo $isAvailable ? 'available' : 'unavailable'; ?>">
                                    <?php echo $isAvailable ? "Available (" . $row['copies'] . ")" : "Unavailable"; ?>
                                </span>
                            </div>

                            <div class="book-details" style="padding: 15px;">
                                <span class="genre-badge"><?php echo htmlspecialchars($row['genre_name'] ?? 'General'); ?></span>
                                <h3 style="font-size: 1rem; margin: 5px 0; color: #1e293b;"><?php echo htmlspecialchars($row['title']); ?></h3>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="text-align: center; color: #94a3b8; padding: 40px; width: 100%;">No books found in the catalog.</p>
                <?php endif; ?>
            </div>
        </main>

        <div class="book-grid">
            <div class="book-card">
                <div class="book-thumbnail">
                    <img src="book-biology.png" alt="Book Cover">
                </div>
                <div class="book-info">
                    <span class="status-badge badge-green">Available</span>
                    <h4>Campbell Biology</h4>
                    <p>Jane B. Reece, Lisa A. Urry</p>
                    <div class="card-footer">
                        <button class="btn-primary">Borrow Now</button>
                        <button class="btn-icon"><i class="fi fi-rr-heart"></i></button>
                    </div>
                </div>
            </div>

            
        </div>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const searchInput = document.getElementById('catalogSearch');
            const genreFilter = document.getElementById('filterGenre');
            const statusFilter = document.getElementById('filterStatus');
            const bookCards = document.querySelectorAll('.book-card');

            function filterBooks() {
                const searchTerm = searchInput.value.toLowerCase();
                const selectedGenre = genreFilter.value;
                const selectedStatus = statusFilter.value;

                bookCards.forEach(card => {
                    const title = card.querySelector('h3').innerText.toLowerCase();
                    const genre = card.getAttribute('data-genre');
                    const badgeText = card.querySelector('.badge').innerText;

                    const matchesSearch = title.includes(searchTerm);
                    const matchesGenre = (selectedGenre === 'all' || genre === selectedGenre);
                    const matchesStatus = (selectedStatus === 'all' || badgeText.includes(selectedStatus));

                    if (matchesSearch && matchesGenre && matchesStatus) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                });
            }

            searchInput.addEventListener('input', filterBooks);
            genreFilter.addEventListener('change', filterBooks);
            statusFilter.addEventListener('change', filterBooks);
        });
    </script>
</body>

</html>