<?php
namespace App\Lib;
use App\model\SystemSettings;
use PHPMailer\PHPMailer\PHPMailer;

class Mail{

    private static $mail;

    static function send($content): bool{
        
        self::$mail->setFrom($content['senderEmail'], $content['senderName']);
        self::$mail->addAddress($content['recieverEmail'], $content['recieverName']);
        self::$mail->Subject = $content['emailSubject'];
        self::$mail->Body = $content['emailBody'];

        if (self::$mail->send()) {
            return  true;
        } else {
            return  false;
        }

    }

    public static function setConfig(SystemSettings $settings){
        self::$mail = new PHPMailer;
        self::$mail->isSMTP();  // Set mailer to use SMTP
//        self::$mail->Host = 'smtp.gmail.com';  // Specify the SMTP server
        self::$mail->Host = $settings->getMailHost();  // Specify the SMTP server
        self::$mail->SMTPAuth = true;  // Enable SMTP authentication
//        self::$mail->Username = 'johnzunigacastillo@gmail.com';  // SMTP username
        self::$mail->Username = $settings->getMailUsername();  // SMTP username
//        self::$mail->Password = 'yaqxmbzfktfkdlcg';  // SMTP password
        self::$mail->Password = $settings->getMailPassword();  // SMTP password
        self::$mail->SMTPSecure = 'tls';  // Enable encryption, 'ssl' also accepted
        self::$mail->Port = 587;  // TCP port to connect to
    }
}