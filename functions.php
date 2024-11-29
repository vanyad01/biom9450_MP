<?php
// This function gets the database URI to make queries.
function getDatabase() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start(); // join the session.
    }

    // get the database URI from the session.
    if (isset($_SESSION['db'])) {
        $db = $_SESSION['db']; // set the URI.
    } else {
        $db = ""; 
    }

    return $db;
}

function getPatientProfile() {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Show selected patient details.
        $firstName = $_POST['patientFirstName'];
        $lastName = $_POST['patientLastName'];

        $db = getDatabase();
        $conn = odbc_connect("Driver={Microsoft Access Driver (*.mdb, *.accdb)};dbq=$db", '', '', SQL_CUR_USE_ODBC);

        // Make a query to get the specified rounds.
        $sql = "SELECT Patients.PatientID, 
                               Patients.FirstName & ' ' & Patients.LastName AS PatientName, 
                               Patients.Gender, 
                               FORMAT(Patients.DateOfBirth, 'dd/mm/yyyy') AS DateOfBirth, 
                               Patients.Age, 
                               Patients.RoomNumber, 
                               Patients.AdditionalNotes
                        FROM Patients
                        WHERE Patients.FirstName = '" . $firstName . "' AND
                              Patients.LastName = '" . $lastName . "';";

        $result = odbc_exec($conn, $sql);

        if ($result) {
            // Start patient profile container
            echo "<div class=\"patient-profile\">";
            echo "<h2>Patient Profile</h2>";
            echo "<div class=\"patient-container\">";

            // Loop through the query result and get the desired fields.
            while (odbc_fetch_row($result)) {
                $PatientID = odbc_result($result, "PatientID");
                $PatientURL = "Images/" . $PatientID . ".webp";
                $PatientName = odbc_result($result, "PatientName");
                $PatientGender = odbc_result($result, "Gender");
                $PatientDOB = odbc_result($result, "DateOfBirth");
                $PatientAge = odbc_result($result, "Age");
                $RoomNumber = odbc_result($result, "RoomNumber");

                // Display patient details
                echo "<div class=\"patient-image\">";
                echo "<img src=\"$PatientURL\" alt=\"Patient Image\" class=\"profile-img\">";
                echo "</div>";

                echo "<div class=\"patient-details\">";
                echo "<p><strong>Patient ID:</strong> $PatientID</p>";
                echo "<p><strong>Name:</strong> $PatientName</p>";
                echo "<p><strong>Gender:</strong> $PatientGender</p>";
                echo "<p><strong>Date of Birth:</strong> $PatientDOB</p>";
                echo "<p><strong>Age:</strong> $PatientAge</p>";
                echo "<p><strong>Room Number:</strong> $RoomNumber</p>";
                echo "</div>";
            }
            echo "</div>"; // Close patient-container
            echo "</div>"; // Close patient-profile
        } else {
            // Print out the error message if there was a problem with the database connection.
            echo "<div class=\"error-message\">";
            echo "<p>Error Getting Data: " . odbc_errormsg($conn) . "</p>";
            echo "</div>";
        }
        // If no data was found, prompt user to double-check filter fields.
        if (odbc_num_rows($result) < 1) {
            echo "<div class=\"no-data\">";
            echo "<p>No patient data was found for your query. Please check all fields again.</p>";
            echo "</div>";
        }
        odbc_close($conn);
    }
}

