<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logging Out</title>
    <link rel="stylesheet" href="CSS/style.css">
</head>
<body>
<header>
        <nav class="navbar">
            <div class="logo">
                <img src="../Images/logo.jpg" alt="Logo">
            </div>
            <ul class="nav-links">
                <li><a href="Index.php">Home</a></li>
                <li><a href="Inquiries.php">Inquiries</a></li>
                <li><a href="Aboutus.php">About Us</a></li>
            </ul>
            <div class="cta-button">
                <a href="Login.php" class="button">Access MedTrak</a>
            </div>
        </nav>
    </header>
    <main class="main-inquiry">
        <section class="info-section">
            <h2>You have been logged out!</h2>
            <p>Thank you for visiting. You will be redirected to the login page shortly.</p>   
            </section>
    </main>
    <script>
        // Clear local storage or session storage to simulate logging out
        localStorage.clear();
        sessionStorage.clear();

        // Redirect to login page after a few seconds
        setTimeout(function() {
            window.location.href = "Login.php"; // Redirect to login page
        }, 1000); // Redirect after 1 seconds
    </script>
</body>
</html>