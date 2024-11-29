<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search and Edit Records</title>
    <a href="Add_Patient.php" class="add-new-patient-btn">Add or Delete New Patient Information</a>

    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 1200px;
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

        .records-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .records-table th, .records-table td {
            padding: 10px;
            text-align: left;
            border: 1px solid #ddd;
        }

        .records-table th {
            background-color: #007BFF;
            color: white;
            font-weight: bold;
        }

        .edit-button {
            padding: 8px 12px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }

        .edit-button:hover {
            background-color: #218838;
        }

        .search-form {
            margin-bottom: 20px;
        }

        .search-form label {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 8px;
            display: block;
            color: #333;
        }

        .search-form input {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
        }

        .search-form button {
            width: 100%;
            padding: 10px;
            background-color: #007BFF;
            color: #fff;
            font-size: 16px;
            font-weight: bold;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .search-form button:hover {
            background-color: #0056b3;
        }

        .add-new-row-button {
            background-color: #f8f9fa;
            padding: 20px;
            margin-top: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .add-new-row-button button {
            background-color: #28a745;
            color: white;
            padding: 10px 20px;
            font-size: 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .add-new-row-button button:hover {
            background-color: #218838;
        }

    </style>
</head>
<body>
    <div class="container">
        <h2>Patient Medication/Diet Records</h2>

        <?php
        // Database connection
        $db = "C:/Users/admin/Downloads/PHPWebProject3/database9450.accdb";
        $conn = odbc_connect("Driver={Microsoft Access Driver (*.mdb, *.accdb)};dbq=$db", '', '', SQL_CUR_USE_ODBC);

        if (!$conn) {
            echo "<p style='color: red;'>Connection Failed: " . odbc_errormsg($conn) . "</p>";
        } else {
            $searchSQL = "";

            // If the form is submitted with search criteria
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $inputID = trim($_POST['patient_id']);
                $firstName = trim($_POST['first_name']);
                $lastName = trim($_POST['last_name']);
                $roundID = trim($_POST['round_id']);

                $conditions = [];
                if (!empty($inputID)) {
                    $conditions[] = "Patients.PatientID = '$inputID'";
                }
                if (!empty($firstName)) {
                    $conditions[] = "Patients.FirstName LIKE '%$firstName%'";
                }
                if (!empty($lastName)) {
                    $conditions[] = "Patients.LastName LIKE '%$lastName%'";
                }
                if (!empty($roundID)) {
                    $conditions[] = "PatientMedicationDietRounds.RoundID = '$roundID'";
                }

                // Combine conditions
                if (!empty($conditions)) {
                    $searchSQL = " WHERE " . implode(" AND ", $conditions);
                }
            }

            // Fetch data based on search criteria, order by RoundID (numerically by 3 digits after RD)
            $sql = "SELECT 
                        Patients.PatientID, 
                        Patients.FirstName, 
                        Patients.LastName, 
                        Patients.DateOfBirth, 
                        Patients.Gender, 
                        Patients.Age, 
                        Patients.RoomNumber,
                        PatientMedicationDietRounds.RoundID, 
                        PatientMedicationDietRounds.CombinedID, 
                        PatientMedicationDietRounds.MedicationID, 
                        PatientMedicationDietRounds.MedicationStatus, 
                        PatientMedicationDietRounds.DietID, 
                        PatientMedicationDietRounds.DietStatus
                    FROM 
                        Patients
                    LEFT JOIN 
                        PatientMedicationDietRounds
                    ON 
                        Patients.PatientID = PatientMedicationDietRounds.PatientID
                    $searchSQL
                    ORDER BY 
                        Val(Mid(PatientMedicationDietRounds.RoundID, 3))";  // Orders by the 3 digits after 'RD'
        
            $result = odbc_exec($conn, $sql);

            if (!$result) {
                echo "<p style='color: red;'>Error fetching records: " . odbc_errormsg($conn) . "</p>";
            } else {
                // Display search form
                echo "<form method='POST' class='search-form'>";
                echo "<label for='patient_id'>Patient ID (optional):</label>";
                echo "<input type='text' id='patient_id' name='patient_id' placeholder='Enter Patient ID'>";

                echo "<label for='first_name'>First Name (optional):</label>";
                echo "<input type='text' id='first_name' name='first_name' placeholder='Enter First Name'>";

                echo "<label for='last_name'>Last Name (optional):</label>";
                echo "<input type='text' id='last_name' name='last_name' placeholder='Enter Last Name'>";

                // Round ID field with input validation format
                echo "<label for='round_id'>Round ID (optional):</label>";
                echo "<input type='text' id='round_id' name='round_id' placeholder='Please enter in the format RD001 to RD999' pattern='^RD[0-9]{3}$' title='Round ID should be in the format RD001 to RD999'>";

                echo "<button type='submit'>Search</button>";
                echo "</form>";

                // Display search results in a table
                echo "<table class='records-table'>";
                echo "<tr>
                        <th>Patient ID</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Round ID</th>
                        <th>Medication ID</th>
                        <th>Medication Status</th>
                        <th>Diet ID</th>
                        <th>Diet Status</th>
                        <th>Actions</th>
                      </tr>";

                while ($row = odbc_fetch_array($result)) {
                    // If MedicationStatus or DietStatus is empty, set them to "Not scheduled yet"
                    $medicationStatus = empty($row['MedicationStatus']) ? "Not scheduled yet" : htmlspecialchars($row['MedicationStatus']);
                    $dietStatus = empty($row['DietStatus']) ? "Not scheduled yet" : htmlspecialchars($row['DietStatus']);

                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['PatientID']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['FirstName']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['LastName']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['RoundID']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['MedicationID']) . "</td>";
                    echo "<td>" . $medicationStatus . "</td>";
                    echo "<td>" . htmlspecialchars($row['DietID']) . "</td>";
                    echo "<td>" . $dietStatus . "</td>";
                    echo "<td>
                            <form method='GET' action='Search_Edit2.php'>
                                <input type='hidden' name='CombinedID' value='" . htmlspecialchars($row['CombinedID']) . "'>
                                <button class='edit-button' type='submit'>Edit</button>
                            </form>
                          </td>";
                    echo "</tr>";
                }

                echo "</table>";
            }
        }

        odbc_close($conn);
        ?>

        <!-- Add New Row Button -->
        <div class="add-new-row-button">
            <a href="addnewrowtest.php"><button type="button">Add New Row</button></a>
        </div>
    </div>
</body>
</html>
