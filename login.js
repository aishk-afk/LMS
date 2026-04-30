// 1. Password Visibility Toggle Logic
const togglePassword = document.querySelector('#togglePassword');
const passwordField = document.querySelector('#password');

togglePassword.addEventListener('click', function () {
    // Toggle the type attribute
    const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
    passwordField.setAttribute('type', type);
    
    // Toggle the icon
    this.textContent = type === 'password' ? '👁️' : '🙈';
});


document.getElementById("loginForm").addEventListener("submit", function(event) {
    event.preventDefault();

    const email = document.getElementById("email").value;
    const password = document.getElementById("password").value;

    fetch("login.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        body: `email=${encodeURIComponent(email)}&password=${encodeURIComponent(password)}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === "success") {
            if (data.role === "admin") {
                window.location.href = "admin_dashboard.html";
            } else {
                const UserId = encodeURIComponent(data.user_id || 'ST001');
                window.location.href = `member_dashboard.php?user_id=${userId}`;
            }
        } else {
            alert(data.message || "Incorrect email or password");
        }
})
    .catch(() => {
        alert("Server error");
    });
});
