<?php
// 1. Database Connection
include 'db_config.php';

// 2. Fetch Genres for the dropdown
$genre_query = "SELECT * FROM genre";
$genres_result = $conn->query($genre_query);

// 3. Fetch Books with Genre Names and Copy counts
$query = "SELECT b.*, g.genre_name, 
          (SELECT COUNT(*) FROM Book_Copy WHERE Book_book_id = b.book_id) as copies 
          FROM Book b
          LEFT JOIN Genre g ON b.Genre_genre_id = g.genre_id";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Management System - Catalog</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="css/layout.css">
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/uicons-regular-rounded/css/uicons-regular-rounded.css'>

    <style>
        /* Layout Fixes */
        .admin-container { display: flex; min-height: 100vh; width: 100%; }
        .main-content { flex-grow: 1; padding: 30px; background: #f8fafc; overflow-x: hidden; }

        /* Grid and Card Styling */
        .book-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 25px; margin-top: 20px; }
        .book-card { background: white; border-radius: 18px !important; box-shadow: 0 4px 6px rgba(0,0,0,0.05); overflow: hidden; cursor: pointer; transition: transform 0.2s ease; padding: 10px; }
        .genre-badge { display: inline-block; color: #94a3b8; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; margin-bottom: 8px; }

        /* Figma Pill Badges */
        .badge { position: absolute; top: 12px; right: 12px; padding: 4px 12px !important; border-radius: 50px !important; font-size: 0.7rem !important; font-weight: 700; z-index: 10; }
        .badge.available { background: #dcfce7 !important; color: #15803d !important; }
        .badge.unavailable { background: #fee2e2 !important; color: #b91c1c !important; }

        /* Modal Styles */
        .modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); z-index: 2000; justify-content: center; align-items: center; }
        .modal-overlay.active { display: flex; }
        .modal-container { background: white; width: 90%; max-width: 850px; max-height: 90vh; overflow-y: auto; border-radius: 12px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); }
    </style>
</head>

