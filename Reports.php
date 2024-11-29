<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start(); // join session started by login.php.
}

if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
    $db = $_SESSION['db'];
} else {
    // Redirect to the login page if not logged in
    header("Location: Login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="CSS/style.css">
</head>
<body>
 
    <header>
        <nav class="navbar practitioner-navbar">
            <div class="logo">
                <img src="../Images/logo.jpg" alt="Logo">
            </div>
            <div class="cta-button">
                <a href="Logout.php" class="button">Logout</a>
            </div>
        </nav>
        
        
    </header>
    <div class="dashboard-container">
        <aside class="sidebar">
            <nav>
                <ul>
                    <li><a href="Administration.php">Administration</a></li>
                    <li><a href="Add_Patient.php">Add Patients</a></li>
                    <li><a href="Medication.php">Medication Summary</a></li>
                    <li><a href="Diet.php">Diet Summary</a></li>
                    <li><a href="Reports.php">Reports</a></li>
                </ul>
            </nav>
        </aside>
        
        <main class="main-dashboard">
            

            
        </main>
    </div>

    <footer>
        <p>&copy; 2024 MedTrak</p>
        <p>Contact us at: <a href="mailto:w.wang213@gmail.com">info@medtrak.com.au</a></p>
        <p>Phone: +61 123 456 789</p>
        <p>Sydney Startup Hub, 11 York St, Sydney NSW 2000</p>
    </footer>

</body>
</html>