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
        .admin-container {
            display: flex;
            min-height: 100vh;
            width: 100%;
        }

        .main-content {
            flex-grow: 1;
            padding: 30px;
            background: #f8fafc;
            overflow-x: hidden;
        }

        /* Grid and Card Styling */
        .book-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 25px;
            margin-top: 20px;
        }

        .genre-badge {
            display: inline-block;
            background: #EEF2FF; 
            color: #4F46E5; 
            padding: 4px 10px; 
            border-radius: 6px; 
            font-size: 0.7rem; 
            font-weight: 700; 
            text-transform: uppercase;
            margin-bottom: 8px;
        }

        /* Modal Styles */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 2000;
            justify-content: center;
            align-items: center;
        }

        .modal-overlay.active { display: flex; }

        .modal-container {
            background: white;
            width: 90%;
            max-width: 850px;
            max-height: 90vh;
            overflow-y: auto;
            border-radius: 12px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }

        .badge.available { background: #dcfce7; color: #15803d; }
        .badge.unavailable { background: #fee2e2; color: #b91c1c; }
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
            <header class="catalog-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
                <div class="header-left">
                    <h1 style="font-size: 1.8rem; color: #1e3a8a;">Library Catalog</h1>
                    <p style="color: #64748b;">Manage and monitor your library collection.</p>
                </div>
                <button class="btn-add" onclick="openModal()" style="background: #1e3a8a; color: white; padding: 12px 20px; border: none; border-radius: 8px; cursor: pointer; display: flex; align-items: center; gap: 8px;">
                    <i class="fi fi-rr-plus"></i> Add New Material
                </button>
            </header>

            <div class="book-grid">
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): 
                        $isAvailable = ($row['copies'] > 0);
                    ?>
                        <div class="book-card" onclick="window.location.href='book_details.php?id=<?php echo $row['book_id']; ?>'"
                            style="background: white; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); overflow: hidden; cursor: pointer;">
                            
                            <div class="book-image" style="height: 250px; position: relative;">
                                <img src="<?php echo htmlspecialchars($row['image_url']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                <span class="badge <?php echo $isAvailable ? 'available' : 'unavailable'; ?>" 
                                      style="position: absolute; top: 10px; right: 10px; padding: 5px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 600;">
                                    <?php echo $isAvailable ? 'Available' : 'Out of Stock'; ?>
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
            formData.append('price', '0'); // Defaulting price if hidden
            formData.append('edition', document.getElementById('modalEdition').value);
            formData.append('pub_date', document.getElementById('modalYear').value);
            formData.append('description', document.getElementById('bookDescription').value);
            formData.append('genre_id', document.getElementById('bookGenre').value);
            formData.append('publisher_name', document.getElementById('modalPublisher').value);

            try {
                const response = await fetch('save_book.php', { method: 'POST', body: formData });
                const result = await response.json();
                if (result.status === 'success') {
                    alert("Book Added Successfully!");
                    location.reload();
                } else {
                    alert("Error: " + result.message);
                }
            } catch (err) { alert("Server error. Check console."); console.error(err); }
        };
    </script>
</body>
</html>