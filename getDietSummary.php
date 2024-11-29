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

$is_ajax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest';

if (isset($_POST['patient-firstName'])) {
    // Get all the POST data.
    $firstName = $_POST['patient-firstName'];
    $lastName = $_POST['patient-lastName'];
    $roundDate = $_POST['round-date'];
    $pracName = $_POST['prac-name'];
    $roundSelected = $_POST['round-selection'];
    $dateType = $_POST['date-selection'];
    $orderBy = isset($_POST['order-by']) ? $_POST['order-by'] : '';

    $conn = odbc_connect("Driver={Microsoft Access Driver (*.mdb, *.accdb)};dbq=$db", '', '', SQL_CUR_USE_ODBC);

    // Make a query to get the specified rounds.
    $sql = "SELECT PatientMedicationDietRounds.PatientID, Patients.FirstName & ' ' & Patients.LastName AS PatientName, 
                        PatientMedicationDietRounds.DietID, DietRegimes.Description, PatientMedicationDietRounds.DietStatus, 
                        Rounds.Type, Rounds.TimeStamp, Rounds.PractitionerID, Practitioners.Names AS PractitionerName
                        FROM (DietRegimes INNER JOIN 
                                 (Patients INNER JOIN 
                                   (Rounds INNER JOIN PatientMedicationDietRounds 
                                 ON Rounds.RoundID = PatientMedicationDietRounds.RoundID) 
                               ON Patients.PatientID = PatientMedicationDietRounds.PatientID) 
                            ON DietRegimes.DietID = PatientMedicationDietRounds.DietID) 
                             LEFT JOIN Practitioners ON Practitioners.PractitionerID = Rounds.PractitionerID
                        WHERE Patients.FirstName LIKE '%" . $firstName . "%' AND
                              Patients.LastName LIKE '%" . $lastName . "%'";
    if ($pracName != "") {
        // If there were settings applied to the practitioner's name, then get all practitioners
        //  whose name contains the characters entered by the user.
        $sql = $sql . " AND Practitioners.Names LIKE '%" . $pracName . "%'";
    }
    if ($roundDate != "") {
        // If a round was selected, add a WHERE condition to look for dates starting from selected date.
        if ($dateType == "Only") {
            // If the user only wants one date, then look for just one date.
            $sql = $sql . " AND Rounds.TimeStamp = #" . $roundDate . "#";
        } elseif ($dateType == "Prior") {
            // If the user wants the week prior to that date, then look for date up to 6 days before the date.
            $roundDatePrior = date('m/d/Y', strtotime($roundDate . ' -6 days'));
            $sql = $sql . " AND Rounds.TimeStamp >= #" . $roundDatePrior . "#";
            $sql = $sql . " AND Rounds.TimeStamp <= #" . $roundDate . "#";
        } elseif ($dateType == "Post") {
            // If the user wants the week after that date, then look for date up to 6 days after the date.
            $roundDatePost = date('m/d/Y', strtotime($roundDate . ' +6 days'));
            $sql = $sql . " AND Rounds.TimeStamp >= #" . $roundDate . "#";
            $sql = $sql . " AND Rounds.TimeStamp <= #" . $roundDatePost . "#";
        }
    }
    if ($roundSelected != "") {
        // If the round was selected on something other than "All Rounds", then look for specific round type.
        $sql = $sql . " AND Rounds.Type = '" . $roundSelected . "'";
    }
    $sql = $sql . " " . "ORDER BY PatientMedicationDietRounds.PatientID, Rounds.TimeStamp";
    // Determine how to order the table.
    if ($orderBy == "pracname") {
        $sql = $sql . ", Practitioners.Names";
    }
    // Always end the SQL query with a semicolon..
    $sql = $sql . ";";

    $result = odbc_exec($conn, $sql);

    // Print out in a table.
    if ($result) {
        if ($is_ajax) {
            echo "<table class='report-summary'>";
            echo "<tr>";
            // Define the table headings.
            echo "<th>Patient ID</th>";
            echo "<th>Patient Name</th>";
            echo "<th>Diet Description</th>";
            echo "<th>Diet Status</th>";
            echo "<th>Round</th>";
            echo "<th>Time Stamp</th>";
            echo "<th>Practitioner Name</th></tr>";

            // Loop through the query result and get the desired fields.
            while (odbc_fetch_row($result)) {
                $PatientID = odbc_result($result, "PatientID");
                $PatientName = odbc_result($result, "PatientName");
                $DietDescription = odbc_result($result, "Description");
                $AdminStatus = odbc_result($result, "DietStatus");
                $RoundType = odbc_result($result, "Type");
                $TimeStamp = odbc_result($result, "TimeStamp");
                $PracName = odbc_result($result, "PractitionerName");

                // Print out the field values.
                echo "<tr><td>$PatientID</td>";
                echo "<td>$PatientName</td>";
                //echo "<td>$MedicationID</td>";
                echo "<td>$DietDescription</td>";
                echo "<td>$AdminStatus</td>";
                echo "<td>$RoundType</td>";
                echo "<td>$TimeStamp</td>";
                echo "<td>$PracName</td></tr>";
            }
            echo "</table>";
        } else {
            // Print out the error message if there was a problem with the database connection.
            echo "Error Getting Data: " . odbc_errormsg($conn);
        }
    }
    // If no data was found, prompt user to double-check filter fields.
    if (odbc_num_rows($result) < 1) {
        echo "No data was found for your search query. Please check all fields again.";
    }
    odbc_close($conn);
}