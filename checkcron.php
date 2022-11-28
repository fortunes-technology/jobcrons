<?php
include_once 'dbconfigcron.php';
include_once 'PHPMailer/send_email.php';

$rets = $crud->checkCronSatus();
$message = '';
date_default_timezone_set('UTC');
foreach($rets as $ret) {
    if (date("Y-m-d") > date("Y-m-d", strtotime($ret['updated_at'])))
        $message .= "Script ".$ret['id']." is not working properly.<br>";
    else
        $message .= "Script ".$ret['id']." is working properly.<br>";
}
send_email("devcenterclover@gmail.com", "Converter cron status", $message);
?>