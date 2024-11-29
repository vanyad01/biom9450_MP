<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple Web Page</title>
    <link rel="stylesheet" href="CSS/style.css">
</head>
<body>
 
    <header>
        <!--Navigation bar for pages before login-->
        <nav class="navbar">
            <div class="logo">
                <img src="../Images/logo.jpg" alt="Logo">
            </div>
            <ul class="nav-links">
                <li><a href="Index.php">Home</a></li>
                <li><a href="Inquiries.php">Inquiries</a></li>
                <li><a href="Aboutus.php">About Us</a></li>
            </ul>
            <!--Displays the access button-->
            <div class="cta-button">
                <a href="Login.php" class="button">Access MedTrak</a>
            </div>
        </nav>
    </header>

    <main class="main-home">
        <!--Container for background image-->
        <div class="hero-container">

            <!--div for text to be displayed over background img-->
            <div class="hero-text">
                  <h1>Welcome to MedTrak</h1>
                <p>Your partner in medical data management and care solutions.</p>
            </div>
        </div>
    </main>
 
    <!--Footer displayed at the bottom of the page-->
    <footer>
        <p>&copy; 2024 MedTrak</p>
        <p>Contact us at: <a href="mailto:w.wang213@gmail.com">info@medtrak.com.au</a></p>
        <p>Phone: +61 123 456 789</p>
        <p>Sydney Startup Hub</p>
        <p>11 York St, Sydney NSW 2000</p>
    </footer>
 
</body>
</html>