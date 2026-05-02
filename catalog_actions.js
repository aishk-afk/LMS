// catalog_actions.js

// --- ADMIN ACTIONS ---
function openEditModal(bookId) {
    fetch(`get_book_details.php?id=${bookId}`)
        .then(res => res.json())
        .then(book => {
            document.getElementById('apiSearchInput').value = book.title;
            // ... (rest of your population logic)
            openModal();
        });
}

function deleteBook(bookId) {
    if (confirm("Delete this book?")) {
        fetch(`delete_book.php?id=${bookId}`)
            .then(res => res.json())
            .then(data => location.reload());
    }
}

// --- MEMBER ACTIONS ---
function borrowBook(bookId) {
    fetch('borrow_process.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `book_id=${bookId}`
    })
        .then(res => res.json())
        .then(data => {
            alert(data.message);
            if (data.status === 'success') location.reload();
        });
}

function joinWaitlist(bookId) {
    fetch('waitlist_process.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `book_id=${bookId}`
    })
        .then(res => res.json())
        .then(data => {
            alert(data.message);
            if (data.status === 'success') location.reload();
        });
}
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

    // Check if we're editing or adding
    const editBookId = document.getElementById('edit_book_id');
    if (editBookId && editBookId.value) {
        formData.append('book_id', editBookId.value);
    }

    formData.append('title', document.getElementById('apiSearchInput').value);
    formData.append('isbn', document.getElementById('modalISBN').value);
    formData.append('image_url', document.getElementById('modalCover').value);
    formData.append('copies', document.getElementById('modalCopies').value || '1');
    formData.append('price', '0');
    formData.append('edition', document.getElementById('modalEdition').value);
    formData.append('pub_date', document.getElementById('modalYear').value);
    formData.append('description', document.getElementById('bookDescription').value);
    formData.append('genre_id', document.getElementById('bookGenre').value);
    formData.append('publisher_name', document.getElementById('modalPublisher').value);

    try {
        const response = await fetch('save_book.php', { method: 'POST', body: formData });
        const text = await response.text();
        const result = JSON.parse(text);
        if (result.status === 'success') {
            const isEditing = editBookId && editBookId.value;
            alert(isEditing ? "Book Updated!" : "Book Added!");
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

// Function to handle deletion with a confirmation alert
function confirmDelete(bookId) {
    if (confirm("Are you sure you want to remove this book from the catalog?")) {
        fetch(`delete_book.php?id=${bookId}`, { method: 'GET' })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    location.reload();
                } else {
                    alert("Error: " + data.message);
                }
            });
    }
}
function openModal() {
    // Reset form for adding new book if no edit_book_id exists
    const editBookId = document.getElementById('edit_book_id');
    if (!editBookId || !editBookId.value) {
        // Reset for new book
        document.getElementById('bookForm').reset();
        document.querySelector('.form-header h2').innerText = "Catalog New Material";
        document.querySelector('.form-header p').innerText = "Fill in metadata to update your collection.";
        document.querySelector('#bookForm button[type="submit"]').innerText = "Publish to Catalog";

        // Clear image preview
        document.getElementById('modalPreviewImg').style.display = 'none';
        document.getElementById('placeholderText').style.display = 'block';
        document.getElementById('modalCover').value = '';
    }

    document.getElementById('addBookModal').classList.add('active');
}
function closeModal() {
    document.getElementById('addBookModal').classList.remove('active');
    // Clear edit_book_id when closing modal so next add is fresh
    const editBookId = document.getElementById('edit_book_id');
    if (editBookId) {
        editBookId.value = '';
    }
}
// Function to open the edit modal (you'll need to fetch book data to fill the form)
function openEditModal(bookId) {
    // 1. Fetch the data from the server
    fetch(`get_book_details.php?id=${bookId}`)
        .then(response => response.json())
        .then(book => {
            if (book.error) {
                alert(book.error);
                return;
            }

            // 2. Fill the form fields with correct IDs
            document.getElementById('apiSearchInput').value = book.title || '';
            document.getElementById('modalAuthor').value = book.author || '';
            document.getElementById('modalISBN').value = book.ISBN || '';
            document.getElementById('bookDescription').value = book.description || '';
            document.getElementById('modalPublisher').value = book.publisher || '';
            document.getElementById('modalYear').value = book.publication_date ? book.publication_date.split('-')[0] : '';
            document.getElementById('modalEdition').value = book.edition || '';
            document.getElementById('bookGenre').value = book.Genre_genre_id || '';

            // 3. Display the book cover image
            if (book.image_url) {
                document.getElementById('modalCover').value = book.image_url;
                const prevImg = document.getElementById('modalPreviewImg');
                prevImg.src = book.image_url;
                prevImg.style.display = 'block';
                document.getElementById('placeholderText').style.display = 'none';
            }

            // 4. Update Modal UI
            document.querySelector('.form-header h2').innerText = "Edit Material";
            document.querySelector('.form-header p').innerText = "Update metadata for this material.";
            document.querySelector('#bookForm button[type="submit"]').innerText = "Update Book";

            // 5. Add/Update hidden input to track which book we are editing
            let form = document.getElementById('bookForm');
            let hiddenInput = document.getElementById('edit_book_id');
            if (!hiddenInput) {
                hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.id = 'edit_book_id';
                hiddenInput.name = 'book_id';
                form.appendChild(hiddenInput);
            }
            hiddenInput.value = bookId;

            // 6. Open the Modal
            openModal();
        })
        .catch(err => console.error("Error fetching book details:", err));
}

function deleteBook(bookId) {
    if (confirm("Are you sure you want to remove this book?")) {
        fetch(`delete_book.php?id=${bookId}`)
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    location.reload();
                } else {
                    alert("Error: " + data.message);
                }
            })
            .catch(err => alert("Delete script (delete_book.php) not found!"));
    }
}

function borrowBook(bookId) {
    alert("Borrowing process started for Book ID: " + bookId);
    // Add your fetch('borrow_process.php') logic here
}

// 6. WAITLIST BUTTON LOGIC (For Members)
function joinWaitlist(bookId) {
    alert("Added to waitlist for Book ID: " + bookId);
    // Add your fetch('waitlist_process.php') logic here
}
function updatePreviewFromManual(url) {
    const prevImg = document.getElementById('modalPreviewImg');
    const placeholder = document.getElementById('placeholderText');
    const hiddenCover = document.getElementById('modalCover');

    if (url) {
        prevImg.src = url;
        prevImg.style.display = 'block';
        placeholder.style.display = 'none';
        hiddenCover.value = url; // Sets the URL to be saved in DB
    } else {
        prevImg.style.display = 'none';
        placeholder.style.display = 'block';
    }
}