<?php
session_start();
error_reporting(0);
include('includes/config.php');

// Include PHPSpreadsheet classes
require 'vendor/autoload.php';  // Ensure PHPSpreadsheet is autoloaded

use PhpOffice\PhpSpreadsheet\IOFactory;

if (isset($_POST['import'])) {
    $file_mimes = array('application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    
    // Validate if file is uploaded
    if (isset($_FILES['import_file']['name']) && in_array($_FILES['import_file']['type'], $file_mimes)) {

        $file = $_FILES['import_file']['tmp_name'];

        // Load Excel file
        $spreadsheet = IOFactory::load($file);
        $sheetData = $spreadsheet->getActiveSheet()->toArray();

        // Start processing row by row
        $query = $dbh->prepare("INSERT INTO tblstudents(StudentName, RollId, StudentEmail, Gender, ClassId, DOB, Status) 
                                VALUES (:studentname, :roolid, :studentemail, :gender, :classid, :dob, :status)");
        $status = 1;

        for ($row = 1; $row < count($sheetData); $row++) {  // Skipping header row
            $studentname = $sheetData[$row][0];
            $roolid = $sheetData[$row][1];
            $studentemail = $sheetData[$row][2];
            $gender = $sheetData[$row][3];
            $classid = $sheetData[$row][4];
            $dob = $sheetData[$row][5];  // Assuming DOB is stored in a valid format (e.g., YYYY-MM-DD)

            $query->bindParam(':studentname', $studentname);
            $query->bindParam(':roolid', $roolid);
            $query->bindParam(':studentemail', $studentemail);
            $query->bindParam(':gender', $gender);
            $query->bindParam(':classid', $classid);
            $query->bindParam(':dob', $dob);
            $query->bindParam(':status', $status);
            $query->execute();
        }

        // Redirect after success with success message
        $_SESSION['msg'] = "Well done! Student Excel file added successfully.";
        header('Location: add-students.php');
        exit();
    } else {
        // Invalid file type error message
        $_SESSION['error'] = "Invalid file format. Please upload a valid Excel file.";
        header('Location: add-students.php');
        exit();
    }
}
?>