function getMedicationTimetableStr() {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Retrieve posted data
        $firstName = $_POST['patientFirstName'];
        $lastName = $_POST['patientLastName'];
        $medRound = $_POST['med-roundSelection'];
        $medDate = $_POST['med-date'];

        // Date range for the week
        $medDateStart = date('m/d/Y', strtotime($medDate));
        $medDateEnd = date('m/d/Y', strtotime($medDate . ' +6 days'));

        $db = getDatabase();
        $conn = odbc_connect("Driver={Microsoft Access Driver (*.mdb, *.accdb)};dbq=$db", '', '', SQL_CUR_USE_ODBC);

        // Query to retrieve medication data
        $sql = "SELECT Medications.Names AS Medication, 
                       Medications.Dosage, 
                       Medications.Route, 
                       PatientMedicationDietRounds.MedicationStatus, 
                       Medications.StockStatus, 
                       Rounds.Type, 
                       Rounds.TimeStamp, 
                       Rounds.PractitionerID, 
                       Practitioners.Names AS PractitionerName
                FROM (Medications 
                      INNER JOIN (Patients 
                                  INNER JOIN (Rounds 
                                              INNER JOIN PatientMedicationDietRounds 
                                              ON Rounds.RoundID = PatientMedicationDietRounds.RoundID) 
                                  ON Patients.PatientID = PatientMedicationDietRounds.PatientID) 
                      ON Medications.MedicationID = PatientMedicationDietRounds.MedicationID) 
                      LEFT JOIN Practitioners ON Practitioners.PractitionerID = Rounds.PractitionerID
                WHERE Rounds.Type = '$medRound' AND
                      Rounds.TimeStamp >= #$medDateStart# AND
                      Rounds.TimeStamp <= #$medDateEnd# AND
                      Patients.FirstName = '$firstName' AND
                      Patients.LastName = '$lastName'
                ORDER BY Medications.Names, Rounds.TimeStamp;";

        $result = odbc_exec($conn, $sql);

        // Initialize array to organize data
        $medicationData = [];
        $dateRange = [];

        // Generate 7-day date range
        $startDate = new DateTime($medDate);
        for ($i = 0; $i < 7; $i++) {
            $dateRange[$startDate->format('Y-m-d')] = $startDate->format('D, d M');
            $startDate->modify('+1 day');
        }

        // Populate medication data
        if ($result) {
            while ($row = odbc_fetch_array($result)) {
                $dispenseDate = (new DateTime($row['TimeStamp']))->format('Y-m-d');
                $medicationName = $row['Medication'];
                $dosageRoute = $row['Dosage'] . ", " . $row['Route'];
                $roundType = $row['Type'];
                $pracName = $row['PractitionerName'];

                // Initialize medication entry if not already in the array
                if (!isset($medicationData[$medicationName])) {
                    $medicationData[$medicationName] = [
                        'dosageRoute' => $dosageRoute,
                        'roundType' => $roundType,
                        'statuses' => array_fill_keys(array_keys($dateRange), null)
                    ];
                }

                // Extract dispenser initials
                $pracInitials = '';
                if (!empty($pracName)) {
                    $names = explode(' ', $pracName);
                    foreach ($names as $name) {
                        $pracInitials .= strtoupper($name[0]); // Get the first letter of each part of the name
                    }
                }

                // Assign status and initials to the correct date
                $medicationData[$medicationName]['statuses'][$dispenseDate] = [
                    'status' => $row['MedicationStatus'],
                    'initials' => $pracInitials
                ];
            }
        } else {
            echo "Error Getting Data: " . odbc_errormsg($conn);
            return;
        }

        // Generate timetable HTML
        $timetableStr = "<tr>";
        $timetableStr .= "<th>Medication</th>";
        $timetableStr .= "<th>Dosage (mg) / Route</th>";
        $timetableStr .= "<th>Round</th>";

        // Add date headers
        foreach ($dateRange as $date => $formattedDate) {
            $timetableStr .= "<th>$formattedDate</th>";
        }
        $timetableStr .= "</tr>";

        // Add rows for each medication
        foreach ($medicationData as $medicationName => $data) {
            $timetableStr .= "<tr>";
            $timetableStr .= "<td>$medicationName</td>";
            $timetableStr .= "<td>{$data['dosageRoute']}</td>";
            $timetableStr .= "<td>{$data['roundType']}</td>";

            // Add status cells for each date
            foreach ($data['statuses'] as $date => $statusData) {
                $statusIcon = "&#x25EF;"; // Default: planned
                if (!empty($statusData['status'])) {
                    if ($statusData['status'] == 'Given') {
                        $statusIcon = "&#x1F7E2;"; // green
                    } elseif ($statusData['status'] == 'Refused') {
                        $statusIcon = "&#x1F534;"; // red
                    } elseif ($statusData['status'] == 'Missed') {
                        $statusIcon = "&#x1F7E1;"; // yellow
                    } elseif ($statusData['status'] == 'Ceased') {
                        $statusIcon = "&#x1F7E3;"; // purple
                    } elseif ($statusData['status'] == 'No stock') {
                        $statusIcon = "&#x1F7E4;"; // brown
                    }
                }

                // Append initials if present
                $dispStatus = $statusIcon;
                if (!empty($statusData['initials'])) {
                    $dispStatus .= " " . $statusData['initials'];
                }

                $timetableStr .= "<td>$dispStatus</td>";
            }

            $timetableStr .= "</tr>";
        }

        odbc_close($conn);
        return $timetableStr;
    }
}

