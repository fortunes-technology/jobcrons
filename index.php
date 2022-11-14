<?php
include_once('dbconfig.php');
include_once('header.php');
?>
    <body class="hold-transition login-page">
        <div class="login-box">
            <div class="login-logo mb-4 d-flex align-items-center justify-content-center">
                <img src="dist/img/cf.svg" height="40" alt="cf Logo" class="brand-image">
                <span class="brand-text font-weight-light ml-2"><strong>Cleanfeed</strong></span>
            </div>
            <!-- /.login-logo -->
            <div class="card">
                <div class="card-body login-card-body">
                    <p class="lead">Sign in</p>
                    <form method="post">
                        <p class="warning"><?php if(isset($authMessage)) {echo $authMessage;}?></p>
                        <div class="input-group mb-3">
                            <input type="email" name="email" class="form-control form-control-lg" placeholder="Enter your email address" required>
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-envelope"></span>
                                </div>
                            </div>
                        </div>
                        <div class="input-group mb-3">
                            <input type="password" name="password" class="form-control form-control-lg" placeholder="Password" required>
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-lock"></span>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <!-- /.col -->
                            <div class="col-12">
                                <button type="submit" name="loginbtn" class="btn btn-primary btn-block btn-lg mt-2 mb-4">Sign In</button>
                            </div>
                            <!-- /.col -->
                        </div>
                    </form>
                    <div class="col-12">
                        <p class="mb-1 text-center">
                            <a href="forgot-password.html">I forgot my password</a>
                        </p>
                    </div>
                </div>
                <!-- /.login-card-body -->
            </div>
        </div>
        <!-- /.login-box -->
        <!-- jQuery -->
        <script src="plugins/jquery/jquery.min.js"></script>
        <!-- Bootstrap 4 -->
        <script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    </body>
</html>

