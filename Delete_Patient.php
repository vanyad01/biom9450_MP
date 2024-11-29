<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start(); // join session started by login.php.
}

if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
    $db = $_SESSION['db'];
} else {
    // Redirect to the login page if not logged in.
    header("Location: Login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Simple Web Page</title>
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
            <?php
            // Enable error reporting for debugging purposes
            ini_set('display_errors', 1);
            error_reporting(E_ALL);

            $conn = @odbc_connect("Driver={Microsoft Access Driver (*.mdb, *.accdb)};dbq=$db", '', '', SQL_CUR_USE_ODBC);

            if (!$conn) {
                echo "<p class='error'>Connection Failed: " . odbc_errormsg($conn) . "</p>";
                exit;
            }

            // Handle delete request
            if (isset($_POST['delete_patient'])) {
                // Fetch PatientID from form submission
                $deletePatientID = $_POST['delete_patient_id'];

                if (empty($deletePatientID)) {
                    // If PatientID is empty, display an error message
                    echo "<p class='error'>No Patient ID provided for deletion. Please enter a valid Patient ID.</p>";
                } else {
                    // Perform a SELECT query to check if the PatientID exists
                    $sqlCheck = "SELECT COUNT(*) AS count FROM Patients WHERE PatientID = '$deletePatientID'";
                    $resultCheck = @odbc_exec($conn, $sqlCheck); // Suppress error
            
                    if (!$resultCheck) {
                        echo "<p class='error'>Error checking Patient ID: " . odbc_errormsg($conn) . "</p>";
                    } else {
                        $row = odbc_fetch_array($resultCheck);
                        $patientExists = $row['count'] > 0;

                        if ($patientExists) {
                            // Perform the DELETE operation if the PatientID exists
                            $sqlDelete = "DELETE FROM Patients WHERE PatientID = '$deletePatientID'";
                            $resultDelete = @odbc_exec($conn, $sqlDelete); // Suppress error
            
                            if (!$resultDelete) {
                                echo "<p class='error'>Error deleting patient: " . odbc_errormsg($conn) . "</p>";
                            } else {
                                echo "<div class='popup'>
                                        <p>Patient deleted successfully!</p>
                                        <p><a href='Search_Edit1.php'>Click here to return to the search page</a></p>
                                      </div>";
                            }
                        } else {
                            echo "<p class='error'>Patient with ID '$deletePatientID' not found.</p>";
                        }
                    }
                }
            }

            // Fetch all patients to display
            $sqlFetchPatients = "SELECT PatientID, FirstName, LastName FROM Patients";
            $resultPatients = @odbc_exec($conn, $sqlFetchPatients);

            if (!$resultPatients) {
                echo "<p class='error'>Error fetching patients: " . odbc_errormsg($conn) . "</p>";
            } else {
                echo "<h2>Delete Patient</h2>";
                echo "<table class='report-summary'>
                    <tr>
                        <th>Patient ID</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Action</th>
                    </tr>";

                // Loop through all patients and display in a table
                while ($row = odbc_fetch_array($resultPatients)) {
                    echo "<tr>
                        <td>" . htmlspecialchars($row['PatientID']) . "</td>
                        <td>" . htmlspecialchars($row['FirstName']) . "</td>
                        <td>" . htmlspecialchars($row['LastName']) . "</td>
                        <td>
                            <form method='POST' action='Delete_Patient.php'>
                                <input type='hidden' name='delete_patient_id' value='" . $row['PatientID'] . "'>
                                <button type='submit' name='delete_patient' onclick='return confirm(\"Are you sure you want to delete this patient?\");'>Delete</button>
                            </form>
                        </td>
                    </tr>";
                }

                echo "</table>";
            }

            odbc_close($conn);
            ?>

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
