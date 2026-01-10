<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

$config = require 'smtp_config.php';

function sendEmail(
    string $email,
    string $name,
    string $subject,
    string $body
): bool {

    global $config;

    $mail = new PHPMailer(true);

    // try {
        // SMTP settings
        $mail->isSMTP();
        $mail->Host       = $config['host'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $config['username'];
        $mail->Password   = $config['password'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = $config['port'];

        // Email details
        $mail->setFrom($config['from_email'], $config['from_name']);
        $mail->addAddress($email, $name);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        return $mail->send();
    // } catch (Exception $e) {
    //     error_log("Email Error: {$mail->ErrorInfo}");
    //     return false;
    // }
}
