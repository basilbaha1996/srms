<?php
session_start();
error_reporting(0);
include('includes/config.php');

if(strlen($_SESSION['alogin']) == "") {   
    header("Location: index.php"); 
} else {
    $stid = intval($_GET['stid']);

    // If a form is submitted to update marks or delete result
    if(isset($_POST['submit']) || isset($_POST['delete'])) {
        $rowid = $_POST['id'] ?? [];
        $marks = $_POST['marks'] ?? []; 
        $exam_category = $_POST['exam_category'] ?? '';

        if(empty($rowid) || empty($marks) || empty($exam_category)) {
            $error = "Missing data! Please make sure all fields are filled correctly.";
        } else {
            if(isset($_POST['submit'])) {
                // Update results
                foreach($_POST['id'] as $count => $id) {
                    $mrks = $marks[$count] ?? null;
                    $iid = $rowid[$count] ?? null;

                    if($mrks !== null && $iid !== null) {
                        $sql = "UPDATE tblresult SET marks=:mrks WHERE id=:iid AND exam_category=:exam_category";
                        $query = $dbh->prepare($sql);
                        $query->bindParam(':mrks', $mrks, PDO::PARAM_STR);
                        $query->bindParam(':iid', $iid, PDO::PARAM_STR);
                        $query->bindParam(':exam_category', $exam_category, PDO::PARAM_STR);
                        $query->execute();
                    }
                }
                $msg = "Result info updated successfully";
            }

            if(isset($_POST['delete'])) {
                // Delete results
                foreach($rowid as $iid) {
                    $sql = "DELETE FROM tblresult WHERE id=:iid";
                    $query = $dbh->prepare($sql);
                    $query->bindParam(':iid', $iid, PDO::PARAM_STR);
                    $query->execute();
                }
                $msg = "Result deleted successfully";
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
    <title>SMS Admin | Student Result Info</title>
    <link rel="stylesheet" href="css/bootstrap.min.css" media="screen">
    <link rel="stylesheet" href="css/font-awesome.min.css" media="screen">
    <link rel="stylesheet" href="css/main.css" media="screen">
    <script src="js/modernizr/modernizr.min.js"></script>

    <!-- Custom styles -->
    <style>
    .form-group label {
        font-weight: bold;
    }

    .panel-title {
        text-align: center;
    }

    .form-horizontal .form-group {
        margin-bottom: 20px;
    }

    .btn-section {
        margin-top: 20px;
        display: flex;
        justify-content: flex-start;
    }

    .btn-section button {
        margin-right: 10px;
    }
    </style>

    <!-- JavaScript to fetch data via AJAX -->
    <script src="js/jquery/jquery-2.2.4.min.js"></script>
    <script>
    function fetchResults() {
        var examCategory = $('#exam_category').val();
        var studentId = '<?php echo $stid; ?>';

        if (examCategory !== "") {
            $.ajax({
                type: "POST",
                url: "get_results.php", // Separate PHP script to handle result fetching
                data: {
                    'exam_category': examCategory,
                    'student_id': studentId
                },
                success: function(response) {
                    $('#results_container').html(response);
                    // Set hidden field value
                    $('#hidden_exam_category').val(examCategory);
                }
            });
        } else {
            $('#results_container').html('');
        }
    }

    // Client-side validation to ensure all marks are filled in before submitting
    function validateForm() {
        var isValid = true;
        $('input[name="marks[]"]').each(function() {
            if ($(this).val() === '') {
                isValid = false;
                alert('Please fill all the marks fields.');
                return false; // break the loop
            }
        });
        return isValid;
    }

    $(document).ready(function() {
        // Automatically fetch results when the exam category is changed
        $('#exam_category').change(function() {
            fetchResults();
        });

        // Form submission event handler for Update and Delete buttons
        $('form').on('submit', function(e) {
            var action = $(document.activeElement).attr('name'); // Identify which button was clicked

            if (action === 'submit') {
                // Ask for confirmation before updating
                if (!confirm('Are you sure you want to update the result?')) {
                    e.preventDefault();
                }
            } else if (action === 'delete') {
                // Ask for confirmation before deleting
                if (!confirm('Are you sure you want to delete the result?')) {
                    e.preventDefault();
                }
            }

            // Validate form before submitting
            if (!validateForm()) {
                e.preventDefault();
            }
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
                                            <h5>Update the Result Info</h5>
                                        </div>
                                    </div>
                                    <div class="panel-body">
                                        <?php if($msg){ ?>
                                        <div class="alert alert-success left-icon-alert" role="alert">
                                            <strong>Well done!</strong> <?php echo htmlentities($msg); ?>
                                        </div>
                                        <?php } else if($error){ ?>
                                        <div class="alert alert-danger left-icon-alert" role="alert">
                                            <strong>Oh snap!</strong> <?php echo htmlentities($error); ?>
                                        </div>
                                        <?php } ?>

                                        <!-- Exam Category Selection -->
                                        <form class="form-horizontal" method="post">
                                            <div class="form-group">
                                                <label for="exam_category" class="col-sm-2 control-label">Exam
                                                    Category</label>
                                                <div class="col-sm-10">
                                                    <select name="exam_category" id="exam_category" class="form-control"
                                                        required="required">
                                                        <option value="">Select Exam Category</option>
                                                        <?php 
                                                        $sql = "SELECT * FROM tblexamcategories WHERE status = 'Active'";
                                                        $query = $dbh->prepare($sql);
                                                        $query->execute();
                                                        $categories = $query->fetchAll(PDO::FETCH_OBJ);
                                                        if ($query->rowCount() > 0) {
                                                            foreach($categories as $category) { ?>
                                                        <option value="<?php echo htmlentities($category->id); ?>">
                                                            <?php echo htmlentities($category->exam_category) . " - " . date('Y', strtotime($category->exam_date)); ?>
                                                        </option>
                                                        <?php }
                                                        } else {
                                                            echo "<option value=''>No active exam categories available</option>";
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                            </div>

                                            <!-- Hidden input to store the selected exam category -->
                                            <input type="hidden" id="hidden_exam_category" name="exam_category">

                                            <!-- Results Container -->
                                            <div id="results_container">
                                                <!-- AJAX will dynamically load results here -->
                                            </div>

                                            <div class="btn-section">
                                                <button type="submit" name="submit" class="btn btn-primary">Update
                                                    Result</button>
                                                <button type="submit" name="delete" class="btn btn-danger">Delete
                                                    Result</button>
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
    <script src="js/bootstrap/bootstrap.min.js"></script>
    <script src="js/main.js"></script>
</body>

</html>
<?php } ?>