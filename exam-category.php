<?php
session_start();
error_reporting(0);
include('includes/config.php');

if(strlen($_SESSION['alogin'])=="") {
    header("Location: index.php");
} else {
    // Add Exam Category
    if(isset($_POST['submit'])) {
        $exam_category = $_POST['exam_category'];
        $exam_date = $_POST['exam_date'];
        $status = $_POST['status'];

        // Check if the exam category with the same date already exists in tblexamcategories
        $checkSql = "SELECT * FROM tblexamcategories WHERE exam_category=:exam_category AND exam_date=:exam_date";
        $checkQuery = $dbh->prepare($checkSql);
        $checkQuery->bindParam(':exam_category', $exam_category, PDO::PARAM_STR);
        $checkQuery->bindParam(':exam_date', $exam_date, PDO::PARAM_STR);
        $checkQuery->execute();
        $exists = $checkQuery->rowCount();

        if($exists > 0) {
            $_SESSION['error'] = "Exam Category with the same name and date already exists";
        } else {
            // Insert the new exam category, date, and status into tblexamcategories
            $sql = "INSERT INTO tblexamcategories (exam_category, exam_date, status) VALUES (:exam_category, :exam_date, :status)";
            $query = $dbh->prepare($sql);
            $query->bindParam(':exam_category', $exam_category, PDO::PARAM_STR);
            $query->bindParam(':exam_date', $exam_date, PDO::PARAM_STR);
            $query->bindParam(':status', $status, PDO::PARAM_STR);
            $query->execute();
            $lastInsertId = $dbh->lastInsertId();

            if($lastInsertId) {
                $_SESSION['msg'] = "Exam Category added successfully";
            } else {
                $_SESSION['error'] = "Something went wrong. Please try again";
            }
        }
        // Redirect to clear form data and prevent resubmission
        header("Location: exam-category.php");
        exit();
    }

    // Toggle Status
    if(isset($_GET['toggle'])) {
        $id = $_GET['toggle'];
        $sql = "UPDATE tblexamcategories SET status = IF(status='Active', 'Inactive', 'Active') WHERE id=:id";
        $query = $dbh->prepare($sql);
        $query->bindParam(':id', $id, PDO::PARAM_STR);
        $query->execute();
        $_SESSION['msg'] = "Exam Category status updated successfully";
        // Redirect to clear query string
        header("Location: exam-category.php");
        exit();
    }

    // Delete Exam Category
    if(isset($_GET['del'])) {
        $id = $_GET['del'];
        $sql = "DELETE FROM tblexamcategories WHERE id=:id";
        $query = $dbh->prepare($sql);
        $query->bindParam(':id', $id, PDO::PARAM_STR);
        $query->execute();
        $_SESSION['msg'] = "Exam Category deleted successfully";
        // Redirect to clear query string
        header("Location: exam-category.php");
        exit();
    }
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Admin Manage Exam Categories</title>
    <link rel="stylesheet" href="css/bootstrap.min.css" media="screen">
    <link rel="stylesheet" href="css/font-awesome.min.css" media="screen">
    <link rel="stylesheet" type="text/css" href="js/DataTables/datatables.min.css" />
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
                            <div class="col-md-6">
                                <h2 class="title">Manage Exam Categories</h2>
                            </div>
                        </div>
                        <div class="row breadcrumb-div">
                            <div class="col-md-6">
                                <ul class="breadcrumb">
                                    <li><a href="dashboard.php"><i class="fa fa-home"></i> Home</a></li>
                                    <li>Exam Categories</li>
                                    <li class="active">Manage Exam Categories</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <section class="section">
                        <div class="container-fluid">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="panel">
                                        <div class="panel-heading">
                                            <div class="panel-title">
                                                <h5>Add Exam Category</h5>
                                            </div>
                                        </div>
                                        <div class="panel-body">
                                            <?php if(isset($_SESSION['msg'])) { ?>
                                            <div class="alert alert-success left-icon-alert" role="alert">
                                                <strong>Well done!</strong>
                                                <?php echo htmlentities($_SESSION['msg']); ?>
                                            </div>
                                            <?php unset($_SESSION['msg']); // Clear the message ?>
                                            <?php } else if(isset($_SESSION['error'])) { ?>
                                            <div class="alert alert-danger left-icon-alert" role="alert">
                                                <strong>Oh snap!</strong>
                                                <?php echo htmlentities($_SESSION['error']); ?>
                                            </div>
                                            <?php unset($_SESSION['error']); // Clear the error ?>
                                            <?php } ?>
                                            <form class="form-horizontal" method="post">
                                                <div class="form-group">
                                                    <label for="exam_category" class="col-sm-2 control-label">Exam
                                                        Category</label>
                                                    <div class="col-sm-10">
                                                        <input type="text" name="exam_category" class="form-control"
                                                            id="exam_category" required="required">
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="exam_date" class="col-sm-2 control-label">Exam
                                                        Date</label>
                                                    <div class="col-sm-10">
                                                        <input type="date" name="exam_date" class="form-control"
                                                            id="exam_date" required="required">
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label class="col-sm-2 control-label">Status</label>
                                                    <div class="col-sm-10">
                                                        <label class="radio-inline">
                                                            <input type="radio" name="status" value="Active" checked>
                                                            Active
                                                        </label>
                                                        <label class="radio-inline">
                                                            <input type="radio" name="status" value="Inactive"> Inactive
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <div class="col-sm-offset-2 col-sm-10">
                                                        <button type="submit" name="submit" class="btn btn-primary">Add
                                                            Exam Category</button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>

                                    <div class="panel">
                                        <div class="panel-heading">
                                            <div class="panel-title">
                                                <h5>View Exam Categories Info</h5>
                                            </div>
                                        </div>
                                        <div class="panel-body p-20">
                                            <table id="example" class="display table table-striped table-bordered"
                                                cellspacing="0" width="100%">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Exam Category</th>
                                                        <th>Exam Date</th>
                                                        <th>Status</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    // Fetch exam categories from tblexamcategories
                                                    $sql = "SELECT * FROM tblexamcategories";
                                                    $query = $dbh->prepare($sql);
                                                    $query->execute();
                                                    $results = $query->fetchAll(PDO::FETCH_OBJ);
                                                    $cnt = 1;
                                                    if($query->rowCount() > 0) {
                                                        foreach($results as $result) { ?>
                                                    <tr>
                                                        <td><?php echo htmlentities($cnt);?></td>
                                                        <td><?php echo htmlentities($result->exam_category);?></td>
                                                        <td><?php echo htmlentities($result->exam_date);?></td>
                                                        <td>
                                                            <?php echo htmlentities($result->status); ?>
                                                            <a href="exam-category.php?toggle=<?php echo htmlentities($result->id);?>"
                                                                class="btn btn-default btn-sm"><?php echo $result->status == 'Active' ? 'Set Inactive' : 'Set Active'; ?></a>
                                                        </td>
                                                        <td>
                                                            <a href="exam-category.php?del=<?php echo htmlentities($result->id);?>"
                                                                onclick="return confirm('Do you really want to delete this record?');"><i
                                                                    class="fa fa-trash" title="Delete Record"></i> </a>
                                                        </td>
                                                    </tr>
                                                    <?php $cnt = $cnt + 1; }
                                                    } ?>
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
    <script src="js/jquery/jquery-2.2.4.min.js"></script>
    <script src="js/bootstrap/bootstrap.min.js"></script>
    <script src="js/DataTables/datatables.min.js"></script>
    <script>
    $(function($) {
        $('#example').DataTable();
    });
    </script>
</body>

</html>
<?php } ?>