<?php
namespace Components\Utils;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
class Email
{
     public static function send($to, $data) {

        $mail = new PHPMailer(true);                              // Passing `true` enables exceptions
        try {
            //Server settings
            //$mail->SMTPDebug = 2;                                 // Enable verbose debug output
            $mail->isSMTP();                                      // Set mailer to use SMTP
            $mail->Host = $data['host'];  // Specify main and backup SMTP servers
            $mail->SMTPAuth = true;                               // Enable SMTP authentication
            $mail->Username = $data['user'];                 // SMTP username
            $mail->Password = $data['pass'];                           // SMTP password
            $mail->SMTPSecure = $data['secure'];                            // Enable TLS encryption, `ssl` also accepted
            $mail->Port = $data['port'];                                    // TCP port to connect to

            //Recipients
            $mail->setFrom($data['user'], '大众彩');
            $mail->addAddress($to);               // Name is optional


            //Content
            $mail->isHTML(true);                                  // Set email format to HTML
            $mail->Subject = $data['subject'][1];
            $mail->Body    = $data['content'];

            $mail->send();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}