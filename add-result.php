<?php
session_start();
error_reporting(E_ALL);
include('includes/config.php');

// Enable PDO Exception mode to handle and display errors clearly
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Initialize $msg and $error to prevent "undefined variable" warnings
$msg = "";
$error = "";

if (strlen($_SESSION['alogin']) == "") {
    header("Location: index.php");
} else {
    if (isset($_POST['submit'])) {
        // Extract form data
        $marks = $_POST['marks'] ?? [];
        $class = $_POST['class'] ?? '';
        $studentid = $_POST['studentid'] ?? '';
        $exam_category = $_POST['exam_category'] ?? '';  // Exam category
        $exam_date = $_POST['exam_date'] ?? '';          // Exam date

        // Input validation
        if (empty($class) || empty($studentid) || empty($exam_category) || empty($exam_date) || empty($marks)) {
            $error = "All fields are required. Please make sure everything is filled out correctly.";
        } else {
            try {
                // Check if a result for the same student, class, exam category, and exam date already exists
                $checkResult = "SELECT * FROM tblresult 
                                WHERE StudentId = :studentid 
                                AND ClassId = :class 
                                AND exam_category = :exam_category 
                                AND exam_date = :exam_date";
                $query = $dbh->prepare($checkResult);
                $query->bindParam(':studentid', $studentid, PDO::PARAM_STR);
                $query->bindParam(':class', $class, PDO::PARAM_STR);
                $query->bindParam(':exam_category', $exam_category, PDO::PARAM_STR);
                $query->bindParam(':exam_date', $exam_date, PDO::PARAM_STR);
                $query->execute();
                $resultExists = $query->rowCount();

                if ($resultExists > 0) {
                    $error = "Result Already Declared for this Student, Class, Exam Category, and Date";
                } else {
                    // Start transaction to ensure all-or-nothing database writes
                    $dbh->beginTransaction();

                    // Fetch **only active subjects** associated with the given class
                    $stmt = $dbh->prepare("SELECT tblsubjects.SubjectName, tblsubjects.id 
                                           FROM tblsubjectcombination 
                                           JOIN tblsubjects ON tblsubjects.id = tblsubjectcombination.SubjectId 
                                           WHERE tblsubjectcombination.ClassId = :cid 
                                           AND tblsubjectcombination.status = 1  -- Only active subjects
                                           ORDER BY tblsubjects.SubjectName");
                    $stmt->execute(array(':cid' => $class));
                    $subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    $sid1 = array();

                    foreach ($subjects as $row) {
                        array_push($sid1, $row['id']);
                    }

                    // Ensure that the number of subjects matches the number of marks provided
                    if (count($sid1) !== count($marks)) {
                        $error = "Mismatch between the number of active subjects and the number of marks provided. Active Subjects fetched: " . count($sid1) . ", Marks provided: " . count($marks);
                    } else {
                        // Insert results for each subject
                        foreach ($marks as $i => $mar) {
                            $sid = $sid1[$i];

                            // Insert result
                            $sql = "INSERT INTO tblresult (StudentId, ClassId, SubjectId, marks, exam_category, exam_date) 
                                    VALUES (:studentid, :class, :sid, :marks, :exam_category, :exam_date)";
                            $query = $dbh->prepare($sql);
                            $query->bindParam(':studentid', $studentid, PDO::PARAM_STR);
                            $query->bindParam(':class', $class, PDO::PARAM_STR);
                            $query->bindParam(':sid', $sid, PDO::PARAM_STR);
                            $query->bindParam(':marks', $mar, PDO::PARAM_STR);
                            $query->bindParam(':exam_category', $exam_category, PDO::PARAM_STR);
                            $query->bindParam(':exam_date', $exam_date, PDO::PARAM_STR);

                            if (!$query->execute()) {
                                // Rollback the transaction if insert fails
                                $dbh->rollBack();
                                $error = "Something went wrong while inserting the results for Subject ID: $sid. Please try again.";
                                break;
                            }
                        }

                        // Commit transaction if no errors
                        if (empty($error)) {
                            $dbh->commit();
                            $msg = "Result info added successfully";
                        }
                    }
                }
            } catch (Exception $e) {
                $dbh->rollBack();
                $error = "Exception occurred: " . $e->getMessage();
            }
        }
    }
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SMS Admin | Add Result</title>
    <link rel="stylesheet" href="css/bootstrap.min.css" media="screen">
    <link rel="stylesheet" href="css/font-awesome.min.css" media="screen">
    <link rel="stylesheet" href="css/animate-css/animate.min.css" media="screen">
    <link rel="stylesheet" href="css/lobipanel/lobipanel.min.css" media="screen">
    <link rel="stylesheet" href="css/prism/prism.css" media="screen">
    <link rel="stylesheet" href="css/select2/select2.min.css">
    <link rel="stylesheet" href="css/main.css" media="screen">
    <script src="js/modernizr/modernizr.min.js"></script>
    <script src="js/jquery/jquery-2.2.4.min.js"></script>

    <style>
    .bottom-centered-logo {
        position: fixed;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 400px;
        height: auto;
        opacity: 0.2;
        z-index: -1;
    }
    </style>

    <script>
    // Function to fetch students dynamically based on class and exam category
    function getStudent(classId, examCategory) {
        $.ajax({
            type: "POST",
            url: "get_student.php",
            data: {
                'classid': classId,
                'exam_category': examCategory
            },
            success: function(data) {
                $("#studentid").html(data);
            }
        });
    }

    // Function to fetch subjects dynamically based on the selected class
    function getSubjects(classId) {
        $.ajax({
            type: "POST",
            url: "get_student.php",
            data: {
                'classid1': classId
            },
            success: function(data) {
                $("#subject").html(data);
            }
        });
    }

    // Function to fetch exam date dynamically based on the selected exam category
    function getExamDate(examCategoryId) {
        $.ajax({
            type: "POST",
            url: "get_exam_date.php",
            data: {
                'exam_category_id': examCategoryId
            },
            success: function(data) {
                $("#exam_date").val(data);
            },
            error: function(xhr, status, error) {
                console.error("Error occurred: " + error);
            }
        });
    }

    $(document).ready(function() {
        // Trigger AJAX when class is changed for students
        $('#classid').change(function() {
            var classId = $(this).val();
            var examCategory = $('#exam_category').val();
            getStudent(classId, examCategory);
            getSubjects(classId);
        });

        // Trigger AJAX when exam category is changed for students and to get the exam date
        $('#exam_category').change(function() {
            var examCategoryId = $(this).val();
            var classId = $('#classid').val();
            getStudent(classId, examCategoryId);
            getExamDate(examCategoryId);
        });
    });
    </script>
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
                                <h2 class="title">Add Result</h2>
                            </div>
                        </div>

                        <div class="row breadcrumb-div">
                            <div class="col-md-6">
                                <ul class="breadcrumb">
                                    <li><a href="dashboard.php"><i class="fa fa-home"></i> Home</a></li>
                                    <li class="active">Student Result</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="panel">
                                    <div class="panel-body">
                                        <?php if($msg){ ?>
                                        <div class="alert alert-success left-icon-alert" role="alert">
                                            <strong>Well done!</strong> <?php echo htmlentities($msg); ?>
                                        </div>
                                        <?php } else if($error){ ?>
                                        <div class="alert alert-danger left-icon-alert" role="alert">
                                            <strong>!!!</strong> <?php echo htmlentities($error); ?>
                                        </div>
                                        <?php } ?>

                                        <!-- Form Start -->
                                        <form class="form-horizontal" method="post">

                                            <!-- Exam Category -->
                                            <div class="form-group">
                                                <label for="exam_category" class="col-sm-2 control-label">Exam
                                                    Category</label>
                                                <div class="col-sm-10">
                                                    <select name="exam_category" id="exam_category" class="form-control"
                                                        required="required" onchange="getExamDate(this.value)">
                                                        <option value="">Select Exam Category</option>
                                                        <?php 
                                                        // Fetch all active exam categories from tblexamcategories
                                                        try {
                                                            $sql = "SELECT * FROM tblexamcategories WHERE status = 'Active'";
                                                            $query = $dbh->prepare($sql);
                                                            $query->execute();
                                                            $results = $query->fetchAll(PDO::FETCH_OBJ);

                                                            if ($query->rowCount() > 0) {
                                                                foreach($results as $result) { ?>
                                                        <option value="<?php echo htmlentities($result->id); ?>">
                                                            <?php echo htmlentities($result->exam_category) . " - " . date('Y', strtotime($result->exam_date)); ?>
                                                        </option>
                                                        <?php }
                                                            } else {
                                                                echo "<option value=''>No active exam categories available</option>";
                                                            }
                                                        } catch (Exception $e) {
                                                            echo "<option value=''>Error fetching categories: " . htmlentities($e->getMessage()) . "</option>";
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                            </div>

                                            <!-- Exam Date -->
                                            <div class="form-group">
                                                <label for="exam_date" class="col-sm-2 control-label">Exam Date</label>
                                                <div class="col-sm-10">
                                                    <input type="text" name="exam_date" id="exam_date"
                                                        class="form-control" required="required" readonly>
                                                </div>
                                            </div>

                                            <!-- Class Selection -->
                                            <div class="form-group">
                                                <label for="classid" class="col-sm-2 control-label">Class</label>
                                                <div class="col-sm-10">
                                                    <select name="class" id="classid" class="form-control"
                                                        required="required">
                                                        <option value="">Select Class</option>
                                                        <?php 
                                                        $sql = "SELECT * from tblclasses";
                                                        $query = $dbh->prepare($sql);
                                                        $query->execute();
                                                        $results = $query->fetchAll(PDO::FETCH_OBJ);
                                                        if ($query->rowCount() > 0) {
                                                            foreach($results as $result) { ?>
                                                        <option value="<?php echo htmlentities($result->id); ?>">
                                                            <?php echo htmlentities($result->ClassName); ?>&nbsp;Section-<?php echo htmlentities($result->Section); ?>
                                                        </option>
                                                        <?php }} ?>
                                                    </select>
                                                </div>
                                            </div>

                                            <!-- Student Selection -->
                                            <div class="form-group">
                                                <label for="studentid" class="col-sm-2 control-label">Student
                                                    Name</label>
                                                <div class="col-sm-10">
                                                    <select name="studentid" id="studentid" class="form-control"
                                                        required="required">
                                                        <!-- Student options will be populated via AJAX -->
                                                    </select>
                                                </div>
                                            </div>

                                            <!-- Subject Selection -->
                                            <div class="form-group">
                                                <label for="subject" class="col-sm-2 control-label">Subjects</label>
                                                <div class="col-sm-10">
                                                    <div id="subject">
                                                        <!-- Subjects will be dynamically inserted here via AJAX -->
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Submit Button -->
                                            <div class="form-group">
                                                <div class="col-sm-offset-2 col-sm-10">
                                                    <button type="submit" name="submit" class="btn btn-primary">Declare
                                                        Result</button>
                                                </div>
                                            </div>
                                        </form>
                                        <!-- Form End -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /.content-container -->
            </div>
            <!-- /.content-wrapper -->
        </div>
        <!-- /.main-wrapper -->

        <script src="js/bootstrap/bootstrap.min.js"></script>
        <script src="js/pace/pace.min.js"></script>
        <script src="js/lobipanel/lobipanel.min.js"></script>
        <script src="js/iscroll/iscroll.js"></script>
        <script src="js/prism/prism.js"></script>
        <script src="js/select2/select2.min.js"></script>
        <script src="js/main.js"></script>

        <div class="bottom-centered-logo">
            <img src="logo.jpeg" alt="Bottom Centered Logo">
        </div>
</body>

</html>
<?php } ?>