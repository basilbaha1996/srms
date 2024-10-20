<?php
session_start();
include('includes/config.php');

// Fetch subject popularity data
$sql_popularity = "SELECT tblsubjects.SubjectName, COUNT(DISTINCT tblsubjectcombination.ClassId) AS total_classes 
                    FROM tblsubjectcombination 
                    JOIN tblsubjects ON tblsubjects.id = tblsubjectcombination.SubjectId 
                    GROUP BY tblsubjects.SubjectName";
$query_popularity = $dbh->prepare($sql_popularity);
$query_popularity->execute();
$popularity_data = $query_popularity->fetchAll(PDO::FETCH_ASSOC);

// Fetch class-wise subject distribution data
$sql_distribution = "SELECT tblclasses.ClassName, tblsubjects.SubjectName 
                    FROM tblsubjectcombination 
                    JOIN tblsubjects ON tblsubjects.id = tblsubjectcombination.SubjectId 
                    JOIN tblclasses ON tblclasses.id = tblsubjectcombination.ClassId";
$query_distribution = $dbh->prepare($sql_distribution);
$query_distribution->execute();
$distribution_data = $query_distribution->fetchAll(PDO::FETCH_ASSOC);

$classSubjectData = [];
foreach ($distribution_data as $row) {
    $classSubjectData[$row['ClassName']][] = $row['SubjectName'];
}

// Fetch unused/unassigned subjects
$sql_unassigned = "SELECT tblsubjects.SubjectName 
                    FROM tblsubjects 
                    LEFT JOIN tblsubjectcombination ON tblsubjectcombination.SubjectId = tblsubjects.id 
                    WHERE tblsubjectcombination.SubjectId IS NULL";
$query_unassigned = $dbh->prepare($sql_unassigned);
$query_unassigned->execute();
$unassigned_data = $query_unassigned->fetchAll(PDO::FETCH_ASSOC);

// Class Performance per Subject
$sql_performance = "SELECT tblsubjects.SubjectName, tblclasses.ClassName, AVG(tblresult.marks) AS average_marks 
                    FROM tblresult 
                    JOIN tblsubjects ON tblsubjects.id = tblresult.SubjectId 
                    JOIN tblstudents ON tblstudents.StudentId = tblresult.StudentId 
                    JOIN tblclasses ON tblstudents.ClassId = tblclasses.id 
                    GROUP BY tblsubjects.SubjectName, tblclasses.ClassName";
$query_performance = $dbh->prepare($sql_performance);
$query_performance->execute();
$performance_data = $query_performance->fetchAll(PDO::FETCH_ASSOC);

$performanceByClass = [];
foreach ($performance_data as $row) {
    $performanceByClass[$row['SubjectName']][] = [
        'class' => $row['ClassName'],
        'average_marks' => round($row['average_marks'], 2)
    ];
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Advanced Reports</title>
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
                                <h2 class="title">Advanced Reports</h2>
                            </div>
                        </div>

                        <!-- Subject Popularity Report -->
                        <section class="section">
                            <h4>Subject Popularity</h4>
                            <p>This report shows how popular each subject is based on the number of classes offering it.
                            </p>
                            <canvas id="subjectPopularityChart"></canvas>
                        </section>

                        <!-- Class-wise Subject Distribution -->
                        <section class="section">
                            <h4>Class-wise Subject Distribution</h4>
                            <p>Displays a breakdown of subjects offered in each class.</p>
                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>Class Name</th>
                                        <th>Subjects</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($classSubjectData as $className => $subjects) { ?>
                                    <tr>
                                        <td><?php echo htmlentities($className); ?></td>
                                        <td><?php echo htmlentities(implode(', ', $subjects)); ?></td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </section>

                        <!-- Unused/Unassigned Subjects -->
                        <section class="section">
                            <h4>Unused/Unassigned Subjects</h4>
                            <p>Displays subjects that are not currently assigned to any class.</p>
                            <?php if (!empty($unassigned_data)) { ?>
                            <ul>
                                <?php foreach ($unassigned_data as $subject) { ?>
                                <li><?php echo htmlentities($subject['SubjectName']); ?></li>
                                <?php } ?>
                            </ul>
                            <?php } else { ?>
                            <p>All subjects are currently assigned to classes.</p>
                            <?php } ?>
                        </section>

                        <!-- Class Performance per Subject -->
                        <section class="section">
                            <h4>Class Performance per Subject</h4>
                            <p>Shows the average performance of classes for each subject.</p>
                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>Subject</th>
                                        <th>Class</th>
                                        <th>Average Marks</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($performanceByClass as $subjectName => $performance) { ?>
                                    <?php foreach ($performance as $data) { ?>
                                    <tr>
                                        <td><?php echo htmlentities($subjectName); ?></td>
                                        <td><?php echo htmlentities($data['class']); ?></td>
                                        <td><?php echo htmlentities($data['average_marks']); ?></td>
                                    </tr>
                                    <?php } ?>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </section>
                    </div>
                </div>
                <!-- Main Content Ends -->

            </div>
        </div>
    </div>

    <!-- Script for Subject Popularity Chart -->
    <script>
    const subjectPopularityData = <?php echo json_encode($popularity_data); ?>;
    const labels = subjectPopularityData.map(item => item.SubjectName);
    const classCounts = subjectPopularityData.map(item => item.total_classes);

    const ctx = document.getElementById('subjectPopularityChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Classes',
                data: classCounts,
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
    </script>
</body>

</html>