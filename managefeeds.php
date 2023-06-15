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
                        <a href="index.php" class="nav-link"><i class="fas fa-plus-circle"></i> Add feeds to map</a>
                    </li>                    
                    <li class="nav-item">
                        <a href="filefeed.php" class="nav-link"><i class="fas fa-plus-circle"></i> Add file feed</a>
                    </li>
                    <li class="nav-item">
                        <a href="managefeeds.php" class="nav-link  active"><i class="fas fa-pen"></i> Manage Feeds</a>
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
                            <?php if ($_SESSION['role'] == 1) { ?>
                                <li class="nav-item">
                                    <a href="user.php" class="nav-link">
                                        <i class="nav-icon fas fa-user"></i>
                                        <p>
                                            Users
                                        </p>
                                    </a>
                                </li>
                            <?php } ?>
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
            <!-- Content Wrapper. Contains page content -->
            <div class="content-wrapper">
                <!-- Main content -->
                <div class="content">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card mt-3">

                                    <div class="card-body padding-body">
                                        <div class="d-flex align-items-center mt-2 ml-4 mb-2 mr-4">
                                            <!-- <button type="button" class="btn btn-default btn-sm checkbox-toggle mr-1"><i class="far fa-square"></i>
                                            </button>
                                            <button type="button" class="btn btn-default btn-sm mr-1"><i class="far fa-trash-alt"></i></button> -->
                                            <div class="dropdown">
                                                <button class="btn btn-default btn-sm mr-2 dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fas fa-filter"></i> Filters
                                                </button>
                                                <form method="get">
                                                    <div class="dropdown-menu dropdown-filters" aria-labelledby="dropdownMenuButton">
                                                        <div class="row">
                                                            <div class="col-md-12"> <label class="mb-1"><small>Date, from:</small></label></div>
                                                            <div class="col-md-12">
                                                                <div class="form-group">
                                                                    <input type="date"  class="form-control" type="text" name="date_from" placeholder="2020-12-02" required>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-12"> <label class="mb-1"><small>To:</small></label></div>
                                                            <div class="col-md-12">
                                                                <div class="form-group">
                                                                    <input type="date"  class="form-control" type="text" name="date_to" placeholder="2020-12-02" required>
                                                                </div>
                                                            </div>
                                                            <button class="btn btn-default btn-block mb-2" type="button">
                                                            Remove filters
                                                            </button>
                                                            <button class="btn btn-primary btn-block" type="submit">
                                                            Apply
                                                            </button>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                            <div class="ml-auto">
                                                <!-- 1-50/200
                                                <div class="btn-group ml-2">
                                                    <button type="button" class="btn btn-default btn-sm"><i class="fas fa-chevron-left"></i></button>
                                                    <button type="button" class="btn btn-default btn-sm"><i class="fas fa-chevron-right"></i></button>
                                                </div> -->
                                                <!-- /.btn-group -->
                                            </div>
                                            <div class="dropdown">
                                                <button class="btn btn-default btn-sm mr-2 dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fas fa-file-csv"></i> CSV
                                                </button>
                                                <div class="dropdown-menu dropdown-menu-right dropdown-csv" aria-labelledby="dropdownMenuButton">
                                                    <div class="row">
                                                        <div class="col-md-12" style="padding-bottom: 5px"> 
                                                            <div class="input-group mb-0">
                                                                <span class="form-control text-blue">EMPTY</span>
                                                                <div class="input-group-append">
                                                                    <button class="btn btn-default" type="button"  id="downEmptyFeeds" type="button"> <i class="fas fa-download mr-1"></i>
                                                                    </button>
                                                                </div>
                                                            </div> 
                                                        </div>
                                                        <div class="col-md-12" style="padding-bottom: 5px"> 
                                                            <div class="input-group mb-0">
                                                                <span class="form-control text-green">READY</span>
                                                                <div class="input-group-append">
                                                                    <button class="btn btn-default" type="button"  id="downReadyFeeds" type="button"> <i class="fas fa-download mr-1"></i>
                                                                    </button>
                                                                </div>
                                                            </div> 
                                                        </div>
                                                        <div class="col-md-12" style="padding-bottom: 5px"> 
                                                            <div class="input-group mb-0">
                                                                <span class="form-control text-red">ERROR</span>
                                                                <div class="input-group-append">
                                                                    <button class="btn btn-default" type="button"  id="downErrorFeeds" type="button"> <i class="fas fa-download mr-1"></i>
                                                                    </button>
                                                                </div>
                                                            </div> 
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- /.float-right -->
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table table-striped" id="feedinfo">
                                                <thead>
                                                    <tr>
                                                        <th>Name</th>
                                                        <th>Input Url</th>
                                                        <th>Output Url</th>
                                                        <th>AI Generate</th>
                                                        <th>CreateDate</th>
                                                        <th>UpdateDate</th>
                                                        <th>Status</th>
                                                        <!-- <th>LastTimeConverted</th> -->
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    if(!empty($_GET['date_from']) && !empty($_GET['date_to'])) {
                                                        $date_from = $_GET['date_from']." 00:00:00";
                                                        $date_to = $_GET['date_to']." 00:00:00";
                                                        $crud->dataview("SELECT * FROM feedinfo WHERE createdate >= '$date_from' AND createdate <= '$date_to'", $s3);
                                                    }
                                                    else {
                                                        $crud->dataview("SELECT * FROM feedinfo", $s3);
                                                    }
                                                    ?>    
                                                </tbody>                                                
                                            </table>
                                            <!-- /.table -->
                                            <div class="p-5 text-center">
                                                <!-- <p class="lead"><i class="nav-icon fas fa-search"></i> <br>No results were found</p> -->
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

            <!-- Remove Modal -->
            <div class="modal fade" id="removeModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Remove</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        Do you really want to remove this XML information?
                    </div>
                    <div class="modal-footer">
                        <input type="hidden" id="removeId">
                        <input type="hidden" id="removeName">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary removeItem">Confirm</button>
                    </div>
                    </div>
                </div>
            </div>

            <!-- Running Modal -->
            <div class="modal fade" id="runningModal" tabindex="-1" role="dialog" aria-labelledby="runningModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="runningModalLabel">Generate</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        Do you want to generate XML right now?
                    </div>
                    <div class="modal-footer">
                        <input type="hidden" id="runningId">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary runningItem">Confirm</button>
                    </div>
                    </div>
                </div>
            </div>

            <!-- Comfirm Modal -->
            <div class="modal fade" id="confirmModal" tabindex="-1" role="dialog" aria-labelledby="confirmModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="confirmModalLabel">Confirm</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        It will be generated soon. Please wait for a while.
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary confirmItem">Confirm</button>
                    </div>
                    </div>
                </div>
            </div>

            <script type="text/javascript">
            </script>
<?php
include_once('footer.php');
?>

