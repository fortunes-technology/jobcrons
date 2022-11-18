<?php
include_once 'dbconfigcron.php';
include_once 'PHPMailer\send_email.php';

$count = $crud->checkCronSatus('Running');
if($count > 0) {
    send_email("samsamuel20101@gmail.com", "status", "Running");
    exit();
}
?>