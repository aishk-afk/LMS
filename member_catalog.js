function handleBorrow(bookId) {
    showConfirmModal(
        'Confirm Borrow',
        'Are you sure you want to borrow this book? This item is due in 14 days.',
        'Borrow Book',
        () => executeAction('book_process.php', bookId)
    );
}

function handleWaitlist(bookId) {
    showConfirmModal(
        'Join Waitlist',
        'No copies are currently available. Join the waitlist to be notified when a copy is returned.',
        'Join Waitlist',
        () => executeAction('waitlist_process.php', bookId)
    );
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