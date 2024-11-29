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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple Web Page</title>
    <link rel="stylesheet" href="CSS/style.css">
    <script type="text/javascript" src="functions.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- use jQuery library. -->
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
                <p>
                    To refine the medication summary, please fill in the following fields. Leave the fields blank to show all details.
                </p>
                <form class="summary-form" id="summary-form" method="POST" onchange="checkSelection()">

                    <!-- Patient's First Name -->
                    <div class="form-row">
                        <input type="text" name="patient-firstName" placeholder="Patient's first name" id="patient-firstName" 
                            value="<?php echo isset($_POST['patient-firstName']) ? htmlspecialchars($_POST['patient-firstName']) : ''; ?>" />
                    </div>

                    <!-- Patient's Last Name -->
                    <div class="form-row">
                        <input type="text" name="patient-lastName" placeholder="Patient's last name" id="patient-lastName" 
                            value="<?php echo isset($_POST['patient-lastName']) ? htmlspecialchars($_POST['patient-lastName']) : ''; ?>" />
                    </div>

                    <!-- Round Date -->
                    <div class="form-row">
                        <input type="text" name="round-date" id="round-date" 
                            class="date-placeholder"
                            placeholder="dd/mm/yyyy"
                            onfocus="enableDateInput(this)"
                            onblur="showPlaceholder(this)" />
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
                                input.placeholder = 'Round-date dd/mm/yyyy'; // Re-add placeholder
                            }
                        }

                        // Initialize field as text input with placeholder on page load
                        document.addEventListener('DOMContentLoaded', function () {
                            const dateInput = document.getElementById('round-date');
                            if (!dateInput.value) {
                                dateInput.type = 'text';
                                dateInput.classList.add('date-placeholder');
                                dateInput.placeholder = 'Round-date dd/mm/yyyy';
                            }
                        });
                    </script>


                    <!-- Date Selection -->
                    <div class="form-row round-selection">
                        <label for="date-selection">Include:</label>
                        <div class="radio-group">
                            <label><input type="radio" name="date-selection" id="any" value="All" checked 
                                <?php echo (isset($_POST['date-selection']) && $_POST['date-selection'] == 'All') ? 'checked' : ''; ?> /> All dates</label>
                            <label><input type="radio" name="date-selection" id="only" value="Only" 
                                <?php echo (isset($_POST['date-selection']) && $_POST['date-selection'] == 'Only') ? 'checked' : ''; ?> /> Selected date</label>
                            <label><input type="radio" name="date-selection" id="prior" value="Prior" 
                                <?php echo (isset($_POST['date-selection']) && $_POST['date-selection'] == 'Prior') ? 'checked' : ''; ?> /> Week prior</label>
                            <label><input type="radio" name="date-selection" id="post" value="Post" 
                                <?php echo (isset($_POST['date-selection']) && $_POST['date-selection'] == 'Post') ? 'checked' : ''; ?> /> Week after</label>
                        </div>
                    </div>

                    <!-- Round Selection -->
                    <div class="form-row round-selection">
                        <label for="round-selection">Round selection:</label>
                        <div class="radio-group">
                            <label><input type="radio" name="round-selection" id="all" value="" checked 
                                <?php echo (isset($_POST['round-selection']) && $_POST['round-selection'] == '') ? 'checked' : ''; ?> /> All rounds</label>
                            <label><input type="radio" name="round-selection" id="morning" value="Morning" 
                                <?php echo (isset($_POST['round-selection']) && $_POST['round-selection'] == 'Morning') ? 'checked' : ''; ?> /> Morning</label>
                            <label><input type="radio" name="round-selection" id="afternoon" value="Afternoon" 
                                <?php echo (isset($_POST['round-selection']) && $_POST['round-selection'] == 'Afternoon') ? 'checked' : ''; ?> /> Afternoon</label>
                            <label><input type="radio" name="round-selection" id="night" value="Night" 
                                <?php echo (isset($_POST['round-selection']) && $_POST['round-selection'] == 'Night') ? 'checked' : ''; ?> /> Night</label>
                        </div>
                    </div>

                    <!-- Practitioner's Name -->
                    <div class="form-row">
                        <input type="text" name="prac-name" placeholder="Practitioner's Name" id="prac-name" 
                            value="<?php echo isset($_POST['prac-name']) ? htmlspecialchars($_POST['prac-name']) : ''; ?>" />
                    </div>

                    <!-- Order By Checkbox -->
                    <div class="form-row">
                        <label for="pracname">Order by practitioner</label>
                        <input type="checkbox" name="order-by" id="pracname" value="pracname" 
                            <?php echo (isset($_POST['order-by']) && $_POST['order-by'] == 'pracname') ? 'checked' : ''; ?> />
                    </div>

                    <!-- Submit Button -->
                    <div class="form-row submit-row">
                        <input type="submit" id="submit-form" value="Apply filters">
                    </div>

                </form>
            </div>


            <!-- A container to display the SQL result. -->
            <div id="medication-summary"></div>
            
            
            <script>
            $(document).ready(function() {
                // When the form is submitted
                $('#summary-form').submit(function(e) {
                    e.preventDefault(); // Prevent the form from submitting the traditional way

                    // Gather all form data
                    var formData = $(this).serialize(); // Serializes the form data (e.g., patient-firstName=John&round-date=2024-11-26)

                    // Send the data using AJAX
                    $.ajax({
                        type: 'POST', // Use POST method to send the data
                        url: 'getMedicationSummary.php', // URL to your PHP script
                        data: formData, // The serialized form data
                        success: function(response) {
                            // If the request was successful, display the response in the response container
                            $('#medication-summary').html(response);
                        },
                        error: function() {
                            // Handle any errors during the AJAX request
                            $('#medication-summary').html('The POST request failed. Please refresh and try again.');
                        }
                    });
                });
            });
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