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
            <div class="cta-button">
                <a href="Login.php" class="button">Access MedTrak</a>
            </div>
        </nav>
    </header>
    <!--Main section for the whole viewport-->
    <main class="main-inquiry">

        <!--Section for contact form-->
        <section class="contact-section">    
            <div class="contact-form">
                <h2>Contact Us</h2>
                <form action="mailto:info@medtrak.com" method="POST" enctype="text/plain">
                    <label for="name">Name</label>
                    <input type="text" id="name" name="name" required>
    
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
    
                    <label for="message">Message</label>
                    <textarea id="message" name="message" rows="5" required></textarea>
    
                    <button type="submit">Send Message</button>
                </form>
            </div>
        </section>
    </main>
 
    <footer>
        <p>&copy; 2024 MedTrak</p>
        <p>Contact us at: <a href="mailto:w.wang213@gmail.com">info@medtrak.com.au</a></p>
        <p>Phone: +61 123 456 789</p>
        <p>Sydney Startup Hub</p>
        <p>11 York St, Sydney NSW 2000</p>
    </footer>
 
</body>
</html>