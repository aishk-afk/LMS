<?php
// 1. Database Connection
$conn = new mysqli("localhost", "root", "", "lms_db");
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

// 2. Fetch Books
$query = "SELECT * FROM book";
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
        /* Cleaning up the Grid Layout */
        .book-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 25px;
            padding: 20px 0;
        }
        .modal-overlay {
            display: none; /* Hidden by default */
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }
        .modal-overlay.active { display: flex; }
        .modal-container {
            background: white;
            width: 90%;
            max-width: 800px;
            max-height: 90vh;
            overflow-y: auto;
            border-radius: 12px;
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

            <div class="toolbar" style="display: flex; gap: 20px; margin-bottom: 25px;">
                <div class="search-bar" style="flex: 1; position: relative;">
                    <input type="text" id="catalogSearch" placeholder="Search title or author..." style="width: 100%; padding: 12px 40px; border: 1px solid #e2e8f0; border-radius: 8px;">
                    <i class="fi fi-rr-search" style="position: absolute; left: 15px; top: 14px; color: #94a3b8;"></i>
                </div>
                <div class="filter-group" style="display: flex; gap: 10px;">
                    <select class="select-filter" id="genreFilter" style="padding: 10px; border-radius: 8px; border: 1px solid #e2e8f0;">
                        <option value="All Genres">All Genres</option>
                        <option value="Computer Science">Computer Science</option>
                        <option value="Mathematics">Mathematics</option>
                    </select>
                </div>
            </div>

            <div class="book-grid" id="bookGrid">
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): 
                        $isAvailable = ($row['copies'] > 0);
                    ?>
                        <div class="book-card" data-genre="<?php echo htmlspecialchars($row['Genre_genre_id']); ?>" style="background: white; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); overflow: hidden;">
                            <div class="book-image" style="height: 250px; position: relative; overflow: hidden;">
                                <img src="<?php echo htmlspecialchars($row['image_url']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                <span class="badge <?php echo $isAvailable ? 'available' : 'unavailable'; ?>" style="position: absolute; top: 10px; right: 10px; padding: 5px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 600;">
                                    <?php echo $isAvailable ? 'Available' : 'Out of Stock'; ?>
                                </span>
                            </div>
                            <div class="book-details" style="padding: 15px;">
                                <span style="font-size: 0.7rem; color: #3b82f6; font-weight: 700; text-transform: uppercase;"><?php echo htmlspecialchars($row['Genre_genre_id']); ?></span>
                                <h3 style="font-size: 1rem; margin: 5px 0; color: #1e293b;"><?php echo htmlspecialchars($row['title']); ?></h3>
                                <p style="font-size: 0.85rem; color: #64748b; margin: 0;">Copies: <?php echo $row['copies']; ?></p>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="grid-column: 1/-1; text-align: center; color: #94a3b8; padding: 40px;">No books found in the database.</p>
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
                        <input type="hidden" id="modalCover"> </div>

                    <div class="col-right">
                        <div style="margin-bottom: 15px;">
                            <label style="display:block; font-weight:600;">Title Search (Google Books)</label>
                            <div style="display: flex; gap: 10px; margin-top: 5px;">
                                <input type="text" id="apiSearchInput" placeholder="Enter book title..." style="flex:1; padding: 10px; border: 1px solid #e2e8f0; border-radius: 6px;">
                                <button type="button" onclick="searchAPI()" style="background:#3b82f6; color:white; border:none; padding: 0 15px; border-radius: 6px; cursor:pointer;"><i class="fi fi-rr-search"></i></button>
                            </div>
                            <div id="apiResults" style="display:flex; gap:10px; overflow-x:auto; margin-top:10px; padding: 5px 0;"></div>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                            <div>
                                <label style="display:block; font-weight:600;">Author</label>
                                <input type="text" id="modalAuthor" required style="width:100%; padding:10px; border:1px solid #e2e8f0; border-radius:6px;">
                            </div>
                            <div>
                                <label style="display:block; font-weight:600;">ISBN</label>
                                <input type="text" id="modalISBN" style="width:100%; padding:10px; border:1px solid #e2e8f0; border-radius:6px;">
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top:15px;">
                            <div>
                                <label style="display:block; font-weight:600;">Copies</label>
                                <input type="number" id="modalCopies" value="1" style="width:100%; padding:10px; border:1px solid #e2e8f0; border-radius:6px;">
                            </div>
                            <div>
                                <label style="display:block; font-weight:600;">Price (₱)</label>
                                <input type="number" id="modalPrice" value="0" style="width:100%; padding:10px; border:1px solid #e2e8f0; border-radius:6px;">
                            </div>
                        </div>

                        <input type="hidden" id="modalPublisher">
                        <input type="hidden" id="modalYear">
                        <input type="hidden" id="modalDesc">
                    </div>
                </div>

                <div class="form-actions" style="margin-top: 30px; text-align: right; border-top: 1px solid #f1f5f9; padding-top: 20px;">
                    <button type="button" onclick="closeModal()" style="padding: 10px 20px; margin-right: 10px; background:white; border:1px solid #cbd5e1; border-radius:6px; cursor:pointer;">Cancel</button>
                    <button type="submit" style="padding: 10px 25px; background:#1e3a8a; color:white; border:none; border-radius:6px; font-weight:600; cursor:pointer;">Publish to Catalog</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Modal Control
        function openModal() { document.getElementById('addBookModal').classList.add('active'); }
        function closeModal() { document.getElementById('addBookModal').classList.remove('active'); }

        // Google Books API Search
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
                        document.getElementById('modalPublisher').value = info.publisher || '';
                        document.getElementById('modalYear').value = info.publishedDate ? info.publishedDate.split('-')[0] : '';
                        document.getElementById('modalDesc').value = info.description || '';
                        
                        // Preview
                        const prevImg = document.getElementById('modalPreviewImg');
                        prevImg.src = cover;
                        prevImg.style.display = 'block';
                        document.getElementById('placeholderText').style.display = 'none';
                    };
                    resultsDiv.appendChild(thumb);
                });
            } catch (e) { resultsDiv.innerHTML = "Error fetching data."; }
        }

        // Handle Form Submission (Save to Database)
        document.getElementById('bookForm').onsubmit = async function(e) {
            e.preventDefault();
            const formData = new FormData();
            formData.append('title', document.getElementById('apiSearchInput').value);
            formData.append('isbn', document.getElementById('modalISBN').value);
            formData.append('image_url', document.getElementById('modalCover').value);
            formData.append('copies', document.getElementById('modalCopies').value);
            formData.append('price', document.getElementById('modalPrice').value);
            formData.append('publisher_name', document.getElementById('modalPublisher').value);
            formData.append('pub_date', document.getElementById('modalYear').value);
            formData.append('description', document.getElementById('modalDesc').value);

            try {
                const response = await fetch('save_book.php', { method: 'POST', body: formData });
                const result = await response.json();
                if (result.status === 'success') {
                    alert("Book Added!");
                    location.reload();
                } else { alert("Error: " + result.message); }
            } catch (err) { alert("Server connection failed."); }
        };
    </script>
</body>
</html>