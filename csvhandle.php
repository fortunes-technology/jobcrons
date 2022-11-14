<?php
    include_once('dbconfig.php');
    include_once('header.php');
?>
    <body class="hold-transition sidebar-mini sidebar-collapse layout-fixed">
        <div class="wrapper">
        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand navbar-white navbar-light sticky-top">
            <!-- Left navbar links -->
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
                </li>
                <li class="nav-item">
                    <a href="addurl.php" class="nav-link active"><i class="fas fa-plus-circle"></i> Add feeds to map</a>
                </li>                
                <li class="nav-item">
                        <a href="filefeed.php" class="nav-link"><i class="fas fa-plus-circle"></i> Add file feed</a>
                    </li>
                <li class="nav-item">
                    <a href="managefeeds.php" class="nav-link"><i class="fas fa-pen"></i> Manage Feeds</a>
                </li>
            </ul>
        </nav>
        <!-- /.navbar -->
        <!-- Main Sidebar Container -->
        <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <!-- Brand Logo -->
            <a href="index.php" class="brand-link">
            <img src="dist/img/cf.svg" alt="l4c Logo" class="brand-image"
                style="opacity: .8">
            <span class="brand-text font-weight-light"><strong>Cleanfeed</strong></span>
            </a>
            <!-- Sidebar -->
            <div class="sidebar">
                <!-- Sidebar user panel (optional) -->
                <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                    <div class="image">
                        <img src="dist/img/user2-160x160.jpg" class="img-circle elevation-2" alt="User Image">
                    </div>
                    <div class="info">
                        <span class="d-block mb-0"><?php echo $_SESSION['username']?></span>
                    </div>
                </div>
                <!-- Sidebar Menu -->
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                        <!-- Add icons to the links using the .nav-icon class
                            with font-awesome or any other icon font library -->
                        <li class="nav-item">
                            <a href="csvhandle.php" class="nav-link">
                                <i class="nav-icon fas fa-file"></i>
                                <p>
                                    Import CSV
                                </p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="logout.php" class="nav-link">
                                <i class="nav-icon fas fa-power-off"></i>
                                <p>
                                    Disconnect
                                </p>
                            </a>
                        </li>                        
                    </ul>
                </nav>
                <!-- /.sidebar-menu -->
            </div>
            <!-- /.sidebar -->
        </aside>
        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <!-- Main content -->
            <div class="content">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card mt-3">

                                <div class="card-header st-head">
                                    <div style="width: 50%; margin: auto; margin-top: 50px;">
                                        <form class="form-horizontal" action="parsexml.php" method="post"  enctype="multipart/form-data">
                                            <div class="form-group">
                                                <div class="col-xs-6">
                                                    <input type="hidden" name="file_importing" value="file_importing">
                                                    <input style="height: 45px;" type="file" class="form-control" name="csvImport" id="csvImport" accept=".csv, .xls, .xlsx">
                                                </div>
                                            </div>
                                            <div class="form-group" style="text-align: center;">
                                                <label for="login" class="control-label col-xs-2"></label>
                                                <button type="submit" class="btn btn-primary">Upload</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>


                                <div class="card-body p-0">
                                    <div class="d-flex align-items-center mt-2 ml-4 mb-2 mr-4">
                                        <p class="mb-0" id="tagNumber"></p>
                                    </div>
                                    <div>
                                        <!-- /.table -->
                                        <div class="p-5 text-center">
                                        <p class="lead"><i class="nav-icon fas fa-link"></i> <br>Attach file please</p>
                                        </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- /.row -->
                    </div>
                    <!-- /.container-fluid -->
                </div>
                <!-- /.content -->
            </div>
            <!-- /.content-wrapper -->
            <!-- Main Footer -->

<?php
include_once('footer.php');
?>