<body>
    <div class="admin-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <img src="applogo(2).png" alt="Logo" class="logo-icon">
                <h2 class="brand-name">Library Hub</h2>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li class="nav-item"><a href="admin_dashboard.html"><i class="fi fi-rr-home"></i> Dashboard</a></li>
                    <li class="nav-item active"><a href="admin_catalog.php"><i class="fi fi-rr-search"></i> Catalog</a></li>
                    <li class="nav-item"><a href="admin_users.html"><i class="fi fi-rr-users-alt"></i> Users</a></li>
                    <li class="nav-item"><a href="admin_waitlist.html"><i class="fi fi-rr-clock"></i> Waitlist</a></li>
                    <li class="nav-item"><a href="admin_settings.html"><i class="fi fi-rr-settings"></i> Settings</a></li>
                </ul>
            </nav>
            <div class="sidebar-footer">
                <div class="admin-profile"><strong>Admin User</strong><br><small>Librarian</small></div>
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
                                <p style="font-size: 0.85rem; color: #64748b; margin: 0;">Copies: <?php echo $row['copies']; ?></p>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="text-align: center; color: #94a3b8; padding: 40px; width: 100%;">No books found in the catalog.</p>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <div id="addBookModal" class="modal-overlay">
        <div class="modal-container">
            <header class="form-header" style="background: #1e3a8a; padding: 20px; display: flex; justify-content: space-between; color: white;">
                <div>
                    <h2 style="margin:0;">Catalog New Material</h2>
                    <p style="margin:0; font-size: 0.8rem; opacity: 0.8;">Fill in metadata to update your collection.</p>
                </div>
                <button onclick="closeModal()" style="background:none; border:none; color:white; font-size: 1.5rem; cursor:pointer;">&times;</button>
            </header>

            <form id="bookForm" style="padding: 25px;">
                <div style="display: grid; grid-template-columns: 200px 1fr; gap: 30px;">
                    <div class="col-left">
                        <label style="font-weight: 600; display: block; margin-bottom: 10px;">Book Cover</label>
                        <div id="modalPreviewBox" style="width: 100%; height: 260px; border: 2px dashed #cbd5e1; border-radius: 8px; display: flex; align-items: center; justify-content: center; background: #f8fafc; overflow: hidden;">
                            <img id="modalPreviewImg" style="display:none; width: 100%; height: 100%; object-fit: cover;">
                            <div id="placeholderText" style="text-align:center; color: #94a3b8;">
                                <i class="fi fi-rr-picture" style="font-size: 2rem;"></i>
                                <p>Preview</p>
                            </div>
                        </div>
                        <input type="hidden" id="modalCover">
                    </div>

                    <div class="col-right">
                        <div style="margin-bottom: 15px;">
                            <label style="font-weight:600; display:block;">Title Search</label>
                            <div style="display: flex; gap: 10px;">
                                <input type="text" id="apiSearchInput" placeholder="Enter book title..." style="flex:1; padding: 10px; border: 1px solid #e2e8f0; border-radius: 6px;">
                                <button type="button" onclick="searchAPI()" style="background:#3b82f6; color:white; border:none; padding: 0 15px; border-radius: 6px; cursor:pointer;"><i class="fi fi-rr-search"></i></button>
                            </div>
                            <div id="apiResults" style="display:flex; gap:10px; overflow-x:auto; margin-top:10px; padding-bottom:5px;"></div>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                            <input type="text" id="modalAuthor" placeholder="Author" style="padding:10px; border:1px solid #e2e8f0; border-radius:6px;">
                            <input type="text" id="modalEdition" placeholder="Edition" style="padding:10px; border:1px solid #e2e8f0; border-radius:6px;">
                            <input type="text" id="modalISBN" placeholder="ISBN" style="padding:10px; border:1px solid #e2e8f0; border-radius:6px;">
                            <input type="text" id="modalPublisher" placeholder="Publisher" style="padding:10px; border:1px solid #e2e8f0; border-radius:6px;">
                            <input type="text" id="modalYear" placeholder="Year (YYYY)" style="padding:10px; border:1px solid #e2e8f0; border-radius:6px;">
                            <input type="number" id="modalCopies" placeholder="Copies" value="1" style="padding:10px; border:1px solid #e2e8f0; border-radius:6px;">
                        </div>

                        <div style="margin-top: 15px;">
                            <label style="font-weight:600;">Genre</label>
                            <select id="bookGenre" style="width: 100%; padding: 10px; border: 1px solid #e2e8f0; border-radius: 8px;">
                                <option value="">Select Genre</option>
                                <?php
                                $genres_result->data_seek(0);
                                while ($genre = $genres_result->fetch_assoc()): ?>
                                    <option value="<?php echo $genre['genre_id']; ?>"><?php echo $genre['genre_name']; ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div style="margin-top: 15px;">
                            <label style="font-weight:600;">Description</label>
                            <textarea id="bookDescription" rows="4" style="width:100%; padding:10px; border:1px solid #e2e8f0; border-radius:6px; resize: vertical;"></textarea>
                        </div>
                    </div>
                </div>

                <div class="form-actions" style="margin-top: 30px; text-align: right; border-top: 1px solid #f1f5f9; padding-top: 20px;">
                    <button type="button" onclick="closeModal()" style="padding: 10px 20px; border-radius:6px; border:1px solid #cbd5e1; background:white; cursor:pointer; margin-right:10px;">Cancel</button>
                    <button type="submit" style="padding: 10px 25px; background:#1e3a8a; color:white; border:none; border-radius:6px; cursor:pointer; font-weight:600;">Publish to Catalog</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openModal() { document.getElementById('addBookModal').classList.add('active'); }
        function closeModal() { document.getElementById('addBookModal').classList.remove('active'); }

        async function searchAPI() {
            const query = document.getElementById('apiSearchInput').value;
            const resultsDiv = document.getElementById('apiResults');
            if (!query) return alert("Enter a title first!");
            resultsDiv.innerHTML = "Searching...";
            try {
                const res = await fetch(`https://www.googleapis.com/books/v1/volumes?q=${encodeURIComponent(query)}`);
                const data = await res.json();
                resultsDiv.innerHTML = "";
                data.items.slice(0, 4).forEach(book => {
                    const info = book.volumeInfo;
                    const cover = info.imageLinks ? info.imageLinks.thumbnail : '';
                    const thumb = document.createElement('div');
                    thumb.innerHTML = `<img src="${cover}" style="width:50px; height:70px; border-radius:4px; border:1px solid #ddd; cursor:pointer;">`;
                    thumb.onclick = () => {
                        document.getElementById('apiSearchInput').value = info.title;
                        document.getElementById('modalAuthor').value = info.authors ? info.authors[0] : '';
                        document.getElementById('modalCover').value = cover;
                        document.getElementById('modalISBN').value = info.industryIdentifiers ? info.industryIdentifiers[0].identifier : '';
                        document.getElementById('modalPublisher').value = info.publisher || '';
                        document.getElementById('modalYear').value = info.publishedDate ? info.publishedDate.split('-')[0] : '';
                        document.getElementById('bookDescription').value = info.description || '';
                        const prevImg = document.getElementById('modalPreviewImg');
                        prevImg.src = cover;
                        prevImg.style.display = 'block';
                        document.getElementById('placeholderText').style.display = 'none';
                    };
                    resultsDiv.appendChild(thumb);
                });
            } catch (e) { resultsDiv.innerHTML = "Error."; }
        }

        document.getElementById('bookForm').onsubmit = async function (e) {
            e.preventDefault();
            const formData = new FormData();
            formData.append('title', document.getElementById('apiSearchInput').value);
            formData.append('isbn', document.getElementById('modalISBN').value);
            formData.append('image_url', document.getElementById('modalCover').value);
            formData.append('copies', document.getElementById('modalCopies').value);
            formData.append('price', '0');
            formData.append('edition', document.getElementById('modalEdition').value);
            formData.append('pub_date', document.getElementById('modalYear').value);
            formData.append('description', document.getElementById('bookDescription').value);
            formData.append('genre_id', document.getElementById('bookGenre').value);

            try {
                const response = await fetch('save_book.php', { method: 'POST', body: formData });
                const text = await response.text();
                const result = JSON.parse(text);
                if (result.status === 'success') {
                    alert("Book Added!");
                    location.reload();
                } else {
                    alert("Error: " + result.message);
                }
            } catch (err) { console.error(err); }
        };

        // FIXED FILTERING LOGIC
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
                    const genre = card.getAttribute('data-genre'); // Using data attribute for safety
                    const badgeText = card.querySelector('.badge').innerText; // e.g. "Available (5)"

                    const matchesSearch = title.includes(searchTerm);
                    const matchesGenre = (selectedGenre === 'all' || genre === selectedGenre);
                    
                    // Fixed status logic using includes()
                    let matchesStatus = (selectedStatus === 'all' || badgeText.includes(selectedStatus));

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