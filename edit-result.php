<?php
session_start();
error_reporting(0);
include('includes/config.php');
if(strlen($_SESSION['alogin'])=="") {   
    header("Location: index.php"); 
}
else {

$stid = intval($_GET['stid']);
$exam_category = isset($_POST['exam_category']) ? $_POST['exam_category'] : ''; // Fetch exam category from form

if(isset($_POST['submit'])) {

    $rowid = $_POST['id'] ?? [];
    $marks = $_POST['marks'] ?? []; 
    $exam_category = $_POST['exam_category'] ?? ''; // Get the exam category from form

    // Debugging: Check if exam_category and other inputs are set
    // echo "<pre>";
    // echo "Submitted Data: ";
    // print_r($_POST);  // To display all submitted form data
    // echo "</pre>";

    if(empty($rowid) || empty($marks) || empty($exam_category)) {
        $error = "Missing data! Please make sure all fields are filled correctly.";
    } else {
        foreach($_POST['id'] as $count => $id) {
            $mrks = $marks[$count] ?? null;
            $iid = $rowid[$count] ?? null;
            
            // Only update if we have valid marks and id
            if($mrks !== null && $iid !== null) {
                $sql = "UPDATE tblresult SET marks=:mrks WHERE id=:iid AND exam_category=:exam_category";
                $query = $dbh->prepare($sql);
                $query->bindParam(':mrks', $mrks, PDO::PARAM_STR);
                $query->bindParam(':iid', $iid, PDO::PARAM_STR);
                $query->bindParam(':exam_category', $exam_category, PDO::PARAM_STR);
                $query->execute();
                $msg = "Result info updated successfully";
            }
        }
    }
}

// Handle delete functionality
if(isset($_POST['delete'])) {
    $rowid = $_POST['id'] ?? []; // Get the result id
    foreach($rowid as $iid) {
        $sql = "DELETE FROM tblresult WHERE id=:iid";
        $query = $dbh->prepare($sql);
        $query->bindParam(':iid', $iid, PDO::PARAM_STR);
        $query->execute();
        $msg = "Result deleted successfully";
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SMS Admin | Student result info</title>
    <link rel="stylesheet" href="css/bootstrap.min.css" media="screen">
    <link rel="stylesheet" href="css/font-awesome.min.css" media="screen">
    <link rel="stylesheet" href="css/main.css" media="screen">
    <script src="js/modernizr/modernizr.min.js"></script>

    <!-- JavaScript to handle confirmation alerts -->
    <script type="text/javascript">
    function confirmAction(action) {
        if (action === 'update') {
            return confirm('Are you sure you want to update the result?');
        } else if (action === 'delete') {
            return confirm('Are you sure you want to delete this result?');
        }
    }
    </script>
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
                                <h2 class="title">Student Result Info</h2>
                            </div>
                        </div>
                        <div class="row breadcrumb-div">
                            <div class="col-md-6">
                                <ul class="breadcrumb">
                                    <li><a href="dashboard.php"><i class="fa fa-home"></i> Home</a></li>
                                    <li class="active">Result Info</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="panel">
                                    <div class="panel-heading">
                                        <div class="panel-title">
                                            <h5>Update or Delete the Result info</h5>
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

                                        <!-- Add form to filter by exam category -->
                                        <form class="form-horizontal" method="post">
                                            <div class="form-group">
                                                <label for="exam_category" class="col-sm-2 control-label">Exam
                                                    Category</label>
                                                <div class="col-sm-10">
                                                    <select name="exam_category" class="form-control"
                                                        required="required">
                                                        <option value="First Exam"
                                                            <?php if($exam_category == 'First Exam') echo 'selected'; ?>>
                                                            First Exam</option>
                                                        <option value="Second Exam"
                                                            <?php if($exam_category == 'Second Exam') echo 'selected'; ?>>
                                                            Second Exam</option>
                                                        <option value="Midterm"
                                                            <?php if($exam_category == 'Midterm') echo 'selected'; ?>>
                                                            Midterm</option>
                                                        <option value="Final Exam"
                                                            <?php if($exam_category == 'Final Exam') echo 'selected'; ?>>
                                                            Final Exam</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <button type="submit" name="filter" class="btn btn-primary">Filter
                                                Results</button>
                                        </form>

                                        <!-- Display student and result info -->
                                        <form class="form-horizontal" method="post">
                                            <!-- Hidden input to store the exam category -->
                                            <input type="hidden" name="exam_category"
                                                value="<?php echo htmlentities($exam_category); ?>">

                                            <?php
                                                // Fetch student details
                                                $ret = "SELECT tblstudents.StudentName, tblclasses.ClassName, tblclasses.Section 
                                                        FROM tblresult 
                                                        JOIN tblstudents ON tblresult.StudentId = tblstudents.StudentId 
                                                        JOIN tblclasses ON tblclasses.id = tblstudents.ClassId 
                                                        WHERE tblstudents.StudentId = :stid 
                                                        LIMIT 1";
                                                $stmt = $dbh->prepare($ret);
                                                $stmt->bindParam(':stid', $stid, PDO::PARAM_STR);
                                                $stmt->execute();
                                                $result = $stmt->fetchAll(PDO::FETCH_OBJ);
                                                if($stmt->rowCount() > 0) {
                                                    foreach($result as $row) {
                                                ?>
                                            <div class="form-group">
                                                <label for="default" class="col-sm-2 control-label">Class</label>
                                                <div class="col-sm-10">
                                                    <?php echo htmlentities($row->ClassName); ?>
                                                    (<?php echo htmlentities($row->Section); ?>)
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label for="default" class="col-sm-2 control-label">Full Name</label>
                                                <div class="col-sm-10">
                                                    <?php echo htmlentities($row->StudentName); ?>
                                                </div>
                                            </div>
                                            <?php } } ?>

                                            <?php
                                                // Fetch results based on the selected exam category
                                                $sql = "SELECT tblstudents.StudentName, tblstudents.StudentId, tblclasses.ClassName, tblclasses.Section, tblsubjects.SubjectName, tblresult.marks, tblresult.id as resultid 
                                                        FROM tblresult 
                                                        JOIN tblstudents ON tblstudents.StudentId = tblresult.StudentId 
                                                        JOIN tblsubjects ON tblsubjects.id = tblresult.SubjectId 
                                                        JOIN tblclasses ON tblclasses.id = tblstudents.ClassId 
                                                        WHERE tblstudents.StudentId = :stid AND tblresult.exam_category = :exam_category";
                                                $query = $dbh->prepare($sql);
                                                $query->bindParam(':stid', $stid, PDO::PARAM_STR);
                                                $query->bindParam(':exam_category', $exam_category, PDO::PARAM_STR);
                                                $query->execute();
                                                $results = $query->fetchAll(PDO::FETCH_OBJ);

                                                $totalMarks = 0; // To calculate total marks
                                                $subjectCount = $query->rowCount(); // Total number of subjects

                                                if($query->rowCount() > 0) {
                                                    foreach($results as $result) {
                                                        $totalMarks += $result->marks; // Add to total marks
                                                ?>
                                            <div class="form-group">
                                                <label for="default"
                                                    class="col-sm-2 control-label"><?php echo htmlentities($result->SubjectName); ?></label>
                                                <div class="col-sm-10">
                                                    <input type="hidden" name="id[]"
                                                        value="<?php echo htmlentities($result->resultid); ?>">
                                                    <input type="text" name="marks[]" class="form-control" id="marks"
                                                        value="<?php echo htmlentities($result->marks); ?>"
                                                        maxlength="5" required="required" autocomplete="off">
                                                </div>
                                            </div>
                                            <?php } } ?>

                                            <!-- Display total marks and percentage -->
                                            <?php if ($subjectCount > 0) { ?>
                                            <div class="form-group">
                                                <label for="total" class="col-sm-2 control-label">Total Marks</label>
                                                <div class="col-sm-10">
                                                    <?php echo htmlentities($totalMarks); ?> out of
                                                    <?php echo htmlentities($subjectCount * 100); ?>
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label for="percentage"
                                                    class="col-sm-2 control-label">Percentage</label>
                                                <div class="col-sm-10">
                                                    <?php echo htmlentities(($totalMarks / ($subjectCount * 100)) * 100); ?>%
                                                </div>
                                            </div>
                                            <?php } ?>

                                            <div class="form-group">
                                                <div class="col-sm-offset-2 col-sm-10">
                                                    <button type="submit" name="submit" class="btn btn-primary"
                                                        onclick="return confirmAction('update');">Update</button>
                                                    <button type="submit" name="delete" class="btn btn-danger"
                                                        onclick="return confirmAction('delete');">Delete</button>
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
    </div>

    <!-- JS -->
    <script src="js/jquery/jquery-2.2.4.min.js"></script>
    <script src="js/bootstrap/bootstrap.min.js"></script>
    <script src="js/main.js"></script>
</body>

</html>
<?php } ?>