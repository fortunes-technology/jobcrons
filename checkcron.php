<?php
include_once 'dbconfigcron.php';
include_once 'PHPMailer\send_email.php';

$status = $crud->checkCronSatus();
if($status == "Finished" )
    send_email("devcenterclover@gmail.com", "Finished", "Cron Script is working properly.");
else
    send_email("devcenterclover@gmail.com", "Running", "Cron Script is not working properly.");
?>