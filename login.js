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

// 2. Your Existing Form Submission Logic
document.getElementById("loginForm").addEventListener("submit", function(event) {
  event.preventDefault();
  
  const email = document.getElementById("email").value;
  const password = document.getElementById("password").value;

  if (email && password) {
    alert("Login successful for: " + email);
    // Add backend authentication logic here
  } else {
    alert("Please fill in all fields.");
  }
});

document.getElementById("loginForm").addEventListener("submit", function(event) {
    event.preventDefault();
    
    const email = document.getElementById("email").value;
    const password = document.getElementById("password").value;

    // Static check for testing
    if (email === "admin@lib.edu" && password === "admin123") {
      //console.log("Admin login successful");  
      window.location.href = "admin_dashboard.html"; 
    } else if (email === "student@lib.edu" && password === "12345") {
        window.location.href = "member_dashboard.html";
    } else {
        alert("Incorrect Email or Password. Try admin@lib.edu / admin123");
    }
});