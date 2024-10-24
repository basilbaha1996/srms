<?php
session_start();
error_reporting(0);
include('includes/config.php');
if(strlen($_SESSION['alogin']) == "") {   
    header("Location: index.php"); 
} else {

    $stid = intval($_GET['stid']);

    // Handle form submission for update
    if(isset($_POST['submit'])) {
        $studentname = $_POST['fullanme'];
        $roolid = $_POST['rollid']; 
        $studentemail = $_POST['emailid']; 
        $gender = $_POST['gender']; 
        $dob = $_POST['dob']; 
        $status = $_POST['status'];
        
        $sql = "UPDATE tblstudents 
                SET StudentName = :studentname, RollId = :roolid, StudentEmail = :studentemail, Gender = :gender, DOB = :dob, Status = :status 
                WHERE StudentId = :stid";
        $query = $dbh->prepare($sql);
        $query->bindParam(':studentname', $studentname, PDO::PARAM_STR);
        $query->bindParam(':roolid', $roolid, PDO::PARAM_STR);
        $query->bindParam(':studentemail', $studentemail, PDO::PARAM_STR);
        $query->bindParam(':gender', $gender, PDO::PARAM_STR);
        $query->bindParam(':dob', $dob, PDO::PARAM_STR);
        $query->bindParam(':status', $status, PDO::PARAM_STR);
        $query->bindParam(':stid', $stid, PDO::PARAM_STR);
        $query->execute();

        $msg = "Student info updated successfully";
    }

    // Handle delete request
    if(isset($_POST['delete'])) {
        $sql = "DELETE FROM tblstudents WHERE StudentId = :stid";
        $query = $dbh->prepare($sql);
        $query->bindParam(':stid', $stid, PDO::PARAM_STR);
        $query->execute();

        header("Location: manage-students.php"); // Redirect to manage students page after deletion
    }

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SMS Admin | Edit Student</title>
    <link rel="stylesheet" href="css/bootstrap.min.css" media="screen">
    <link rel="stylesheet" href="css/font-awesome.min.css" media="screen">
    <link rel="stylesheet" href="css/animate-css/animate.min.css" media="screen">
    <link rel="stylesheet" href="css/lobipanel/lobipanel.min.css" media="screen">
    <link rel="stylesheet" href="css/prism/prism.css" media="screen">
    <link rel="stylesheet" href="css/select2/select2.min.css">
    <link rel="stylesheet" href="css/main.css" media="screen">
    <script src="js/modernizr/modernizr.min.js"></script>
</head>

<body class="top-navbar-fixed">
    <div class="main-wrapper">

        <!-- ========== TOP NAVBAR ========== -->
        <?php include('includes/topbar.php'); ?>
        <!-- ========== WRAPPER FOR BOTH SIDEBARS & MAIN CONTENT ========== -->
        <div class="content-wrapper">
            <div class="content-container">

                <!-- ========== LEFT SIDEBAR ========== -->
                <?php include('includes/leftbar.php'); ?>
                <!-- /.left-sidebar -->

                <div class="main-page">

                    <div class="container-fluid">
                        <div class="row page-title-div">
                            <div class="col-md-6">
                                <h2 class="title">Edit Student Information</h2>
                            </div>
                        </div>
                        <!-- /.row -->
                        <div class="row breadcrumb-div">
                            <div class="col-md-6">
                                <ul class="breadcrumb">
                                    <li><a href="dashboard.php"><i class="fa fa-home"></i> Home</a></li>
                                    <li class="active">Edit Student Information</li>
                                </ul>
                            </div>
                        </div>
                        <!-- /.row -->
                    </div>

                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="panel">
                                    <div class="panel-heading">
                                        <div class="panel-title">
                                            <h5>Fill the Student info</h5>
                                        </div>
                                    </div>
                                    <div class="panel-body">
                                        <?php if($msg){ ?>
                                        <div class="alert alert-success left-icon-alert" role="alert">
                                            <strong>Well done!</strong><?php echo htmlentities($msg); ?>
                                        </div>
                                        <?php } else if($error){ ?>
                                        <div class="alert alert-danger left-icon-alert" role="alert">
                                            <strong>error!</strong> <?php echo htmlentities($error); ?>
                                        </div>
                                        <?php } ?>

                                        <form class="form-horizontal" method="post">
                                            <?php 
                                                $sql = "SELECT tblstudents.StudentName, tblstudents.RollId, tblstudents.RegDate, tblstudents.StudentId, tblstudents.Status, tblstudents.StudentEmail, tblstudents.Gender, tblstudents.DOB, tblclasses.ClassName, tblclasses.Section 
                                                        FROM tblstudents 
                                                        JOIN tblclasses ON tblclasses.id = tblstudents.ClassId 
                                                        WHERE tblstudents.StudentId = :stid";
                                                $query = $dbh->prepare($sql);
                                                $query->bindParam(':stid', $stid, PDO::PARAM_STR);
                                                $query->execute();
                                                $results = $query->fetchAll(PDO::FETCH_OBJ);

                                                if($query->rowCount() > 0) {
                                                    foreach($results as $result) { 
                                            ?>
                                            <div class="form-group">
                                                <label for="fullanme" class="col-sm-2 control-label">Full Name</label>
                                                <div class="col-sm-10">
                                                    <input type="text" name="fullanme" class="form-control"
                                                        value="<?php echo htmlentities($result->StudentName); ?>"
                                                        required="required">
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label for="rollid" class="col-sm-2 control-label">Roll Id</label>
                                                <div class="col-sm-10">
                                                    <input type="text" name="rollid" class="form-control"
                                                        value="<?php echo htmlentities($result->RollId); ?>"
                                                        maxlength="5" required="required">
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label for="emailid" class="col-sm-2 control-label">Email ID</label>
                                                <div class="col-sm-10">
                                                    <input type="email" name="emailid" class="form-control"
                                                        value="<?php echo htmlentities($result->StudentEmail); ?>"
                                                        required="required">
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label for="gender" class="col-sm-2 control-label">Gender</label>
                                                <div class="col-sm-10">
                                                    <input type="radio" name="gender" value="Male"
                                                        <?php if($result->Gender == 'Male'){ echo "checked"; } ?>> Male
                                                    <input type="radio" name="gender" value="Female"
                                                        <?php if($result->Gender == 'Female'){ echo "checked"; } ?>>
                                                    Female
                                                    <input type="radio" name="gender" value="Other"
                                                        <?php if($result->Gender == 'Other'){ echo "checked"; } ?>>
                                                    Other
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label for="dob" class="col-sm-2 control-label">DOB</label>
                                                <div class="col-sm-10">
                                                    <input type="date" name="dob" class="form-control"
                                                        value="<?php echo htmlentities($result->DOB); ?>">
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label for="classname" class="col-sm-2 control-label">Class</label>
                                                <div class="col-sm-10">
                                                    <input type="text" class="form-control"
                                                        value="<?php echo htmlentities($result->ClassName) . ' (' . htmlentities($result->Section) . ')'; ?>"
                                                        readonly>
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label for="status" class="col-sm-2 control-label">Status</label>
                                                <div class="col-sm-10">
                                                    <input type="radio" name="status" value="1"
                                                        <?php if($result->Status == '1'){ echo "checked"; } ?>> Active
                                                    <input type="radio" name="status" value="0"
                                                        <?php if($result->Status == '0'){ echo "checked"; } ?>> Block
                                                </div>
                                            </div>

                                            <?php }} ?>

                                            <div class="form-group">
                                                <div class="col-sm-offset-2 col-sm-10">
                                                    <button type="submit" name="submit"
                                                        class="btn btn-primary">Update</button>
                                                    <button type="submit" name="delete" class="btn btn-danger"
                                                        onclick="return confirm('Are you sure you want to delete this student?');">Delete
                                                        Student</button>
                                                </div>
                                            </div>
                                        </form>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <script src="js/jquery/jquery-2.2.4.min.js"></script>
        <script src="js/bootstrap/bootstrap.min.js"></script>
        <script src="js/pace/pace.min.js"></script>
        <script src="js/lobipanel/lobipanel.min.js"></script>
        <script src="js/iscroll/iscroll.js"></script>
        <script src="js/prism/prism.js"></script>
        <script src="js/select2/select2.min.js"></script>
        <script src="js/main.js"></script>
        <script>
        $(function($) {
            $(".js-states").select2();
            $(".js-states-limit").select2({
                maximumSelectionLength: 2
            });
            $(".js-states-hide").select2({
                minimumResultsForSearch: Infinity
            });
        });
        </script>
</body>

</html>
<?php } ?>