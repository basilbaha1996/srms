<?php
session_start();
error_reporting(0);
include('includes/config.php');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Result Management System</title>
    <link rel="stylesheet" href="css/bootstrap.min.css" media="screen">
    <link rel="stylesheet" href="css/font-awesome.min.css" media="screen">
    <link rel="stylesheet" href="css/animate-css/animate.min.css" media="screen">
    <link rel="stylesheet" href="css/lobipanel/lobipanel.min.css" media="screen">
    <link rel="stylesheet" href="css/prism/prism.css" media="screen">
    <link rel="stylesheet" href="css/main.css" media="screen">
    <link rel="icon" type="image/x-icon" href="favico.png">

    <script src="js/modernizr/modernizr.min.js"></script>

    <!-- Custom CSS for the header -->
    <style>
    .header {
        background-color: white;
        padding: 20px 0;
        text-align: center;
        margin-bottom: 40px;
    }

    .header img {
        width: 80px;
        margin-right: 20px;
    }

    .header h1 {
        font-size: 40px;
        margin: 0;
        display: inline-block;
        vertical-align: middle;
    }

    .subheader {
        font-size: 24px;
        margin-top: 10px;
    }
    </style>
</head>

<body>
    <div class="main-wrapper">
        <!-- ========== HEADER WITH LARGE LOGO AND TITLE ========== -->
        <div class="header">
            <img src="logo.jpeg" alt="School Logo">
            <h1>SYSTEMS SCHOOL</h1>
            <img src="logo.jpeg" alt="School Logo">
        </div>

        <div class="content-wrapper">
            <div class="content-container">
                <div class="main-page">
                    <div class="container-fluid">
                        <div class="row page-title-div">
                            <div class="col-md-12">
                                <h2 class="title" align="center">Result Management System</h2>
                            </div>
                        </div>
                    </div>

                    <section class="section">
                        <div class="container-fluid">
                            <div class="row">
                                <div class="col-md-8 col-md-offset-2">
                                    <div class="panel">
                                        <div class="panel-heading">
                                            <div class="panel-title">
                                                <?php
                                                // Fetch student and class info
                                                $rollid = $_POST['rollid'];
                                                $classid = $_POST['class'];
                                                $exam_category = $_POST['exam_category'];  // Get the exam category
                                                
                                                $_SESSION['rollid'] = $rollid;
                                                $_SESSION['classid'] = $classid;

                                                $query = "SELECT tblstudents.StudentName, tblstudents.RollId, tblstudents.RegDate, tblstudents.StudentId, tblstudents.Status, tblclasses.ClassName, tblclasses.Section 
                                                          FROM tblstudents 
                                                          JOIN tblclasses ON tblclasses.id = tblstudents.ClassId 
                                                          WHERE tblstudents.RollId = :rollid AND tblstudents.ClassId = :classid";
                                                $stmt = $dbh->prepare($query);
                                                $stmt->bindParam(':rollid', $rollid, PDO::PARAM_STR);
                                                $stmt->bindParam(':classid', $classid, PDO::PARAM_STR);
                                                $stmt->execute();
                                                $resultss = $stmt->fetchAll(PDO::FETCH_OBJ);

                                                if ($stmt->rowCount() > 0) {
                                                    foreach ($resultss as $row) { ?>
                                                <p><b>Student Name :</b> <?php echo htmlentities($row->StudentName);?>
                                                </p>
                                                <p><b>Student Roll Id :</b> <?php echo htmlentities($row->RollId);?></p>
                                                <p><b>Student Class:</b>
                                                    <?php echo htmlentities($row->ClassName);?>(<?php echo htmlentities($row->Section);?>)
                                                </p>
                                                <?php } 
                                                    // Fetch Exam Category and Date
                                                    $examQuery = "SELECT exam_category, exam_date FROM tblexamcategories WHERE id = :exam_category_id";
                                                    $examStmt = $dbh->prepare($examQuery);
                                                    $examStmt->bindParam(':exam_category_id', $exam_category, PDO::PARAM_STR);
                                                    $examStmt->execute();
                                                    $examDetails = $examStmt->fetch(PDO::FETCH_OBJ);
                                                    if ($examDetails) {
                                                ?>
                                                <p><b>Exam Category:</b>
                                                    <?php echo htmlentities($examDetails->exam_category); ?></p>
                                                <p><b>Exam Date:</b>
                                                    <?php echo htmlentities($examDetails->exam_date); ?></p>
                                                <?php } } ?>
                                            </div>
                                        </div>
                                        <div class="panel-body p-20">
                                            <table class="table table-hover table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Subject</th>
                                                        <th>Marks</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    // Fetch result based on Roll Number, Class, and Exam Category
                                                    $query = "SELECT t.StudentName, t.RollId, t.ClassId, t.marks, t.SubjectId, tblsubjects.SubjectName 
                                                              FROM (SELECT sts.StudentName, sts.RollId, sts.ClassId, tr.marks, tr.SubjectId 
                                                                    FROM tblstudents AS sts 
                                                                    JOIN tblresult AS tr ON tr.StudentId = sts.StudentId 
                                                                    WHERE tr.exam_category = :exam_category) AS t 
                                                              JOIN tblsubjects ON tblsubjects.id = t.SubjectId 
                                                              WHERE (t.RollId = :rollid AND t.ClassId = :classid)";
                                                    
                                                    $query = $dbh->prepare($query);
                                                    $query->bindParam(':rollid', $rollid, PDO::PARAM_STR);
                                                    $query->bindParam(':classid', $classid, PDO::PARAM_STR);
                                                    $query->bindParam(':exam_category', $exam_category, PDO::PARAM_STR);
                                                    $query->execute();
                                                    $results = $query->fetchAll(PDO::FETCH_OBJ);

                                                    $cnt = 1;
                                                    $totlcount = 0;

                                                    if ($query->rowCount() > 0) {
                                                        foreach ($results as $result) {
                                                            ?>
                                                    <tr>
                                                        <th scope="row"><?php echo htmlentities($cnt); ?></th>
                                                        <td><?php echo htmlentities($result->SubjectName); ?></td>
                                                        <td><?php echo htmlentities($totalmarks = $result->marks); ?>
                                                        </td>
                                                    </tr>
                                                    <?php 
                                                            $totlcount += $totalmarks;
                                                            $cnt++;
                                                        } 

                                                        // Calculate total out of marks
                                                        $outof = ($cnt - 1) * 100;
                                                        // Calculate percentage
                                                        $percentage = ($totlcount * 100) / $outof;
                                                        ?>
                                                    <tr>
                                                        <th scope="row" colspan="2">Total Marks</th>
                                                        <td><b><?php echo htmlentities($totlcount); ?></b> out of
                                                            <b><?php echo htmlentities($outof); ?></b>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <th scope="row" colspan="2">Percentage</th>
                                                        <td><b><?php echo htmlentities(number_format($percentage, 2)); ?>%</b>
                                                        </td>
                                                    </tr>

                                                    <!-- Add Evaluation based on percentage -->
                                                    <tr>
                                                        <th scope="row" colspan="2">Evaluation</th>
                                                        <td><b>
                                                                <?php
                                                                if ($percentage >= 90) {
                                                                    echo "Excellent";
                                                                } elseif ($percentage >= 76) {
                                                                    echo "Very Good";
                                                                } elseif ($percentage >= 66) {
                                                                    echo "Good";
                                                                } elseif ($percentage >= 50) {
                                                                    echo "Acceptable";
                                                                } else {
                                                                    echo "Fail";
                                                                }
                                                                ?>
                                                            </b>
                                                        </td>
                                                    </tr>
                                                    <?php } else { ?>
                                                    <div class="alert alert-warning left-icon-alert" role="alert">
                                                        <strong>Notice!</strong> Your result is not declared yet.
                                                    </div>
                                                    <?php } ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <!-- <div class="col-sm-6">
                                    <a href="index.php">Back to Home</a>
                                </div> -->
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div>

    <!-- JS -->
    <script src="js/jquery/jquery-2.2.4.min.js"></script>
    <script src="js/bootstrap/bootstrap.min.js"></script>
    <script src="js/main.js"></script>

</body>

</html>