function getDietTimetableStr() {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Retrieve posted data
        $firstName = $_POST['patientFirstName'];
        $lastName = $_POST['patientLastName'];
        $medRound = $_POST['med-roundSelection'];
        $medDate = $_POST['med-date'];

        // Define the date range (7 days)
        $medDateStart = date('m/d/Y', strtotime($medDate));
        $medDateEnd = date('m/d/Y', strtotime($medDate . ' +6 days'));

        $db = getDatabase();
        $conn = odbc_connect("Driver={Microsoft Access Driver (*.mdb, *.accdb)};dbq=$db", '', '', SQL_CUR_USE_ODBC);

        // Query to retrieve diet data
        $sql = "SELECT Rounds.RoundID, 
                       PatientMedicationDietRounds.PatientID, 
                       Patients.FirstName, Patients.LastName, 
                       PatientMedicationDietRounds.DietID, 
                       DietRegimes.Description, 
                       DietRegimes.Rules, 
                       PatientMedicationDietRounds.DietStatus, 
                       Rounds.Type, Rounds.TimeStamp, 
                       Rounds.PractitionerID, 
                       Practitioners.Names AS PractitionerName
                FROM (DietRegimes 
                      INNER JOIN (Patients 
                                  INNER JOIN (Rounds 
                                              INNER JOIN PatientMedicationDietRounds 
                                              ON Rounds.RoundID = PatientMedicationDietRounds.RoundID) 
                                  ON Patients.PatientID = PatientMedicationDietRounds.PatientID) 
                      ON DietRegimes.DietID = PatientMedicationDietRounds.DietID) 
                      LEFT JOIN Practitioners ON Rounds.PractitionerID = Practitioners.PractitionerID
                WHERE Rounds.Type = '$medRound' AND
                      Rounds.TimeStamp >= #$medDateStart# AND
                      Rounds.TimeStamp <= #$medDateEnd# AND
                      Patients.FirstName = '$firstName' AND
                      Patients.LastName = '$lastName'
                ORDER BY DietRegimes.Description, Rounds.TimeStamp;";

        $result = odbc_exec($conn, $sql);

        // Initialize array to organize data
        $dietData = [];
        $dateRange = [];

        // Generate 7-day date range
        $startDate = new DateTime($medDate);
        for ($i = 0; $i < 7; $i++) {
            $dateRange[$startDate->format('Y-m-d')] = $startDate->format('D, d M');
            $startDate->modify('+1 day');
        }

        // Populate diet data
        if ($result) {
            while ($row = odbc_fetch_array($result)) {
                $dispenseDate = (new DateTime($row['TimeStamp']))->format('Y-m-d');
                $meal = $row['Description'];
                $rules = $row['Rules'];
                $roundType = $row['Type'];
                $pracName = $row['PractitionerName'];

                // Initialize meal entry if not already in the array
                if (!isset($dietData[$meal])) {
                    $dietData[$meal] = [
                        'rules' => $rules,
                        'roundType' => $roundType,
                        'statuses' => array_fill_keys(array_keys($dateRange), null)
                    ];
                }

                // Extract dispenser initials
                $pracInitials = '';
                if (!empty($pracName)) {
                    $names = explode(' ', $pracName);
                    foreach ($names as $name) {
                        $pracInitials .= strtoupper($name[0]); // Get the first letter of each part of the name
                    }
                }

                // Assign status and initials to the correct date
                $dietData[$meal]['statuses'][$dispenseDate] = [
                    'status' => $row['DietStatus'],
                    'initials' => $pracInitials
                ];
            }
        } else {
            echo "Error Getting Data: " . odbc_errormsg($conn);
            return;
        }

        // Generate timetable HTML
        $timetableStr = "<tr>";
        $timetableStr .= "<th>Meal</th>";
        $timetableStr .= "<th>Rules</th>";
        $timetableStr .= "<th>Round</th>";

        // Add date headers
        foreach ($dateRange as $date => $formattedDate) {
            $timetableStr .= "<th>$formattedDate</th>";
        }
        $timetableStr .= "</tr>";

        // Add rows for each meal
        foreach ($dietData as $meal => $data) {
            $timetableStr .= "<tr>";
            $timetableStr .= "<td>$meal</td>";
            $timetableStr .= "<td>{$data['rules']}</td>";
            $timetableStr .= "<td>{$data['roundType']}</td>";

            // Add status cells for each date
            foreach ($data['statuses'] as $date => $statusData) {
                $statusIcon = "&#x25EF;"; // Default: planned
                if (!empty($statusData['status'])) {
                    if ($statusData['status'] == 'Given') {
                        $statusIcon = "&#x1F7E2;"; // green
                    } elseif ($statusData['status'] == 'Missed') {
                        $statusIcon = "&#x1F7E1;"; // yellow
                    } elseif ($statusData['status'] == 'Fasting') {
                        $statusIcon = "&#x1F7E3;"; // purple
                    }
                }

                // Append initials if present
                $dispStatus = $statusIcon;
                if (!empty($statusData['initials'])) {
                    $dispStatus .= " " . $statusData['initials'];
                }

                $timetableStr .= "<td>$dispStatus</td>";
            }

            $timetableStr .= "</tr>";
        }

        odbc_close($conn);
        return $timetableStr;
    }
}

