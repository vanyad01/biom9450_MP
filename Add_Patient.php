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
<html>
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Patient</title>
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
                <div class="config-summary">
                    <h2>Add New Patient</h2>
                    <?php
                    // Database connection
                    $conn = @odbc_connect("Driver={Microsoft Access Driver (*.mdb, *.accdb)};dbq=$db", '', '', SQL_CUR_USE_ODBC);

                    if (!$conn) {
                        echo "<p class='error'>Connection Failed: " . odbc_errormsg($conn) . "</p>";
                    }

                    // Check if the form is submitted
                    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                        // Get form data
                        $patientID = $_POST['patient_id'];
                        $firstName = $_POST['first_name'];
                        $lastName = $_POST['last_name'];
                        $dob = $_POST['dob'];
                        $gender = $_POST['gender'];
                        $age = $_POST['age'];
                        $roomNumber = $_POST['room_number'];

                        // Check if the PatientID already exists
                        $sqlCheck = "SELECT COUNT(*) AS count FROM Patients WHERE PatientID = '$patientID'";
                        $resultCheck = @odbc_exec($conn, $sqlCheck); // Suppress error
                        if (!$resultCheck) {
                            echo "<p class='error'>Error checking Patient ID: " . odbc_errormsg($conn) . "</p>";
                        } else {
                            $row = odbc_fetch_array($resultCheck);
                            $patientExists = $row['count'] > 0;

                            if ($patientExists) {
                                echo "<p class='error'>Patient ID '$patientID' is already taken. Please choose another one.</p>";
                            } else {
                                // Insert query if PatientID does not exist
                                $sqlInsert = "INSERT INTO Patients (PatientID, FirstName, LastName, DateOfBirth, Gender, Age, RoomNumber)
                                            VALUES ('$patientID', '$firstName', '$lastName', '$dob', '$gender', '$age', '$roomNumber')";

                                $resultInsert = @odbc_exec($conn, $sqlInsert); // Suppress error
                    
                                if (!$resultInsert) {
                                    echo "<p class='error'>Error adding patient: " . odbc_errormsg($conn) . "</p>";
                                } else {
                                    echo "<div class='popup' id='successPopup'>
                                            <p>Patient added successfully!</p>
                                            <p><a href='Search_Edit1.php'>Click here to return to the search page</a></p>
                                        </div>";
                                }
                            }
                        }
                    }

                    odbc_close($conn);
                    ?>

                    <!-- Patient information form -->
                    <form method="POST" onsubmit="return validateForm()" class="patient-form">
                        <div class="form-row">
                            <input type="text" name="patient_id" placeholder="Patient ID" id="patient_id" required>
                        </div>

                        <div class="form-row">
                            <input type="text" name="first_name" placeholder="First Name" id="first_name" required>
                        </div>

                        <div class="form-row">
                            <input type="text" name="last_name" placeholder="Last Name" id="last_name" required>
                        </div>

                        <div class="form-row">
                            <input type="text" name="round-date" id="round-date" 
                                class="date-placeholder"
                                placeholder="dd/mm/yyyy"
                                onfocus="enableDateInput(this)"
                                onblur="showPlaceholder(this)" />
                        </div>

                        <div class="form-row">
                            <select name="gender" id="gender" required>
                                <option value="">Select Gender</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </div>

                        <div class="form-row">
                            <input type="number" name="age" placeholder="Age" id="age" required>
                        </div>

                        <div class="form-row">
                            <input type="text" name="room_number" placeholder="Room Number" id="room_number" required>
                        </div>

                        <div class="form-row submit-row">
                            <input type="submit" id="submit-form" value="Apply filters">
                        </div>
                        <div class="form-row submit-row">
                            <a href="Delete_Patient.php" class="delete-link">Click here to delete a patient</a>
                        </div>
                    </form>
                </div>
                <script>
                    // Transform the text field into a date picker on focus
                    function enableDateInput(input) {
                        input.type = 'date';
                        input.classList.remove('date-placeholder');
                        input.placeholder = ''; // Remove placeholder when focused
                    }

                    // Revert back to text input with placeholder when unfocused and empty
                    function showPlaceholder(input) {
                        if (!input.value) {
                            input.type = 'text';
                            input.classList.add('date-placeholder');
                            input.placeholder = 'Date of Birth dd/mm/yyyy'; // Re-add placeholder
                        }
                    }

                    // Initialize field as text input with placeholder on page load
                    document.addEventListener('DOMContentLoaded', function () {
                        const dateInput = document.getElementById('round-date');
                        if (!dateInput.value) {
                            dateInput.type = 'text';
                            dateInput.classList.add('date-placeholder');
                            dateInput.placeholder = 'Date of Birth dd/mm/yyyy';
                        }
                    });
                </script>

                <!-- Link to delete patient -->
                
                
                <script>
                    // Simple validation for the form before submitting
                    function validateForm() {
                        const patientID = document.getElementById("patient_id").value;
                        const firstName = document.getElementById("first_name").value;
                        const lastName = document.getElementById("last_name").value;
                        const dob = document.getElementById("dob").value;
                        const gender = document.getElementById("gender").value;
                        const age = document.getElementById("age").value;
                        const roomNumber = document.getElementById("room_number").value;

                        if (!patientID || !firstName || !lastName || !dob || !gender || !age || !roomNumber) {
                            alert("All fields are required.");
                            return false;
                        }
                        return true;
                    }

                    // Show the popup after the form is successfully submitted
                    window.onload = function() {
                        var successPopup = document.getElementById("successPopup");
                        if (successPopup) {
                            successPopup.style.display = "block"; // Show the success popup
                        }
                    };
                </script>
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
