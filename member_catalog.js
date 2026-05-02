function handleBorrow(bookId) {
    if (confirm("Do you want to borrow this book for 7 days?")) {
        executeAction('borrow_process.php', bookId);
    }
}

function handleWaitlist(bookId) {
    if (confirm("No copies available. Join the waitlist to be notified?")) {
        executeAction('waitlist_process.php', bookId);
    }
}

function executeAction(url, id) {
    const formData = new FormData();
    formData.append('book_id', id);

    fetch(url, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            alert(data.message);
            location.reload(); // Refresh to update copy count
        } else {
            alert("Error: " + data.message);
        }
    })
    .catch(err => console.error("Request failed", err));
}