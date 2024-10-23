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
    <title>School Result Management System</title>
    <link rel="stylesheet" href="css/bootstrap.min.css" media="screen">
    <link rel="stylesheet" href="css/font-awesome.min.css" media="screen">
    <link rel="stylesheet" href="css/animate-css/animate.min.css" media="screen">
    <link rel="stylesheet" href="css/icheck/skins/flat/blue.css">
    <link rel="stylesheet" href="css/main.css" media="screen">
    <script src="js/modernizr/modernizr.min.js"></script>

    <!-- Custom CSS for header and layout -->
    <style>
    .header {
        background-color: white;
        color: black;
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

    .centered-container {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 80vh;
        flex-direction: column;
    }

    .panel {
        width: 100%;
        max-width: 500px;
    }

    @media (max-width: 768px) {
        .panel {
            max-width: 100%;
        }
    }
    </style>
</head>

<body class="">
    <div class="main-wrapper">
        <!-- ========== HEADER WITH LARGE LOGO AND TITLE ========== -->
        <div class="header">
            <img src="logo.jpeg" alt="School Logo">
            <h1>SYSTEMS SCHOOL</h1>
            <img src="logo.jpeg" alt="School Logo">
        </div>

        <!-- Main container with flexbox to center the content -->
        <div class="centered-container">
            <div class="main-content">
                <h2 class="subheader">Student Result Management System</h2>

                <!-- Search Form for Results -->
                <section class="section">
                    <div class="row mt-40">
                        <div class="col-md-12">
                            <div class="panel">
                                <div class="panel-heading">
                                    <div class="panel-title text-center">
                                        <h4>Search Results</h4>
                                    </div>
                                </div>
                                <div class="panel-body p-20">
                                    <!-- Search Form -->
                                    <form action="result.php" method="post">
                                        <!-- Roll ID Input -->
                                        <div class="form-group">
                                            <label for="rollid">Enter your Roll Id</label>
                                            <input type="text" class="form-control" id="rollid"
                                                placeholder="Enter Your Roll Id" autocomplete="off" name="rollid"
                                                required>
                                        </div>

                                        <!-- Class Selection -->
                                        <div class="form-group">
                                            <label for="default" class="control-label">Class</label>
                                            <select name="class" class="form-control" id="default" required="required">
                                                <option value="">Select Class</option>
                                                <?php 
                                                $sql = "SELECT * from tblclasses";
                                                $query = $dbh->prepare($sql);
                                                $query->execute();
                                                $results=$query->fetchAll(PDO::FETCH_OBJ);
                                                if($query->rowCount() > 0) {
                                                    foreach($results as $result) {   ?>
                                                <option value="<?php echo htmlentities($result->id); ?>">
                                                    <?php echo htmlentities($result->ClassName); ?>&nbsp;
                                                    Section-<?php echo htmlentities($result->Section); ?>
                                                </option>
                                                <?php }} ?>
                                            </select>
                                        </div>

                                        <!-- Exam Category Selection -->
                                        <div class="form-group">
                                            <label for="exam_category" class="control-label">Exam Category</label>
                                            <select name="exam_category" id="exam_category" class="form-control"
                                                required="required">
                                                <option value="">Select Exam Category</option>
                                                <option value="First Exam">First Exam</option>
                                                <option value="Second Exam">Second Exam</option>
                                                <option value="Midterm">Midterm</option>
                                                <option value="Final Exam">Final Exam</option>
                                            </select>
                                        </div>

                                        <!-- Search Button -->
                                        <div class="form-group mt-20">
                                            <button type="submit" class="btn btn-success btn-labeled pull-right">Search
                                                <span class="btn-label btn-label-right"><i
                                                        class="fa fa-check"></i></span>
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <!-- JS -->
    <script src="js/jquery/jquery-2.2.4.min.js"></script>
    <script src="js/bootstrap/bootstrap.min.js"></script>
    <script src="js/main.js"></script>
</body>

</html>