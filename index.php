<?php
session_start();
error_reporting(0);
include('includes/config.php');

if ($_SESSION['alogin'] != '') {
    $_SESSION['alogin'] = '';
}

if (isset($_POST['login'])) {
    $uname = $_POST['username'];
    $password = md5($_POST['password']);
    
    // Query to check username and password in admin table
    $sql = "SELECT UserName, Password FROM admin WHERE UserName=:uname and Password=:password";
    $query = $dbh->prepare($sql);
    $query->bindParam(':uname', $uname, PDO::PARAM_STR);
    $query->bindParam(':password', $password, PDO::PARAM_STR);
    $query->execute();
    $results = $query->fetchAll(PDO::FETCH_OBJ);
    
    if ($query->rowCount() > 0) {
        $_SESSION['alogin'] = $_POST['username'];
        echo "<script type='text/javascript'> document.location = 'dashboard.php'; </script>";
    } else {
        echo "<script>alert('Invalid Details');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Login</title>
    <link rel="stylesheet" href="css/bootstrap.min.css" media="screen">
    <link rel="stylesheet" href="css/font-awesome.min.css" media="screen">
    <link rel="stylesheet" href="css/animate-css/animate.min.css" media="screen">
    <link rel="stylesheet" href="css/prism/prism.css" media="screen">
    <link rel="stylesheet" href="css/main.css" media="screen">
    <script src="js/modernizr/modernizr.min.js"></script>

    <!-- Custom CSS for centering and header style -->
    <style>
    .centered-container {
        min-height: 80vh;
        display: flex;
        justify-content: center;
        align-items: center;
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

    /* Header section styling */
    .header {
        background-color: white;
        /* Bootstrap Primary Blue */
        color: white;
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

    /* Centered content styling */
    .main-content {
        text-align: center;
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

                <!-- Admin Login Section -->
                <section class="section">
                    <div class="row mt-40">
                        <div class="col-md-12">
                            <div class="panel">
                                <div class="panel-heading">
                                    <div class="panel-title text-center">
                                        <h4>Admin Login</h4>
                                    </div>
                                </div>
                                <div class="panel-body p-20">
                                    <form class="form-horizontal" method="post">
                                        <div class="form-group">
                                            <label for="inputEmail3" class="col-sm-2 control-label">Username</label>
                                            <div class="col-sm-10">
                                                <input type="text" name="username" class="form-control" id="inputEmail3"
                                                    placeholder="Username" required>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="inputPassword3" class="col-sm-2 control-label">Password</label>
                                            <div class="col-sm-10">
                                                <input type="password" name="password" class="form-control"
                                                    id="inputPassword3" placeholder="Password" required>
                                            </div>
                                        </div>

                                        <div class="form-group mt-20">
                                            <div class="col-sm-offset-2 col-sm-10">
                                                <button type="submit" name="login"
                                                    class="btn btn-success btn-labeled pull-right">Sign in
                                                    <span class="btn-label btn-label-right"><i
                                                            class="fa fa-check"></i></span></button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Search for Result Section -->
                    <div class="row mt-40">
                        <div class="col-md-12">
                            <div class="panel">
                                <div class="panel-heading">
                                    <div class="panel-title text-center">
                                        <h4>Search for a result</h4>
                                    </div>
                                </div>
                                <div class="panel-body p-20">
                                    <form class="form-horizontal" method="post">
                                        <div class="form-group">
                                            <div class="col-sm-12 text-center">
                                                <a href="find-result.php" class="btn btn-primary">Click here</a>
                                            </div>
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

    <!-- ========== COMMON JS FILES ========== -->
    <script src="js/jquery/jquery-2.2.4.min.js"></script>
    <script src="js/jquery-ui/jquery-ui.min.js"></script>
    <script src="js/bootstrap/bootstrap.min.js"></script>
    <script src="js/pace/pace.min.js"></script>
    <script src="js/lobipanel/lobipanel.min.js"></script>
    <script src="js/iscroll/iscroll.js"></script>
    <script src="js/main.js"></script>

</body>

</html>