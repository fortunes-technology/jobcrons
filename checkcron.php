<?php
include_once 'dbconfigcron.php';
include_once 'PHPMailer/send_email.php';

$rets = $crud->checkCronSatus();
$message = '';
foreach($rets as $ret) {
    if ($ret['ret'] == 'Okay')
        $message .= "Script ".$ret['id']." is working properly.<br>";
    else if ($ret['ret'] == 'Error')
        $message .= "Script ".$ret['id']." is not working properly.<br>";
}

send_email("devcenterclover@gmail.com", "Converter cron status", $message);
?>