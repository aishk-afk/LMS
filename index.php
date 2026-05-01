<?php
session_start();

// This script detects the "?action=logout" in the URL and wipes the session
if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    session_unset();
    session_destroy();
    header("Location: login.php"); // Redirects back to login after clearing data
    exit;
}
?>

<!DOCTYPE html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Learning Management Hub</title>
    <link rel="stylesheet" href="css/login.css">
</head>

<body>
    <div class="container">
        <div class="left-panel">
            <h3>Learning Library Management Hub</h3> <br><br>
            <blockquote>"A library is the delivery room for the birth of ideas. "<br>
                <span> - Norman Cousins</span>
            </blockquote>
        </div>


        <div class="right-panel">
            <h2> Welcome</h2>
            <p> Please sign in to access the library portal.</p>
            <form id="loginForm">
                <label for="email">Email Address</label>
                <input type="email" id="email" placeholder="name@library.edu" name="email" required>
                <label for="password">Password</label>
                <input type="password" id="password" placeholder="Enter your password" name="password" required>
                <span id="togglePassword" class="toggle-icon"></span>
                <button type="submit">Sign In</button>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('loginForm').addEventListener('submit', function (e) {
            e.preventDefault();

            const formData = new FormData(this);

            fetch('login.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        if (data.role === 'admin') {
                            window.location.href = 'admin_dashboard.php';
                        } else {
                            window.location.href = 'member_dashboard.php';
                        }
                    } else {
                        alert(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert("Something went wrong with the connection.");
                });
        });
    </script>
</body>

</html>