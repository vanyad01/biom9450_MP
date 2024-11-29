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
    <title>Simple Web Page</title>
    <link rel="stylesheet" href="CSS/style.css">
    <script type="text/javascript" src="functions.js"></script>
</head>
<body>
 
    <header>
        <nav class="navbar practitioner-navbar">
            <div class="logo">
                <img src="../Images/logo.jpg" alt="Logo">
            </div>
            <h2>Welcome, Dr. <?php echo htmlspecialchars($username)?>!</h2>
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

        <?php include 'functions.php'; ?>

        <main class="main-dashboard">
            <?php
            $TTm = "<tr><td>Medication timetable not loaded yet.</td></tr>";  // medication timetable string.
            $TTd = "<tr><td>Diet timetable not loaded yet.</td></tr>";  // diet timetable string.
            ?>

            <div class="config-summary">
                <p>To update a patient's round details, please first select a round and date.</p>
                <form class="summary-form" action="Administration.php" method="POST">
                    <div class="form-row">
                        <label for="round-selection">Round selection:</label>
                        <div class="radio-group">
                            <label><input type="radio" name="med-roundSelection" id="morning" value="Morning" checked 
                                <?php echo (isset($_POST['med-roundSelection']) && $_POST['med-roundSelection'] == 'Morning') ? 'checked' : ''; ?> /> Morning</label>
                            <label><input type="radio" name="med-roundSelection" id="afternoon" value="Afternoon" 
                                <?php echo (isset($_POST['med-roundSelection']) && $_POST['med-roundSelection'] == 'Afternoon') ? 'checked' : ''; ?> /> Afternoon</label>
                            <label><input type="radio" name="med-roundSelection" id="night" value="Night" 
                                <?php echo (isset($_POST['med-roundSelection']) && $_POST['med-roundSelection'] == 'Night') ? 'checked' : ''; ?> /> Night</label>
                        </div>
                    </div>
                    <div class="form-row">
                        
                        <?php
                        // Fetch the earliest round date from the database
                        $conn = odbc_connect("Driver={Microsoft Access Driver (*.mdb, *.accdb)};dbq=$db", '', '', SQL_CUR_USE_ODBC);
                        $sql = "SELECT Rounds.TimeStamp
                                            FROM Rounds
                                        ORDER BY Rounds.TimeStamp;";
                                    $result = odbc_exec($conn, $sql);

                                    $startRoundDate = odbc_result($result, 'TimeStamp');
                                    $startRoundDate = date('Y-m-d', strtotime($startRoundDate));
                        ?>
                        <input type="text" class="date-placeholder" name="med-date" id="med-date" required 
                            value="<?php echo isset($_POST['med-date']) ? htmlspecialchars($_POST['med-date']) : ''; ?>" 
                            min="<?php echo $startRoundDate ?>" placeholder="Select the week starting from dd/mm/yyyy"
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
                                input.placeholder = 'Select the week starting from dd/mm/yyyy'; // Re-add placeholder
                            }
                        }

                        // Initialize field as text input with placeholder on page load
                        document.addEventListener('DOMContentLoaded', function () {
                            const dateInput = document.getElementById('round-date');
                            if (!dateInput.value) {
                                dateInput.type = 'text';
                                dateInput.classList.add('date-placeholder');
                                dateInput.placeholder = 'Select the week starting from dd/mm/yyyy';
                            }
                        });
                    </script>
                    <div class="form-row submit-row">
                        <input type="submit" id="submit-form" value="Load Patients" />
                    </div>
                </form>

                <p>Please choose a patient from the dropdown menu and click "Generate Timetable".</p>
                <form class="summary-form" action="Administration.php" method="POST">
                    <div class="form-row">
                        <select name="patient-name" id="patient-name" onchange="updateHiddenFields()">
                            <?php
                            // Only run SQL queries if a round selection is provided
                            if (isset($_POST['med-roundSelection']) && isset($_POST['med-date'])) {
                                // Connect to the database.
                                $conn = odbc_connect("Driver={Microsoft Access Driver (*.mdb, *.accdb)};dbq=$db", '', '', SQL_CUR_USE_ODBC);
                                
                                $medRound = $_POST['med-roundSelection'];
                                $medDate = $_POST['med-date'];
                                //$medRound = addslashes($_POST['med-roundSelection']);
                                //$medDate = date('m/d/Y', strtotime($_POST['med-date'])); // Convert date format for Access

                                // Make a query to get a list of all the patients matching the round.
                                $sql = "SELECT PatientMedicationDietRounds.PatientID, Patients.FirstName, Patients.LastName, 
                                               PatientMedicationDietRounds.MedicationID, Medications.Names AS MedicationName, Medications.Dosage, Medications.Route, 
                                               PatientMedicationDietRounds.MedicationStatus, Medications.StockStatus, Rounds.Type, Rounds.TimeStamp, Rounds.RoundID,
                                               Rounds.PractitionerID, Practitioners.Names AS PractitionerName
                                        FROM (Medications INNER JOIN 
                                                (Patients INNER JOIN 
                                                (Rounds INNER JOIN PatientMedicationDietRounds 
                                                        ON Rounds.RoundID = PatientMedicationDietRounds.RoundID) 
                                                        ON Patients.PatientID = PatientMedicationDietRounds.PatientID) 
                                                        ON Medications.MedicationID = PatientMedicationDietRounds.MedicationID) 
                                                        LEFT JOIN Practitioners ON Practitioners.PractitionerID = Rounds.PractitionerID
                                        WHERE Rounds.Type = '" . $medRound . "' AND
                                            Rounds.TimeStamp >= #" . $medDate . "#
                                        ORDER BY PatientMedicationDietRounds.PatientID;";
                                $result = odbc_exec($conn, $sql);

                                // Added error handling
                                //if (!$result) {
                                //    echo "Error executing query: " . odbc_errormsg($conn);
                                //    exit;
                                //}
                                $previousOption = "";
                                $allOptions = "<option selected disabled>Select a patient</option>";
                                if ($result) {
                                    while (odbc_fetch_row($result)) {
                                        $patientFirstName = odbc_result($result, "FirstName");
                                        $patientLastName = odbc_result($result, "LastName");
                                        $patientName = $patientFirstName . " " . $patientLastName; // join the names together for display. 
                                        $roundID = odbc_result($result, "RoundID");

                                        // Make the patient's name an option in the form list.
                                        // Use data- attribute to store "hidden values" along with the full name since
                                        //  the original SQL tables do not actually have a column for "full name".
                                        if (isset($_POST['patient-name'])) {
                                            $selected = ($patientName == $_POST['patient-name']) ? 'selected' : '';
                                            $optionString = "<option value=\"$patientName\" 
                                                        data-firstname=\"$patientFirstName\" 
                                                        data-lastname=\"$patientLastName\"
                                                        data-medround=\"$medRound\"
                                                        data-meddate=\"$medDate\" $selected>$patientName</option>";
                                        } else {
                                            $optionString = "<option value=\"$patientName\" 
                                                            data-firstname=\"$patientFirstName\" 
                                                            data-lastname=\"$patientLastName\"
                                                            data-medround=\"$medRound\"
                                                            data-meddate=\"$medDate\">$patientName</option>";
                                        }
                                        // Make sure that the patient's name appears as a select option only ONCE.
                                        if (strcmp($optionString, $previousOption) != 0) {
                                            $previousOption = $optionString;
                                            $allOptions .= $optionString;
                                        }
                                    }
                                    if (!odbc_fetch_row($result, 1)) {
                                        echo "<option selected disabled>Round plans have not been for your selection.</option>";
                                    } else {
                                        echo $allOptions;
                                    }
                                } else {
                                    echo "<option>Something went wrong!</option>";
                                }
                                odbc_close($conn);
                            } else {
                                echo "<option selected disabled>Select a patient</option>"; // default option.
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-row submit-row">
                        <!-- Hide the fields for storing the first and last name. -->
                        <!-- This will allow the same form values to be retained after the page refreshes,
                        and is accessible with a $_POST request. -->
                        <input type="hidden" name="patientFirstName" id="patientFirstName" value=""/>
                        <input type="hidden" name="patientLastName" id="patientLastName" value="" />
                        <input type="hidden" name="med-roundSelection" id="medRound" value="" />
                        <input type="hidden" name="med-date" id="medDate" value="" />
                        <input type="submit" id="submit-patient" value="New patient not selected..." disabled/>
                    </div>
                </form>
            </div>


            <!-- PRINT OUT THE PATIENT'S INFO -->
            <?php
            if (isset($_POST['patientFirstName'])) {
                getPatientProfile();
            }
            ?>

            <br>

            <!-- PRINT OUT THE MEDICATION ROUND FOR A WEEK. -->
            <?php
            // Check if the patient's first name has been posted since this means View Patient Round has been clicked.
            if (isset($_POST['patientFirstName'])) {
                $TTm = getMedicationTimetableStr();


                // Get the POST data to refine the SQL query.
                $firstName = $_POST['patientFirstName'];
                $lastName = $_POST['patientLastName'];
                $medRound = $_POST['med-roundSelection'];
                $medDate = $_POST['med-date'];

                // Get the dates for the week.
                $medDateStart = date('Y-m-d', strtotime($medDate));
                $medDateEnd = date('Y-m-d', strtotime($medDate . ' +6 days'));

                // To dispense medications, create new forms and send the data.
                echo "<div class=\"medDispense-div\">";
                echo "<form class=\"dispense-form\" action=\"Administration.php\" method=\"POST\">";

                // Pick the date.
                echo "<div class=\"form-row\">";
                echo "<label for=\"dispense-date\">Dispense Medication</label>";
                echo "<input type=\"date\" id=\"dispense-date\" name=\"dispense-date\" min=\"$medDateStart\" max=\"$medDateEnd\" required>";
                echo "</div>";

                // Pick the medication.
                echo "<div class=\"form-row\">";
                //echo "<label for=\"medication-name\">Select a medication: </label>";
                echo "<select name=\"medication-name\" id=\"medication-name\" required>";
                echo "<option value=\"\" selected disabled>Select a medication</option>";

                // Connect to the database.
                $conn = odbc_connect("Driver={Microsoft Access Driver (*.mdb, *.accdb)};dbq=$db", '', '', SQL_CUR_USE_ODBC);

                // Make a query to get a list of all the medications matching the round.
                $sql = "SELECT Medications.Names AS MedicationName
                                        FROM (Medications INNER JOIN 
                                                (Patients INNER JOIN 
                                                    (Rounds INNER JOIN PatientMedicationDietRounds 
                                                            ON Rounds.RoundID = PatientMedicationDietRounds.RoundID) 
                                                            ON Patients.PatientID = PatientMedicationDietRounds.PatientID) 
                                                            ON Medications.MedicationID = PatientMedicationDietRounds.MedicationID) 
                                                            LEFT JOIN Practitioners ON Practitioners.PractitionerID = Rounds.PractitionerID
                                        WHERE Rounds.Type = '" . $medRound . "' AND
                                              Rounds.TimeStamp >= #" . $medDate . "# AND
                                              Patients.FirstName = '" . $firstName . "' AND
                                              Patients.LastName = '" . $lastName . "'
                                        ORDER BY PatientMedicationDietRounds.PatientID;";

                $result = odbc_exec($conn, $sql);

                $previousOption = "";
                if ($result) {
                    while (odbc_fetch_row($result)) {
                        $MedicationName = odbc_result($result, "MedicationName");
                        $optionString = "<option value=\"$MedicationName\">$MedicationName</option>";

                        // Make sure that the medication appears as a select option only ONCE.
                        if (strcmp($optionString, $previousOption) != 0) {
                            echo $optionString;
                            $previousOption = $optionString;
                        }
                    }
                } else {
                    echo "<option>Something went wrong!</option>";
                }
                odbc_close($conn);
                echo "</select>";
                echo "</div>";

                // Pick the medication status (i.e., has it been given or not).
                echo "<div class=\"form-row\">";
                //echo "<label for=\"medication-status\">Select a status: </label>";
                echo "<select name=\"medication-status\" id=\"medication-status\" required>";
                echo "<option value=\"\" selected disabled>Select a status</option>";
                echo "<option value=\"Given\">&#x1F7E2; = Given</option>";
                echo "<option value=\"Refused\">&#x1F534; = Refused</option>";
                echo "<option value=\"Missed\">&#x1F7E1; = Missed</option>";
                echo "<option value=\"Ceased\">&#x1F7E3; = Ceased</option>";
                echo "<option value=\"No stock\">&#x1F7E4; = No Stock</option>";
                echo "</select>";
                echo "</div>";

                // Hidden elements to restore the fields on submission of this form.
                $fullname = $firstName . " " . $lastName;
                echo "<input type=\"hidden\" name=\"patientFirstName\" id=\"dispFirstName\" value=\"$firstName\"/>";
                echo "<input type=\"hidden\" name=\"patientLastName\" id=\"dispLastName\" value=\"$lastName\"/>";
                echo "<input type=\"hidden\" name=\"patient-name\" id=\"dispFullname\" value=\"$fullname\"/>";
                echo "<input type=\"hidden\" name=\"med-roundSelection\" id=\"dispRoundType\" value=\"$medRound\"/>";
                echo "<input type=\"hidden\" name=\"med-date\" id=\"dispDate\" value=\"$medDate\"/>";
                echo "<input type=\"hidden\" name=\"roundID\" id=\"dispRoundID\" value=\"$roundID\"/>";


                // Submit button.
                echo "<div class=\"form-row submit-row\">";
                echo "<input type=\"submit\" id=\"submit-patient\" value=\"Dispense Round\" />";
                echo "</div>";
                echo "</form>";


                // Show the legend
                echo "<div class=\"medLegend-div\" id=\"medLegend-div\">";
                echo "<p class=\"legend-title\">Dispense Status Legend:</p>";
                echo "<ul class=\"medLegend\">";
                echo "<li>&#x1F7E2; = given</li>";
                echo "<li>&#x1F534; = refused</li>";
                echo "<li>&#x1F7E1; = missed</li>";
                echo "<li>&#x1F7E3; = ceased</li>";
                echo "<li>&#x1F7E4; = no stock</li>";
                echo "<li>&#x25EF; = planned</li>";
                echo "</ul>";
                echo "</div>";
                echo "</div>";
            }
            ?>

            <div class="timetable"><br>
                <!-- print out table here. -->
                <strong>Medication Timetable</strong>
                <table class="report-summary" id="medTable"></table>
            </div>


            <!-- DISPENSE THE ROUND AND UPDATE THE TABLE. -->
            <?php
            // When submitted, update the table.
            if (isset($_POST['dispense-date'])) {

                // Connect to the database.
                $conn = odbc_connect("Driver={Microsoft Access Driver (*.mdb, *.accdb)};dbq=$db", '', '', SQL_CUR_USE_ODBC);

                // Get the POST data to refine the SQL query.
                $firstName = $_POST['patientFirstName'];
                $lastName = $_POST['patientLastName'];
                $medRound = $_POST['med-roundSelection'];
                $DispenseDate = $_POST['dispense-date'];
                $MedicationName = $_POST['medication-name'];
                $MedicationStatus = $_POST['medication-status'];

                $DispenseDateFormatted = date('Y-m-d', strtotime($DispenseDate));
                // Check that a round has been planned for the desired dispense date. i.e., the dispense date should exist in the Rounds table.
                $sql = "SELECT Rounds.RoundID
                        FROM Rounds
                        WHERE Rounds.TimeStamp = #" . $DispenseDate . "#;";
                $result = odbc_exec($conn, $sql);
                $dateErrorFlag = 0; // an error flag to prevent other forms from being shown if the date hasn't been set yet.
            
                if ($result) {
                    $TTm = getMedicationTimetableStr();
                    // If no round was found, notify the user that their date selection was bad.
                    if (!odbc_fetch_row($result)) {
                        $errorMsg = "The date " . $DispenseDate . " has not yet been allocated a round for " . $firstName . " " . $lastName . " yet. You cannot dispense medication for this date. To add a round, please go to the Patients page.";
                        echo $errorMsg;
                        $dateErrorFlag = 1;
                    }
                } else {
                    echo "ERROR: The query could not go through to the database.";
                }


                // If there were no errors with the date chosen for dispensing, then update the tables.
                if (!$dateErrorFlag) {
                    // Get the Round ID and Patient ID based on the above.
                    $sql = "SELECT PatientMedicationDietRounds.PatientID, Patients.FirstName, Patients.LastName, 
                                           PatientMedicationDietRounds.MedicationID, Medications.Names AS MedicationName, Medications.Dosage, Medications.Route, 
                                           PatientMedicationDietRounds.MedicationStatus, Medications.StockStatus, Rounds.Type, Rounds.TimeStamp, Rounds.RoundID,
                                           Rounds.PractitionerID, Practitioners.Names AS PractitionerName
                        FROM (Medications INNER JOIN 
                                (Patients INNER JOIN 
                                    (Rounds INNER JOIN PatientMedicationDietRounds 
                                            ON Rounds.RoundID = PatientMedicationDietRounds.RoundID) 
                                            ON Patients.PatientID = PatientMedicationDietRounds.PatientID) 
                                            ON Medications.MedicationID = PatientMedicationDietRounds.MedicationID) 
                                            LEFT JOIN Practitioners ON Practitioners.PractitionerID = Rounds.PractitionerID
                        WHERE Rounds.Type = '" . $medRound . "' AND
                              Rounds.TimeStamp = #" . $DispenseDate . "# AND
                              Patients.FirstName = '" . $firstName . "' AND
                              Patients.LastName = '" . $lastName . "' AND
                              Medications.Names = '" . $MedicationName . "'
                        ORDER BY PatientMedicationDietRounds.PatientID;";
                    $result = odbc_exec($conn, $sql);

                    if ($result) {
                        while (odbc_fetch_row($result)) {
                            $PatientID = odbc_result($result, "PatientID");
                            $roundID = odbc_result($result, "RoundID");
                        }
                    } else {
                        $TTm = getMedicationTimetableStr();
                        echo "ERROR: Could not find the requested round or patient (database connection failed).";
                    }


                    // Update the PatientMedicationDietRounds table with correct medication status.
                    $sql = "UPDATE PatientMedicationDietRounds
                           SET MedicationStatus = '" . $MedicationStatus . "'
                         WHERE RoundID = '" . $roundID . "' AND
                               PatientID = '" . $PatientID . "';";
                    $result = odbc_exec($conn, $sql);
                    if (!$result) {
                        $TTm = getMedicationTimetableStr();
                        echo "ERROR: Could not update PatientMedicationDietRounds table (database connection failed).";
                    }


                    // Find the practitioner based on the username of the current session.
                    $sql = "SELECT Practitioners.PractitionerID, Practitioners.Names
                        FROM Practitioners
                        WHERE UserName = '" . $username . "';";
                    $result = odbc_exec($conn, $sql);

                    if ($result) {
                        // Fetch all rows from the SELECT query before performing any UPDATEs
                        $practitioners = [];
                        while (odbc_fetch_row($result)) {
                            $practitioners[] = odbc_result($result, "PractitionerID");
                            $practitionerName = odbc_result($result, 'Names');
                        }

                        // Iterate through the fetched PractitionerIDs and update the Rounds table
                        foreach ($practitioners as $PractitionerID) {
                            $updateSQL = "UPDATE Rounds
                                          SET PractitionerID = '$PractitionerID'
                                          WHERE RoundID = '$roundID' AND Rounds.TimeStamp = #$DispenseDate#;";

                            // Execute the UPDATE query
                            $updateResult = odbc_exec($conn, $updateSQL);

                            // Check if the update failed
                            if (!$updateResult) {
                                echo "Could not update Rounds table (database connection failed).";
                            }
                        }
                    } else {
                        echo "ERROR: Could not search Practitioners table (database connection failed).";
                    }


                    // Update Medications table if out of stock.
                    if ($MedicationStatus == "No stock") {
                        $sql = "UPDATE Medications
                               SET StockStatus = '" . $MedicationStatus . "'
                             WHERE Names = '" . $MedicationName . "';";
                    } else { // if "no stock" was NOT selected, then medication must be in stock.
                        $sql = "UPDATE Medications
                               SET StockStatus = 'In stock'
                             WHERE Names = '" . $MedicationName . "';";
                    }
                    $result = odbc_exec($conn, $sql);
                    if (!$result) {
                        echo "Could not update Medications table.";
                    }

                    odbc_close($conn);

                    // If the medication was refused, send an email to the facility director
                    //  with the patient name and practitioner ID.
                    if ($MedicationStatus == "Refused") {
                        // Create the string.
                        $msgStr = "To%20the%20Director,%0A%0D%0A";
                        $msgStr .= "This%20is%20an%20email%20to%20notify%20you%20that%20Patient%20" . $PatientID . "%20(" . $firstName . "%20" . $lastName . ")%20has%20refused%20medication%20for%20the%20" . $medRound . "%20round%20on%20" . $DispenseDate . ".";
                        $msgStr .= "%0A%0D%0AKind%20regards,%0A%0D%0A" . $practitionerName;
                        $mailStr = "<form name=\"mail-notif\" action=\"mailto:director@medtrak.com.au?subject=Medication%20Refused&body=" . $msgStr . "\" method=\"POST\" onsubmit=\"hideButton()\">
                                    <div class=\"form-row submit-row\">
                                    <input type=\"submit\" id=\"mail-sent\" value=\"Notify Director of Refusal\"> 
                                    </div></form>";

                        // Display the button.
                        echo $mailStr;
                    }

                    $TTm = getMedicationTimetableStr();
                }
            }
            ?>

            <!-- Use Javascript to update the tables. -->
            <script>
                // Show table.
                document.getElementById('medTable').innerHTML = "<?php echo $TTm; ?>";
            </script>

            <hr />

            <!-- Repeat same process to show diet timetable. -->
            <?php
            // Check if the patient's first name has been posted since this means View Patient Round has been clicked.
            if (isset($_POST['patientFirstName'])) {
                $TTd = getDietTimetableStr();


                // Get the POST data to refine the SQL query.
                $firstName = $_POST['patientFirstName'];
                $lastName = $_POST['patientLastName'];
                $dietRound = $_POST['med-roundSelection'];
                $dietDate = $_POST['med-date'];

                // Get the dates for the week.
                $dietDateStart = date('Y-m-d', strtotime($dietDate));
                $dietDateEnd = date('Y-m-d', strtotime($dietDate . ' +6 days'));

                // To dispense medications, create new forms and send the data.
                echo "<div class=\"dietDispense-div\">";
                echo "<hr>";
                echo "<form class=\"dispense-form\" action=\"Administration_Wilson2.php\" method=\"POST\">";

                // Pick the date.
                echo "<div class=\"form-row\">";
                echo "<label for=\"dispenseDiet-date\">Dispense Diet Regime: </label>";
                echo "<input type=\"date\" id=\"dispenseDiet-date\" name=\"dispenseDiet-date\" min=\"$dietDateStart\" max=\"$dietDateEnd\" required>";
                echo "</div>";

                // Pick the meal.
                echo "<div class=\"form-row\">";
                //echo "<label for=\"meal-name\">Select the meal to update: </label>";
                echo "<select name=\"meal-name\" id=\"meal-name\" required>";
                echo "<option value=\"\" selected disabled>Select a meal</option>";

                // Connect to the database.
                $conn = odbc_connect("Driver={Microsoft Access Driver (*.mdb, *.accdb)};dbq=$db", '', '', SQL_CUR_USE_ODBC);

                // Make a query to get a list of all the medications matching the round.
                $sql = "SELECT DietRegimes.Description AS MealName
                                        FROM (DietRegimes INNER JOIN 
                                                (Patients INNER JOIN 
                                                    (Rounds INNER JOIN PatientMedicationDietRounds 
                                                            ON Rounds.RoundID = PatientMedicationDietRounds.RoundID) 
                                                            ON Patients.PatientID = PatientMedicationDietRounds.PatientID) 
                                                            ON DietRegimes.DietID = PatientMedicationDietRounds.DietID) 
                                                            LEFT JOIN Practitioners ON Practitioners.PractitionerID = Rounds.PractitionerID
                                        WHERE Rounds.Type = '" . $dietRound . "' AND
                                              Rounds.TimeStamp >= #" . $dietDate . "# AND
                                              Patients.FirstName = '" . $firstName . "' AND
                                              Patients.LastName = '" . $lastName . "'
                                        ORDER BY PatientMedicationDietRounds.PatientID;";

                $result = odbc_exec($conn, $sql);

                $previousOption = "";
                if ($result) {
                    while (odbc_fetch_row($result)) {
                        $MealName = odbc_result($result, "MealName");
                        $optionString = "<option value=\"$MealName\">$MealName</option>";

                        // Make sure that the medication appears as a select option only ONCE.
                        if (strcmp($optionString, $previousOption) != 0) {
                            echo $optionString;
                            $previousOption = $optionString;
                        }
                    }
                } else {
                    echo "<option>Something went wrong!</option>";
                }
                odbc_close($conn);
                echo "</select>";
                echo "</div>";

                // Pick the meal status (i.e., has it been given or not).
                echo "<div class=\"form-row\">";
                //echo "<label for=\"meal-status\">Select a status: </label>";
                echo "<select name=\"meal-status\" id=\"meal-status\" required>";
                echo "<option value=\"\" selected disabled>Select a status</option>";
                echo "<option value=\"Given\">&#x1F7E2; = Given</option>";
                echo "<option value=\"Refused\">&#x1F534; = Refused</option>";
                echo "<option value=\"Missed\">&#x1F7E1; = Missed</option>";
                echo "<option value=\"Fasting\">&#x1F7E3; = Fasting</option>";
                echo "</select>";
                echo "</div>";


                // Hidden elements to restore the fields on submission of this form.
                $fullname = $firstName . " " . $lastName;
                echo "<input type=\"hidden\" name=\"patientFirstName\" id=\"dispFirstName\" value=\"$firstName\"/>";
                echo "<input type=\"hidden\" name=\"patientLastName\" id=\"dispLastName\" value=\"$lastName\"/>";
                echo "<input type=\"hidden\" name=\"patient-name\" id=\"dispFullname\" value=\"$fullname\"/>";
                echo "<input type=\"hidden\" name=\"med-roundSelection\" id=\"dispRoundType\" value=\"$dietRound\"/>";
                echo "<input type=\"hidden\" name=\"med-date\" id=\"dispDate\" value=\"$dietDate\"/>";
                echo "<input type=\"hidden\" name=\"roundID\" id=\"dispRoundID\" value=\"$roundID\"/>";


                // Submit button.
                echo "<div class=\"form-row submit-row\">";
                echo "<input type=\"submit\" id=\"submit-patient\" value=\"Dispense Round\" />";
                echo "</div>";
                echo "</form>";


                // Show the legend
                echo "<div class=\"medLegend-div\">";
                echo "<p class=\"legend-title\">Dispense status legend: </p>";
                echo "<ul class=\"medLegend\">";
                echo "<li>&#x1F7E2; = given</li>";
                echo "<li>&#x1F534; = refused</li>"; // added, 27-11-24
                echo "<li>&#x1F7E1; = missed</li>";
                echo "<li>&#x1F7E3; = fasting</li>";
                echo "<li>&#x25EF; = planned</li>";
                echo "</ul>";
                echo "</div>";

                echo "</div>";
            }
            ?>


            <div class="timetable"><br>
                <!-- print out table here. -->
                <strong>Diet Timetable</strong>
                <table class="report-summary" id="dietTable"></table>
            </div><br><br>


            <!-- DISPENSE THE ROUND AND UPDATE THE TABLE. -->
            <?php
            // When submitted, update the table.
            if (isset($_POST['dispenseDiet-date'])) {

                // Connect to the database.
                $conn = odbc_connect("Driver={Microsoft Access Driver (*.mdb, *.accdb)};dbq=$db", '', '', SQL_CUR_USE_ODBC);

                // Get the POST data to refine the SQL query.
                $firstName = $_POST['patientFirstName'];
                $lastName = $_POST['patientLastName'];
                $dietRound = $_POST['med-roundSelection'];
                $DispenseDate = $_POST['dispenseDiet-date'];
                $MealName = $_POST['meal-name'];
                $MealStatus = $_POST['meal-status'];

                $DispenseDateFormatted = date('Y-m-d', strtotime($DispenseDate));
                // Check that a round has been planned for the desired dispense date. i.e., the dispense date should exist in the Rounds table.
                $sql = "SELECT Rounds.RoundID
                        FROM Rounds
                        WHERE Rounds.TimeStamp = #" . $DispenseDate . "#;";
                $result = odbc_exec($conn, $sql);
                $dateErrorFlag = 0; // an error flag to prevent other forms from being shown if the date hasn't been set yet.
            
                if ($result) {
                    $TTd = getDietTimetableStr();
                    // If no round was found, notify the user that their date selection was bad.
                    if (!odbc_fetch_row($result)) {
                        $errorMsg = "The date " . $DispenseDate . " has not yet been allocated a round for " . $firstName . " " . $lastName . " yet. You cannot dispense meals for this date. To add a round, please go to the Patients page.";
                        echo $errorMsg;
                        $dateErrorFlag = 1;
                    }
                } else {
                    echo "ERROR: The query could not go through to the database.";
                }


                // If there were no errors with the date chosen for dispensing, then update the tables.
                if (!$dateErrorFlag) {
                    // Get the Round ID based on the above.
                    $sql = "SELECT PatientMedicationDietRounds.PatientID, Patients.FirstName, Patients.LastName, 
                                           PatientMedicationDietRounds.DietID, DietRegimes.Description AS MealName, DietRegimes.Rules, 
                                           PatientMedicationDietRounds.DietStatus, Rounds.Type, Rounds.TimeStamp, Rounds.RoundID,
                                           Rounds.PractitionerID, Practitioners.Names AS PractitionerName
                            FROM (DietRegimes INNER JOIN 
                                    (Patients INNER JOIN 
                                        (Rounds INNER JOIN PatientMedicationDietRounds 
                                                ON Rounds.RoundID = PatientMedicationDietRounds.RoundID) 
                                                ON Patients.PatientID = PatientMedicationDietRounds.PatientID) 
                                                ON DietRegimes.DietID = PatientMedicationDietRounds.DietID) 
                                                LEFT JOIN Practitioners ON Practitioners.PractitionerID = Rounds.PractitionerID
                            WHERE Rounds.Type = '" . $dietRound . "' AND
                                  Rounds.TimeStamp = #" . $DispenseDate . "# AND
                                  Patients.FirstName = '" . $firstName . "' AND
                                  Patients.LastName = '" . $lastName . "' AND
                                  DietRegimes.Description = '" . $MealName . "'
                            ORDER BY PatientMedicationDietRounds.PatientID;";
                    $result = odbc_exec($conn, $sql);

                    if ($result) {
                        while (odbc_fetch_row($result)) {
                            $PatientID = odbc_result($result, "PatientID");
                            $roundID = odbc_result($result, "RoundID");
                        }
                    } else {
                        $TTd = getDietTimetableStr();
                        echo "ERROR: Could not find the requested round or patient (database connection failed).";
                    }


                    // Update the PatientMedicationDietRounds table with correct medication status.
                    $sql = "UPDATE PatientMedicationDietRounds
                           SET DietStatus = '" . $MealStatus . "'
                         WHERE RoundID = '" . $roundID . "' AND
                               PatientID = '" . $PatientID . "';";
                    $result = odbc_exec($conn, $sql);
                    if (!$result) {
                        $TTd = getDietTimetableStr();
                        echo "ERROR: Could not update PatientMedicationDietRounds table (database connection failed).";
                    }


                    $sql = "SELECT Practitioners.PractitionerID, Practitioners.Names
                        FROM Practitioners
                        WHERE UserName = '" . $username . "';";
                    $result = odbc_exec($conn, $sql);

                    if ($result) {
                        // Fetch all rows from the SELECT query before performing any UPDATEs
                        $practitioners = [];
                        while (odbc_fetch_row($result)) {
                            $practitioners[] = odbc_result($result, "PractitionerID");
                            $practitionerName = odbc_result($result, 'Names');
                        }

                        // Iterate through the fetched PractitionerIDs and update the Rounds table
                        foreach ($practitioners as $PractitionerID) {
                            $updateSQL = "UPDATE Rounds
                                          SET PractitionerID = '$PractitionerID'
                                          WHERE RoundID = '$roundID' AND Rounds.TimeStamp = #$DispenseDate#;";

                            // Execute the UPDATE query
                            $updateResult = odbc_exec($conn, $updateSQL);

                            // Check if the update failed
                            if (!$updateResult) {
                                echo "Could not update Rounds table (database connection failed).";
                            }
                        }
                    } else {
                        echo "ERROR: Could not search Practitioners table (database connection failed).";
                    }
                    odbc_close($conn);

                    // added, 27-11-24
                    // If the meal was refused, send an email to the facility director
                    //  with the patient name and practitioner ID.
                    if ($MealStatus == "Refused") {
                        // Create the string.
                        $msgStr = "To%20the%20Director,%0A%0D%0A";
                        $msgStr .= "This%20is%20an%20email%20to%20notify%20you%20that%20Patient%20" . $PatientID . "%20(" . $firstName . "%20" . $lastName . ")%20has%20refused%20the%20meal%20for%20the%20" . $dietRound . "%20round%20on%20" . $DispenseDate . ".";
                        $msgStr .= "%0A%0D%0AKind%20regards,%0A%0D%0A" . $practitionerName;
                        $mailStr = "<form name=\"mail-notif\" action=\"mailto:director@medtrak.com.au?subject=Medication%20Refused&body=" . $msgStr . "\" method=\"POST\" onsubmit=\"hideButton()\">
                                    <div class=\"form-row submit-row\">
                                    <input type=\"submit\" id=\"mail-sent\" value=\"Notify Director of Refusal\"> 
                                    </div></form>";

                        // Display the button.
                        echo $mailStr;
                    }

                    $TTd = getDietTimetableStr(); // generate timetable again after updating.
                }
            }
            ?>

            <!-- Use Javascript to update the tables. -->
            <script>
                // Show table.
                document.getElementById('dietTable').innerHTML = "<?php echo $TTd; ?>";
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