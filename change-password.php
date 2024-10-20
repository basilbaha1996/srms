<?php
session_start();
error_reporting(0);
include('includes/config.php');
if(strlen($_SESSION['alogin']) == "")
{
    header("Location: index.php");
}
else {
    if (isset($_POST['submit'])) {
        // Get current password, new password, and new username
        $password = md5($_POST['password']);
        $newpassword = md5($_POST['newpassword']);
        $newusername = $_POST['newusername'];
        $currentusername = $_SESSION['alogin']; // Current username from session
        
        // Verify current password and username
        $sql = "SELECT Password FROM admin WHERE UserName=:username AND Password=:password";
        $query = $dbh->prepare($sql);
        $query->bindParam(':username', $currentusername, PDO::PARAM_STR);
        $query->bindParam(':password', $password, PDO::PARAM_STR);
        $query->execute();
        $results = $query->fetchAll(PDO::FETCH_OBJ);
        
        // If the current password is correct, update the username and password
        if ($query->rowCount() > 0) {
            // Update both username and password
            $con = "UPDATE admin SET UserName=:newusername, Password=:newpassword WHERE UserName=:currentusername";
            $chngpwd1 = $dbh->prepare($con);
            $chngpwd1->bindParam(':newusername', $newusername, PDO::PARAM_STR);
            $chngpwd1->bindParam(':newpassword', $newpassword, PDO::PARAM_STR);
            $chngpwd1->bindParam(':currentusername', $currentusername, PDO::PARAM_STR);
            $chngpwd1->execute();
            
            // Update session username to the new username
            $_SESSION['alogin'] = $newusername;
            
            $msg = "Your username and password have been successfully updated.";
        } else {
            $error = "Your current password is wrong.";
        }
    }
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Change Username and Password</title>
    <link rel="stylesheet" href="css/bootstrap.css" media="screen">
    <link rel="stylesheet" href="css/font-awesome.min.css" media="screen">
    <link rel="stylesheet" href="css/animate-css/animate.min.css" media="screen">
    <link rel="stylesheet" href="css/lobipanel/lobipanel.min.css" media="screen">
    <link rel="stylesheet" href="css/prism/prism.css" media="screen">
    <link rel="stylesheet" href="css/main.css" media="screen">
    <script src="js/modernizr/modernizr.min.js"></script>
    <script type="text/javascript">
    function valid() {
        if (document.chngpwd.newpassword.value != document.chngpwd.confirmpassword.value) {
            alert("New Password and Confirm Password Field do not match!");
            document.chngpwd.confirmpassword.focus();
            return false;
        }
        return true;
    }
    </script>
    <style>
    .errorWrap {
        padding: 10px;
        margin: 0 0 20px 0;
        background: #fff;
        border-left: 4px solid #dd3d36;
        box-shadow: 0 1px 1px 0 rgba(0, 0, 0, .1);
    }

    .succWrap {
        padding: 10px;
        margin: 0 0 20px 0;
        background: #fff;
        border-left: 4px solid #5cb85c;
        box-shadow: 0 1px 1px 0 rgba(0, 0, 0, .1);
    }
    </style>
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
                                <h2 class="title">Admin Change Username and Password</h2>
                            </div>
                        </div>
                        <div class="row breadcrumb-div">
                            <div class="col-md-6">
                                <ul class="breadcrumb">
                                    <li><a href="dashboard.php"><i class="fa fa-home"></i> Home</a></li>
                                    <li class="active">Admin Change Username and Password</li>
                                </ul>
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
                                                <h5>Admin Change Username and Password</h5>
                                            </div>
                                        </div>
                                        <?php if($msg){ ?>
                                        <div class="alert alert-success left-icon-alert" role="alert">
                                            <strong>Well done!</strong> <?php echo htmlentities($msg); ?>
                                        </div>
                                        <?php } else if($error){ ?>
                                        <div class="alert alert-danger left-icon-alert" role="alert">
                                            <strong>Oh snap!</strong> <?php echo htmlentities($error); ?>
                                        </div>
                                        <?php } ?>
                                        <div class="panel-body">
                                            <form name="chngpwd" method="post" onSubmit="return valid();">
                                                <!-- Current Password -->
                                                <div class="form-group has-success">
                                                    <label for="success" class="control-label">Current Password</label>
                                                    <div class="">
                                                        <input type="password" name="password" class="form-control"
                                                            required="required" id="success">
                                                    </div>
                                                </div>

                                                <!-- New Username -->
                                                <div class="form-group has-success">
                                                    <label for="success" class="control-label">New Username</label>
                                                    <div class="">
                                                        <input type="text" name="newusername" class="form-control"
                                                            required="required" id="success">
                                                    </div>
                                                </div>

                                                <!-- New Password -->
                                                <div class="form-group has-success">
                                                    <label for="success" class="control-label">New Password</label>
                                                    <div class="">
                                                        <input type="password" name="newpassword" required="required"
                                                            class="form-control" id="success">
                                                    </div>
                                                </div>

                                                <!-- Confirm Password -->
                                                <div class="form-group has-success">
                                                    <label for="success" class="control-label">Confirm Password</label>
                                                    <div class="">
                                                        <input type="password" name="confirmpassword"
                                                            class="form-control" required="required" id="success">
                                                    </div>
                                                </div>

                                                <!-- Submit Button -->
                                                <div class="form-group has-success">
                                                    <div class="">
                                                        <button type="submit" name="submit"
                                                            class="btn btn-success btn-labeled">
                                                            Change <span class="btn-label btn-label-right"><i
                                                                    class="fa fa-check"></i></span>
                                                        </button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <!-- /.col-md-8 col-md-offset-2 -->
                            </div>
                            <!-- /.row -->
                        </div>
                        <!-- /.container-fluid -->
                    </section>
                    <!-- /.section -->
                </div>
                <!-- /.main-page -->
            </div>
            <!-- /.content-container -->
        </div>
        <!-- /.content-wrapper -->
    </div>
    <!-- /.main-wrapper -->

    <!-- ========== COMMON JS FILES ========== -->
    <script src="js/jquery/jquery-2.2.4.min.js"></script>
    <script src="js/jquery-ui/jquery-ui.min.js"></script>
    <script src="js/bootstrap/bootstrap.min.js"></script>
    <script src="js/pace/pace.min.js"></script>
    <script src="js/lobipanel/lobipanel.min.js"></script>
    <script src="js/iscroll/iscroll.js"></script>

    <!-- ========== PAGE JS FILES ========== -->
    <script src="js/prism/prism.js"></script>

    <!-- ========== THEME JS ========== -->
    <script src="js/main.js"></script>
</body>

</html>
<?php } ?>