<?php
session_start();
include('includes/config.php');

// Fetch class distribution (number of sections per class)
$sql_class_distribution = "SELECT ClassName, COUNT(Section) AS TotalSections 
                           FROM tblclasses 
                           GROUP BY ClassName";
$query_class_distribution = $dbh->prepare($sql_class_distribution);
$query_class_distribution->execute();
$class_distribution_data = $query_class_distribution->fetchAll(PDO::FETCH_ASSOC);

// Fetch classes sorted by their numeric order
$sql_class_numeric = "SELECT ClassName, ClassNameNumeric, Section 
                      FROM tblclasses 
                      ORDER BY ClassNameNumeric";
$query_class_numeric = $dbh->prepare($sql_class_numeric);
$query_class_numeric->execute();
$numeric_class_data = $query_class_numeric->fetchAll(PDO::FETCH_ASSOC);

// Fetch number of students per section for each class
$sql_section_distribution = "SELECT tblclasses.ClassName, tblclasses.Section, COUNT(tblstudents.StudentId) AS TotalStudents
                             FROM tblstudents
                             JOIN tblclasses ON tblstudents.ClassId = tblclasses.id
                             GROUP BY tblclasses.ClassName, tblclasses.Section";
$query_section_distribution = $dbh->prepare($sql_section_distribution);
$query_section_distribution->execute();
$section_distribution_data = $query_section_distribution->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Advanced Class Reports</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/font-awesome.min.css">
    <link rel="stylesheet" href="css/main.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
    canvas {
        width: 100% !important;
        height: 400px !important;
    }
    </style>
</head>

<body class="top-navbar-fixed">
    <div class="main-wrapper">
        <!-- ========== TOP NAVBAR ========== -->
        <?php include('includes/topbar.php'); ?>

        <!-- ========== WRAPPER FOR BOTH SIDEBARS & MAIN CONTENT ========== -->
        <div class="content-wrapper">
            <div class="content-container">
                <?php include('includes/leftbar.php'); ?>
                <!-- /.left-sidebar -->

                <!-- Main Content Starts -->
                <div class="main-page">
                    <div class="container-fluid">
                        <div class="row page-title-div">
                            <div class="col-md-12">
                                <h2 class="title">Advanced Class Reports</h2>
                            </div>
                        </div>

                        <!-- Class Distribution Report -->
                        <section class="section">
                            <h4>Class Distribution (Sections per Class)</h4>
                            <p>This report shows how many sections are available for each class.</p>
                            <canvas id="classDistributionChart"></canvas>
                        </section>

                        <!-- Classes by Numeric Order -->
                        <section class="section">
                            <h4>Classes by Numeric Order</h4>
                            <p>This report displays the classes sorted by their numeric order.</p>
                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>Class Name</th>
                                        <th>Class Numeric</th>
                                        <th>Section</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($numeric_class_data as $class) { ?>
                                    <tr>
                                        <td><?php echo htmlentities($class['ClassName']); ?></td>
                                        <td><?php echo htmlentities($class['ClassNameNumeric']); ?></td>
                                        <td><?php echo htmlentities($class['Section']); ?></td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </section>

                        <!-- Class Section Distribution (Corrected) -->
                        <section class="section">
                            <h4>Class Section Distribution</h4>
                            <p>This report shows how many students are assigned to each section within a specific class.
                            </p>
                            <canvas id="sectionDistributionChart"></canvas>
                        </section>
                    </div>
                </div>
                <!-- Main Content Ends -->
            </div>
        </div>
    </div>

    <!-- JavaScript for Charts -->
    <script>
    // Data for Class Distribution Chart
    const classDistributionData = <?php echo json_encode($class_distribution_data); ?>;
    const classLabels = classDistributionData.map(item => item.ClassName);
    const sectionCounts = classDistributionData.map(item => item.TotalSections);

    const classCtx = document.getElementById('classDistributionChart').getContext('2d');
    new Chart(classCtx, {
        type: 'bar',
        data: {
            labels: classLabels,
            datasets: [{
                label: 'Sections',
                data: sectionCounts,
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1
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

    // Data for Class Section Distribution Chart
    const sectionDistributionData = <?php echo json_encode($section_distribution_data); ?>;

    // Prepare the data to show sections for each class
    const classSectionLabels = sectionDistributionData.map(item => `${item.ClassName} - Section ${item.Section}`);
    const studentCounts = sectionDistributionData.map(item => item.TotalStudents);

    const sectionCtx = document.getElementById('sectionDistributionChart').getContext('2d');
    new Chart(sectionCtx, {
        type: 'bar',
        data: {
            labels: classSectionLabels,
            datasets: [{
                label: 'Number of Students',
                data: studentCounts,
                backgroundColor: 'rgba(255, 159, 64, 0.2)',
                borderColor: 'rgba(255, 159, 64, 1)',
                borderWidth: 1
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