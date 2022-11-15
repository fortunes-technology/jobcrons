<?php
    include_once('dbconfig.php');
    include_once('header.php');
?>
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
                            <a href="logout.php" class="nav-link">
                                <i class="nav-icon fas fa-power-off"></i>
                                <p>
                                    Disconnect
                                </p>
                            </a>
                        </li>
                    </ul>
                </nav>
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                        <li class="nav-item">
                            <a href="user.php" class="nav-link">
                                <i class="nav-icon fas fa-user"></i>
                                <p>
                                    Users
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


                                <div class="card-header st-head" style="top:0px">
                                </div>


                                <div class="card-body p-0">
                                    <div class="d-flex align-items-center mt-2 ml-4 mb-2 mr-4">
                                        <p class="mb-0" id="tagNumber"></p>
                                    </div>
                                    <div class="relative-div position-relative">
                                        <div class="for-check">
                                            <input type="checkbox" name="checkAllUser" id="checkAllUser">
                                        </div>
                                        <div class="for-check">
                                            <a href="#" data-toggle='modal' id='removeUserHref'><i class="fas fa-trash"></i></a>
                                        </div>
                                        <div class="for-check">
                                            <a href="#" data-toggle='modal' data-target="#createUser"><i class="fas fa-plus-circle" style="margin-right: 8px;"></i>Add User</a>
                                        </div>
                                    </div>
                                    <div class="table-container">
                                        <div class="table-responsive">
                                            <table style="width: 100%;" class="table table-striped" id="user_all">
                                                <thead>
                                                    <tr>
                                                        <th></th>
                                                        <th>No</th>
                                                        <th>Username</th>
                                                        <th>Email</th>
                                                        <th>Created At</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php $crud->getAllUser("SELECT * FROM users");?>
                                                </tbody>                                                
                                            </table>
                                        </div>
                                        <div class="p-5 text-center">
                                        
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

        <!-- Create User Modal -->
        <div class="modal fade" id="createUser" tabindex="-1" role="dialog" aria-labelledby="createUserLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createUserLabel">Create User</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form>
                        <input type="hidden" id="userId" value="" />
                        <div class="form-group">
                            <label for="userName">User name</label>
                            <input type="text" class="form-control" id="userName" placeholder="Enter user name" required>
                        </div>
                        <div class="form-group">
                            <label for="userEmail">Email</label>
                            <input type="email" class="form-control" id="userEmail" placeholder="Enter email" required>
                        </div>
                        <div class="form-group">
                            <label for="userPwd">Password</label>
                            <input type="password" class="form-control" id="userPwd" placeholder="Enter password" required>
                        </div>
                        <div class="form-group">
                            <label for="userPwdCon">Confirm Password</label>
                            <input type="password" class="form-control" id="userPwdCon" placeholder="Enter password" required>
                        </div>
                        <div class="form-group">
                            <label for="userRole">Role</label>
                            <select class="form-control" id="userRole">
                                <option value="2">User</option>
                                <option value="1">admin</option>
                            </select>
                        </div>
                    </form>
                    
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary createUser">Confirm</button>
                </div>
                </div>
            </div>
        </div>

        <!-- Remove Modal -->
        <div class="modal fade" id="removeUser" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Remove User</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Do you really want to remove these Users?
                </div>
                <div class="modal-footer">
                    <input type="hidden" id="removeUserId">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary removeUser">Confirm</button>
                </div>
                </div>
            </div>
        </div>

        <!-- Main Footer -->
<?php
include_once('footer.php');
?>

