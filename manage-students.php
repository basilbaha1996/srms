<?php
session_start();
error_reporting(0);
include('includes/config.php');

// Handling AJAX request for fetching sections based on class selection
if (isset($_POST['action']) && $_POST['action'] == 'fetchSections') {
    $className = $_POST['className'];
    
    // Fetch sections for the selected class
    $sql = "SELECT DISTINCT Section FROM tblclasses WHERE ClassName = :className";
    $query = $dbh->prepare($sql);
    $query->bindParam(':className', $className, PDO::PARAM_STR);
    $query->execute();
    $sections = $query->fetchAll(PDO::FETCH_OBJ);

    if ($query->rowCount() > 0) {
        foreach ($sections as $section) {
            echo '<label><input type="checkbox" class="section-checkbox" value="' . $section->Section . '"> ' . $section->Section . '</label><br>';
        }
    }
    exit();
}

// Handling AJAX request for fetching students based on class and selected sections
if (isset($_POST['action']) && $_POST['action'] == 'fetchStudents') {
    $className = $_POST['className'];
    $sections = $_POST['sections']; // Sections array

    $sectionPlaceholders = implode(',', array_fill(0, count($sections), '?'));

    $sql = "SELECT tblstudents.StudentName, tblstudents.RollId, tblstudents.RegDate, tblstudents.StudentId, tblstudents.Status, tblclasses.ClassName, tblclasses.Section 
            FROM tblstudents 
            JOIN tblclasses ON tblclasses.id = tblstudents.ClassId 
            WHERE tblclasses.ClassName = ?";

    // If sections are selected, filter by section
    if (!empty($sections)) {
        $sql .= " AND tblclasses.Section IN ($sectionPlaceholders)";
    }

    $query = $dbh->prepare($sql);

    // Bind class name and section values dynamically
    $query->bindValue(1, $className);
    foreach ($sections as $index => $section) {
        $query->bindValue($index + 2, $section);
    }

    $query->execute();
    $results = $query->fetchAll(PDO::FETCH_OBJ);

    // Generate the student table
    if ($query->rowCount() > 0) {
        foreach ($results as $result) {
            echo '<tr>';
            echo '<td>' . htmlentities($result->StudentName) . '</td>';
            echo '<td>' . htmlentities($result->RollId) . '</td>';
            echo '<td>' . htmlentities($result->ClassName) . ' (' . htmlentities($result->Section) . ')</td>';
            echo '<td>' . htmlentities($result->RegDate) . '</td>';
            echo '<td>' . ($result->Status == 1 ? 'Active' : 'Blocked') . '</td>';
            echo '<td><a href="edit-student.php?stid=' . htmlentities($result->StudentId) . '"><i class="fa fa-edit" title="Edit Record"></i></a></td>';
            echo '</tr>';
        }
    } else {
        echo '<tr><td colspan="6">No students found.</td></tr>';
    }
    exit();
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Manage Students</title>
    <link rel="stylesheet" href="css/bootstrap.min.css" media="screen">
    <link rel="stylesheet" href="css/font-awesome.min.css" media="screen">
    <link rel="stylesheet" href="css/animate-css/animate.min.css" media="screen">
    <link rel="stylesheet" href="css/lobipanel/lobipanel.min.css" media="screen">
    <link rel="stylesheet" href="css/main.css" media="screen">
    <script src="js/jquery/jquery-2.2.4.min.js"></script>
    <style>
    .errorWrap {
        padding: 10px;
        margin: 0 0 20px 0;
        background: #fff;
        border-left: 4px solid #dd3d36;
    }

    .succWrap {
        padding: 10px;
        margin: 0 0 20px 0;
        background: #fff;
        border-left: 4px solid #5cb85c;
    }
    </style>
</head>

<body class="top-navbar-fixed">
    <div class="main-wrapper">
        <?php include('includes/topbar.php'); ?>
        <div class="content-wrapper">
            <div class="content-container">
                <?php include('includes/leftbar.php'); ?>

                <div class="main-page">
                    <div class="container-fluid">
                        <div class="row page-title-div">
                            <div class="col-md-6">
                                <h2 class="title">Manage Students</h2>
                            </div>
                        </div>
                        <div class="row breadcrumb-div">
                            <div class="col-md-6">
                                <ul class="breadcrumb">
                                    <li><a href="dashboard.php"><i class="fa fa-home"></i> Home</a></li>
                                    <li>Students</li>
                                    <li class="active">Manage Students</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Filter Section -->
                    <section class="section">
                        <div class="container-fluid">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="panel">
                                        <div class="panel-heading">
                                            <h5>Filter by Class and Section</h5>
                                        </div>
                                        <div class="panel-body">
                                            <div class="form-group">
                                                <label for="classFilter">Select Class</label>
                                                <select id="classFilter" class="form-control">
                                                    <option value="">Select Class</option>
                                                    <?php 
                                                    $sql = "SELECT DISTINCT ClassName FROM tblclasses";
                                                    $query = $dbh->prepare($sql);
                                                    $query->execute();
                                                    $results = $query->fetchAll(PDO::FETCH_OBJ);
                                                    if ($query->rowCount() > 0) {
                                                        foreach ($results as $result) {
                                                            echo '<option value="' . $result->ClassName . '">' . $result->ClassName . '</option>';
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                            </div>

                                            <!-- Sections will be shown here -->
                                            <div class="form-group" id="sectionFilter"></div>
                                        </div>
                                    </div>

                                    <div class="panel">
                                        <div class="panel-heading">
                                            <h5>View Students Info</h5>
                                        </div>
                                        <div class="panel-body">
                                            <table id="studentTable" class="display table table-striped table-bordered"
                                                cellspacing="0" width="100%">
                                                <thead>
                                                    <tr>
                                                        <th>Student Name</th>
                                                        <th>Roll Id</th>
                                                        <th>Class (Section)</th>
                                                        <th>Reg Date</th>
                                                        <th>Status</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="studentList">
                                                    <!-- Students will be loaded here -->
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div>

    <!-- AJAX and JS -->
    <script>
    $(document).ready(function() {
        // Fetch sections when class is selected
        $('#classFilter').change(function() {
            const className = $(this).val();
            if (className) {
                $.ajax({
                    url: 'manage-students.php',
                    type: 'POST',
                    data: {
                        action: 'fetchSections',
                        className: className
                    },
                    success: function(response) {
                        $('#sectionFilter').html(response);

                        // Fetch all students for the selected class
                        fetchStudents(className, []);
                    }
                });
            } else {
                $('#sectionFilter').html('');
                $('#studentList').html('');
            }
        });

        // Fetch students when sections are selected
        $(document).on('change', '.section-checkbox', function() {
            const className = $('#classFilter').val();
            const sections = [];
            $('.section-checkbox:checked').each(function() {
                sections.push($(this).val());
            });

            // Fetch students based on class and selected sections
            fetchStudents(className, sections);
        });

        // Function to fetch students based on class and sections
        function fetchStudents(className, sections) {
            $.ajax({
                url: 'manage-students.php',
                type: 'POST',
                data: {
                    action: 'fetchStudents',
                    className: className,
                    sections: sections
                },
                success: function(response) {
                    $('#studentList').html(response);
                }
            });
        }
    });
    </script>
</body>

</html>