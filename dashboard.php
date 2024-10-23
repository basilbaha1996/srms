<?php
session_start();
error_reporting(0);

// Include the config file to establish the database connection
include('includes/config.php');

// Proceed with database queries if the connection is successful
if(strlen($_SESSION['alogin']) == "") {
    header("Location: index.php");
} else {
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Student Result Management System | Dashboard</title>
    <link rel="stylesheet" href="css/bootstrap.min.css" media="screen">
    <link rel="stylesheet" href="css/font-awesome.min.css" media="screen">
    <link rel="stylesheet" href="css/animate-css/animate.min.css" media="screen">
    <link rel="stylesheet" href="css/lobipanel/lobipanel.min.css" media="screen">
    <link rel="stylesheet" href="css/toastr/toastr.min.css" media="screen">
    <link rel="stylesheet" href="css/icheck/skins/line/blue.css">
    <link rel="stylesheet" href="css/icheck/skins/line/red.css">
    <link rel="stylesheet" href="css/icheck/skins/line/green.css">
    <link rel="stylesheet" href="css/main.css" media="screen">
    <script src="js/modernizr/modernizr.min.js"></script>
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
                            <div class="col-sm-6">
                                <h2 class="title">Dashboard</h2>
                            </div>
                        </div>
                    </div>

                    <section class="section">
                        <div class="container-fluid">
                            <div class="row">
                                <!-- Total Students -->
                                <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                                    <a class="dashboard-stat bg-primary" href="manage-students.php">
                                        <?php
                                        $sql1 = "SELECT StudentId FROM tblstudents";
                                        $query1 = $dbh->prepare($sql1);
                                        $query1->execute();
                                        $results1 = $query1->fetchAll(PDO::FETCH_OBJ);
                                        $totalstudents = $query1->rowCount();
                                        ?>
                                        <span class="number counter"><?php echo htmlentities($totalstudents); ?></span>
                                        <span class="name">Total students</span>
                                        <span class="bg-icon"><i class="fa fa-users"></i></span>
                                    </a>
                                    <div class="text-center" style="margin-top: 10px;">
                                        <a href="student_reports.php" class="btn btn-primary btn-sm">Advanced
                                            Reports</a>
                                    </div>
                                </div>

                                <!-- Total Subjects -->
                                <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                                    <a class="dashboard-stat bg-danger" href="manage-subjects.php">
                                        <?php
                                        $sql = "SELECT id FROM tblsubjects";
                                        $query = $dbh->prepare($sql);
                                        $query->execute();
                                        $results = $query->fetchAll(PDO::FETCH_OBJ);
                                        $totalsubjects = $query->rowCount();
                                        ?>
                                        <span class="number counter"><?php echo htmlentities($totalsubjects); ?></span>
                                        <span class="name">Total Subjects</span>
                                        <span class="bg-icon"><i class="fa fa-ticket"></i></span>
                                    </a>
                                    <div class="text-center" style="margin-top: 10px;">
                                        <a href="subject-reports.php" class="btn btn-danger btn-sm">Advanced
                                            Reports</a>
                                    </div>
                                </div>

                                <!-- Total Classes -->
                                <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                                    <a class="dashboard-stat bg-warning" href="manage-classes.php">
                                        <?php
                                        $sql2 = "SELECT id FROM tblclasses";
                                        $query2 = $dbh->prepare($sql2);
                                        $query2->execute();
                                        $results2 = $query2->fetchAll(PDO::FETCH_OBJ);
                                        $totalclasses = $query2->rowCount();
                                        ?>
                                        <span class="number counter"><?php echo htmlentities($totalclasses); ?></span>
                                        <span class="name">Total classes</span>
                                        <span class="bg-icon"><i class="fa fa-bank"></i></span>
                                    </a>
                                    <div class="text-center" style="margin-top: 10px;">
                                        <a href="class-reports.php" class="btn btn-warning btn-sm">Advanced
                                            Reports</a>
                                    </div>
                                </div>

                                <!-- Total Results -->
                                <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                                    <a class="dashboard-stat bg-success" href="manage-results.php">
                                        <?php
                                        $sql3 = "SELECT DISTINCT StudentId FROM tblresult";
                                        $query3 = $dbh->prepare($sql3);
                                        $query3->execute();
                                        $results3 = $query3->fetchAll(PDO::FETCH_OBJ);
                                        $totalresults = $query3->rowCount();
                                        ?>
                                        <span class="number counter"><?php echo htmlentities($totalresults); ?></span>
                                        <span class="name">Results</span>
                                        <span class="bg-icon"><i class="fa fa-file-text"></i></span>
                                    </a>
                                    <div class="text-center" style="margin-top: 10px;">
                                        <a href="result-reports.php" class="btn btn-success btn-sm">Advanced
                                            Reports</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Logo Centered -->
                    <div style="
                        position: fixed;
                        top: 0;
                        left: 0;
                        width: 100vw;
                        height: 100vh;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        z-index: -1;">
                        <img src="logo.jpeg" alt="Logo" style="width: 400px; height: auto; opacity: 0.1;">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="js/jquery/jquery-2.2.4.min.js"></script>
    <script src="js/jquery-ui/jquery-ui.min.js"></script>
    <script src="js/bootstrap/bootstrap.min.js"></script>
    <script src="js/pace/pace.min.js"></script>
    <script src="js/lobipanel/lobipanel.min.js"></script>
    <script src="js/iscroll/iscroll.js"></script>
    <script src="js/prism/prism.js"></script>
    <script src="js/waypoint/waypoints.min.js"></script>
    <script src="js/counterUp/jquery.counterup.min.js"></script>
    <script src="js/amcharts/amcharts.js"></script>
    <script src="js/amcharts/serial.js"></script>
    <script src="js/amcharts/plugins/export/export.min.js"></script>
    <link rel="stylesheet" href="js/amcharts/plugins/export/export.css" type="text/css" media="all" />
    <script src="js/amcharts/themes/light.js"></script>
    <script src="js/toastr/toastr.min.js"></script>
    <script src="js/icheck/icheck.min.js"></script>
    <script src="js/main.js"></script>
    <script src="js/production-chart.js"></script>
    <script src="js/traffic-chart.js"></script>
    <script src="js/task-list.js"></script>
</body>

<div class="foot">
    <footer></footer>
</div>

</html>

<?php } ?>