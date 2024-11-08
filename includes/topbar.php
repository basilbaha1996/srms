<?php
session_start();
if (!isset($_SESSION['alogin']) || $_SESSION['alogin'] == '') {
    header("Location: index.php");
    exit();
}
?>

<nav class="navbar top-navbar bg-white box-shadow">
    <div class="container-fluid">
        <div class="row">
            <div class="navbar-header no-padding">
                <a class="navbar-brand" href="dashboard.php">
                    <img src="logo.jpeg" alt="School Logo"
                        style="height: 45px; width: auto; display: inline-block; vertical-align: middle; margin-right: 10px;">
                    <span style="display: inline-block; vertical-align: middle;">
                        Student Result Management System || SYSTEMS SCHOOL
                    </span>
                </a>
                <span class="small-nav-handle hidden-sm hidden-xs"><i class="fa fa-outdent"></i></span>
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse"
                    data-target="#navbar-collapse-1" aria-expanded="false">
                    <span class="sr-only">Toggle navigation</span>
                    <i class="fa fa-ellipsis-v"></i>
                </button>
                <button type="button" class="navbar-toggle mobile-nav-toggle">
                    <i class="fa fa-bars"></i>
                </button>
            </div>
            <!-- /.navbar-header -->

            <div class="collapse navbar-collapse" id="navbar-collapse-1">
                <ul class="nav navbar-nav" data-dropdown-in="fadeIn" data-dropdown-out="fadeOut">
                    <li class="hidden-sm hidden-xs"><a href="#" class="full-screen-handle"><i
                                class="fa fa-arrows-alt"></i></a></li>
                </ul>

                <ul class="nav navbar-nav navbar-right" data-dropdown-in="fadeIn" data-dropdown-out="fadeOut">
                    <li><a href="logout.php" class="color-danger text-center"><i class="fa fa-sign-out"></i> Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>

<!-- Custom styles -->
<style>
.navbar {
    min-height: 80px;
    /* Set minimum height to fit the logo */
}

.navbar-brand img {
    height: 70px;
    /* Ensure the logo height */
}

.navbar-brand {
    padding: 5px 15px;
    /* Adjust padding for vertical alignment */
}
</style>