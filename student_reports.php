<?php
session_start();
include('includes/config.php');

// Fetch gender distribution data
$sql_gender = "SELECT Gender, COUNT(*) AS TotalStudents FROM tblstudents GROUP BY Gender";
$query_gender = $dbh->prepare($sql_gender);
$query_gender->execute();
$gender_data = $query_gender->fetchAll(PDO::FETCH_ASSOC);

// Fetch class distribution data (updated to fetch ClassName instead of ClassId)
$sql_class = "SELECT tblclasses.ClassName, COUNT(tblstudents.StudentId) AS TotalStudents
              FROM tblstudents
              JOIN tblclasses ON tblstudents.ClassId = tblclasses.id
              GROUP BY tblclasses.ClassName";
$query_class = $dbh->prepare($sql_class);
$query_class->execute();
$class_data = $query_class->fetchAll(PDO::FETCH_ASSOC);

// Fetch age group distribution
$sql_age = "SELECT 
    CASE
        WHEN TIMESTAMPDIFF(YEAR, DOB, CURDATE()) BETWEEN 5 AND 10 THEN '5-10'
        WHEN TIMESTAMPDIFF(YEAR, DOB, CURDATE()) BETWEEN 11 AND 15 THEN '11-15'
        WHEN TIMESTAMPDIFF(YEAR, DOB, CURDATE()) BETWEEN 16 AND 20 THEN '16-20'
        ELSE '21+'
    END AS AgeRange,
    COUNT(*) AS TotalStudents
FROM tblstudents
GROUP BY AgeRange";
$query_age = $dbh->prepare($sql_age);
$query_age->execute();
$age_data = $query_age->fetchAll(PDO::FETCH_ASSOC);

// Convert data to JSON format for use in JavaScript
$gender_json = json_encode($gender_data);
$class_json = json_encode($class_data);
$age_json = json_encode($age_data);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Advanced Student Reports</title>
    <!-- Add your CSS links -->
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/font-awesome.min.css">
    <link rel="stylesheet" href="css/animate-css/animate.min.css">
    <link rel="stylesheet" href="css/lobipanel/lobipanel.min.css">
    <link rel="stylesheet" href="css/toastr/toastr.min.css">
    <link rel="stylesheet" href="css/icheck/skins/line/blue.css">
    <link rel="stylesheet" href="css/icheck/skins/line/red.css">
    <link rel="stylesheet" href="css/icheck/skins/line/green.css">
    <link rel="stylesheet" href="css/main.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <style>
    canvas {
        width: 100% !important;
        height: 400px !important;
    }
    </style>
</head>

<body class="top-navbar-fixed">
    <div class="main-wrapper">

        <!-- ========== Include Top Bar and Left Sidebar ========== -->
        <?php include('includes/topbar.php'); ?>
        <div class="content-wrapper">
            <div class="content-container">
                <?php include('includes/leftbar.php'); ?>

                <!-- Main Content Starts -->
                <div class="main-page">
                    <div class="container-fluid">
                        <div class="row page-title-div">
                            <div class="col-md-12">
                                <h2 class="title">Advanced Student Reports</h2>
                            </div>
                        </div>

                        <!-- Charts Section -->
                        <section class="section">
                            <div class="container-fluid">
                                <div class="row">
                                    <!-- Gender Distribution Chart -->
                                    <div class="col-lg-6">
                                        <h4>Gender Distribution</h4>
                                        <canvas id="genderChart"></canvas>
                                    </div>

                                    <!-- Class Distribution Chart -->
                                    <div class="col-lg-6">
                                        <h4>Class Distribution</h4>
                                        <canvas id="classChart"></canvas>
                                    </div>
                                </div>

                                <div class="row mt-5">
                                    <!-- Age Group Distribution Chart -->
                                    <div class="col-lg-12">
                                        <h4>Age Group Distribution</h4>
                                        <canvas id="ageChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </section>

                    </div>
                </div>
                <!-- Main Content Ends -->

            </div>
        </div>
    </div>

    <!-- JavaScript for Charts -->
    <script>
    // Convert PHP variables to JavaScript objects
    const genderData = <?php echo $gender_json; ?>;
    const classData = <?php echo $class_json; ?>;
    const ageData = <?php echo $age_json; ?>;

    // Prepare data for Gender Distribution Chart
    const genderLabels = genderData.map(item => item.Gender);
    const genderCounts = genderData.map(item => item.TotalStudents);

    // Prepare data for Class Distribution Chart
    const classLabels = classData.map(item => item.ClassName);
    const classCounts = classData.map(item => item.TotalStudents);

    // Prepare data for Age Group Distribution Chart
    const ageLabels = ageData.map(item => item.AgeRange);
    const ageCounts = ageData.map(item => item.TotalStudents);

    // Gender Distribution Chart
    const genderChartCtx = document.getElementById('genderChart').getContext('2d');
    new Chart(genderChartCtx, {
        type: 'pie',
        data: {
            labels: genderLabels,
            datasets: [{
                label: 'Number of Students',
                data: genderCounts,
                backgroundColor: ['#3498db', '#e74c3c', '#9b59b6']
            }]
        }
    });

    // Class Distribution Chart
    const classChartCtx = document.getElementById('classChart').getContext('2d');
    new Chart(classChartCtx, {
        type: 'bar',
        data: {
            labels: classLabels,
            datasets: [{
                label: 'Number of Students',
                data: classCounts,
                backgroundColor: '#f39c12'
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Age Group Distribution Chart
    const ageChartCtx = document.getElementById('ageChart').getContext('2d');
    new Chart(ageChartCtx, {
        type: 'bar',
        data: {
            labels: ageLabels,
            datasets: [{
                label: 'Number of Students',
                data: ageCounts,
                backgroundColor: '#2ecc71'
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
    </script>
</body>

</html>