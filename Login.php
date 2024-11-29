<?php
// VANYA ADDED SESSION_START() CODE TO ACCESS USERNAME IN Administration.php (Sat, 23/11/24):
session_start(); // start hte session.
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Practitioner Login</title>
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

    <!--Container for both left/right-->
    <div class="container">

        <!--Login section, left side-->
        <div class="login-section">
            <div class="login-form">
                <h2>Log In</h2>

                <?php
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $db = "C:\Users\wwang\Desktop\MedTrak Official\database9450.accdb";
                    $conn = odbc_connect("Driver={Microsoft Access Driver (*.mdb, *.accdb)};Dbq=$db", '', '');

                    if (!$conn) {
                        echo "<p class='message error'>Connection Failed: " . odbc_errormsg() . "</p>";
                    } else {
                        $inputUsername = $_POST['username'];
                        $inputPassword = $_POST['password'];

                        $sql = "SELECT COUNT(*) AS MatchCount FROM Practitioners 
                                WHERE UserName = '$inputUsername' AND Password = '$inputPassword'";

                        $result = odbc_exec($conn, $sql);

                        if (!$result) {
                            echo "<p class='message error'>Error executing query: " . odbc_errormsg($conn) . "</p>";
                        } else {
                            $row = odbc_fetch_array($result);
                            $matchCount = $row['MatchCount'];

                            if ($matchCount > 0) {
                                // ['username'] & $_SESSION['db'] CODE TO ACCESS USERNAME & DATABASE IN OTHER PAGES (Sat, 23/11/24):
                                $_SESSION['username'] = $_POST['username']; // store the username within the session.
                                $_SESSION['db'] = "C:/Users/wwang/Desktop/MedTrak Official/database9450.accdb"; // store the database URI.

                                // Redirect to another page upon successful login
                                header("Location: Administration.php");
                                exit();
                            } else {
                                echo "<p class='message error'>Invalid username or password. Please try again.</p>";
                            }
                        }
                        odbc_close($conn);
                    }
                }
                ?>

                <!-- This is the login form that is displayed-->
                <form id="loginForm" method="POST" autocomplete="off">
                    <input type="text" name="username" id="username" placeholder="Username" required>
                    <input type="password" name="password" id="password" placeholder="Password" required><br>
                    <button type="submit">Log in</button>
                </form>
            </div>
        </div>

        <!--Backround img for rightside-->
        <div class="image-section">
            <img src="../Images/LoginBG.jpg" alt="Right Side Image">
        </div>
    </div>
 
    <footer>
        <p>&copy; 2024 MedTrak</p>
        <p>Contact us at: <a href="mailto:w.wang213@gmail.com">info@medtrak.com.au</a></p>
        <p>Phone: +61 123 456 789</p>
        <p>Sydney Startup Hub</p>
        <p>11 York St, Sydney NSW 2000</p>
    </footer>
</body>
</html>
