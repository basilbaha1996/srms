<?php
session_start();
error_reporting(E_ALL); // Enable error reporting
ini_set('display_errors', 1); // Display errors for debugging

include('includes/config.php');
if(strlen($_SESSION['alogin'])=="")
{   
    header("Location: index.php"); 
}
else {
    if(isset($_POST['submit'])) {
        $username = $_POST['username'];
        $password = md5($_POST['password']);  // Encrypt the password using md5
        
        // Ensure that the inputs are not empty
        if (!empty($username) && !empty($password)) {
            // Insert the new admin into the database (without the Role column)
            $sql = "INSERT INTO admin(UserName, Password) VALUES(:username, :password)";
            $query = $dbh->prepare($sql);
            $query->bindParam(':username', $username, PDO::PARAM_STR);
            $query->bindParam(':password', $password, PDO::PARAM_STR);
            
            // Check if query executed successfully
            if ($query->execute()) {
                $msg = "New Admin added successfully";
            } else {
                $error = "Something went wrong. Please try again.";
            }
        } else {
            $error = "Username or password cannot be empty.";
        }
    }
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Add New Admin</title>
    <link rel="stylesheet" href="css/bootstrap.min.css" media="screen">
    <link rel="stylesheet" href="css/font-awesome.min.css" media="screen">
    <link rel="stylesheet" href="css/main.css" media="screen">
    <style>
    .centered-logo {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        z-index: -1;
    }

    .centered-logo img {
        width: 400px;
        height: auto;
        opacity: 0.2;
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
                                <h2 class="title">Add New Admin</h2>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="panel">
                                    <div class="panel-body">
                                        <!-- Display success or error messages -->
                                        <?php if(isset($msg)){ ?>
                                        <div class="alert alert-success">
                                            <strong>Well done!</strong> <?php echo htmlentities($msg); ?>
                                        </div>
                                        <?php } else if(isset($error)){ ?>
                                        <div class="alert alert-danger">
                                            <strong>Oh snap!</strong> <?php echo htmlentities($error); ?>
                                        </div>
                                        <?php } ?>

                                        <form class="form-horizontal" method="post">
                                            <div class="form-group">
                                                <label for="username" class="col-sm-2 control-label">Username</label>
                                                <div class="col-sm-10">
                                                    <input type="text" name="username" class="form-control"
                                                        required="required" autocomplete="off">
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label for="password" class="col-sm-2 control-label">Password</label>
                                                <div class="col-sm-10">
                                                    <input type="password" name="password" class="form-control"
                                                        required="required" autocomplete="off">
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <div class="col-sm-offset-2 col-sm-10">
                                                    <button type="submit" name="submit" class="btn btn-primary">Add
                                                        Admin</button>
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

    <div class="centered-logo">
        <img src="logo.jpeg" alt="Centered Logo">
    </div>

    <script src="js/jquery/jquery-2.2.4.min.js"></script>
    <script src="js/bootstrap/bootstrap.min.js"></script>
</body>

</html>
<?php } ?>