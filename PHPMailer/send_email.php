<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/phpmailer/src/Exception.php';
require 'vendor/phpmailer/src/PHPMailer.php';
require 'vendor/phpmailer/src/SMTP.php';

function send_email($email, $subject, $message) {   
    //Load composer's autoloader

    $mail = new PHPMailer(true);
    try {
        //Server settings
        $mail->isSMTP();                                     
        $mail->Host = 'smtp.gmail.com';                      
        $mail->SMTPAuth = true;                             
        $mail->Username = 'irvin.nelson22@gmail.com';     
        $mail->Password = 'fwjjztwqwnydmxop';// 'developer@622';
        $mail->SMTPOptions = array(
            'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
            )
        );                         
        $mail->SMTPSecure = 'ssl';                           
        $mail->Port = 465;                                   

        //Send Email
        $mail->setFrom('irvin.nelson22@gmail.com');
        
        //Recipients
        $mail->addAddress($email);              
        $mail->addReplyTo('irvin.nelson22@gmail.com');
        
        //Content
        $mail->isHTML(true);                                  
        $mail->Subject = $subject;
        $mail->Body    = $message;
        
        $from = 'irvin.nelson22@gmail.com';
        $headers = "MIME-Version: 1.0" . "\r\n"; 
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n"; 
        // Create email headers
        $headers .= 'From: '.$from."\r\n".
            'Reply-To: '.$from."\r\n" .
            'X-Mailer: PHP/' . phpversion();
        $mail->header = $headers;

        $mail->send();
		
        echo 'Message has been sent';
    } catch (Exception $e) {
	    echo 'Message could not be sent. Mailer Error: '.$mail->ErrorInfo;
    }
}


