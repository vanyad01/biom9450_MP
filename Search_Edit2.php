<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Record</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            font-size: 24px;
            color: #333;
            margin-bottom: 20px;
        }

        form label {
            display: block;
            margin-bottom: 8px;
            font-size: 16px;
            color: #555;
        }

        form input[type="text"], form button {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
        }

        form input[readonly] {
            background-color: #f0f0f0;
            cursor: not-allowed;
        }

        form button {
            background-color: #007BFF;
            color: white;
            font-size: 16px;
            font-weight: bold;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        form button:hover {
            background-color: #0056b3;
        }

        /* Popup styling */
        .popup {
            text-align: center;
            padding: 20px;
            margin-top: 20px;
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            border-radius: 5px;
            color: #155724;
        }

        .popup a {
            color: #007bff;
            text-decoration: none;
            font-weight: bold;
        }

        .popup a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Edit Record</h2>

        <?php
        // Database connection
        $db = "C:/Users/admin/Downloads/PHPWebProject3/database9450.accdb";
        $conn = odbc_connect("Driver={Microsoft Access Driver (*.mdb, *.accdb)};dbq=$db", '', '', SQL_CUR_USE_ODBC);

        if (!$conn) {
            echo "<p style='color: red;'>Connection Failed: " . odbc_errormsg($conn) . "</p>";
        } else {
            // Make sure the CombinedID is passed in the URL
            if (!isset($_GET['CombinedID']) || empty($_GET['CombinedID'])) {
                echo "<p style='color: red;'>Invalid ID provided. Please return to the previous page.</p>";
                exit;
            }

            $CombinedID = $_GET['CombinedID'];

            // Handle form submission (updating the record)
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $newMedicationID = $_POST['medication_id'];
                $newMedicationStatus = $_POST['medication_status'];
                $newDietID = $_POST['diet_id'];
                $newDietStatus = $_POST['diet_status'];

                // Update query
                $sqlUpdate = "UPDATE PatientMedicationDietRounds 
                              SET MedicationID = '$newMedicationID', 
                                  MedicationStatus = '$newMedicationStatus', 
                                  DietID = '$newDietID', 
                                  DietStatus = '$newDietStatus'
                              WHERE CombinedID = '$CombinedID'";

                $resultUpdate = odbc_exec($conn, $sqlUpdate);

                if ($resultUpdate) {
                    // Show success message with a link to go back to the search page
                    echo "<div class='popup'>
                            <p>Record updated successfully!</p>
                            <p><a href='Search_Edit1.php?patient_id={$_GET['patient_id']}&first_name={$_GET['first_name']}&last_name={$_GET['last_name']}'>Click here to return to the search page</a></p>
                          </div>";
                } else {
                    echo "<p style='color: red;'>Error updating record: " . odbc_errormsg($conn) . "</p>";
                }
            }

            // Fetch record based on CombinedID
            $sql = "SELECT RoundID, PatientID, MedicationID, MedicationStatus, DietID, DietStatus 
                    FROM PatientMedicationDietRounds 
                    WHERE CombinedID = '$CombinedID'";
            $result = odbc_exec($conn, $sql);

            if ($result && $row = odbc_fetch_array($result)) {
                // Fetch the PatientID from the same row
                $patientID = $row['PatientID'];

                // Display the form with the values for editing
                echo "<form method='POST'>";

                // Hidden RoundID and PatientID
                echo "<input type='hidden' name='round_id' value='" . $row['RoundID'] . "'>";
                echo "<input type='hidden' name='patient_id' value='" . $patientID . "'>";

                // Display the fields that can be edited
                echo "<label for='medication_id'>Medication ID:</label>";
                echo "<input type='text' name='medication_id' value='" . $row['MedicationID'] . "' required>";

                echo "<label for='medication_status'>Medication Status:</label>";
                echo "<input type='text' name='medication_status' value='" . $row['MedicationStatus'] . "' required>";

                echo "<label for='diet_id'>Diet ID:</label>";
                echo "<input type='text' name='diet_id' value='" . $row['DietID'] . "' required>";

                echo "<label for='diet_status'>Diet Status:</label>";
                echo "<input type='text' name='diet_status' value='" . $row['DietStatus'] . "' required>";

                echo "<button type='submit'>Update Record</button>";
                echo "</form>";
            } else {
                echo "<p style='color: red;'>No record found for Combined ID $CombinedID.</p>";
            }
        }

        odbc_close($conn);
        ?>
    </div>
</body>
</html>
