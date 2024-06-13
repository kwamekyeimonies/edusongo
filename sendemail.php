<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

function sendEventNotification($to, $body) {
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';  // Set the SMTP server to send through
        $mail->SMTPAuth = true;
        $mail->Username = 'your-email@example.com';  // SMTP username 
        $mail->Password = 'Morvic++';  // SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Recipients
        $mail->setFrom('your-email@example.com', 'Event Manager');
        $mail->addAddress($to);

        // Content
        $mail->isHTML(true);
        $mail->Subject = '.';
        $mail->Body = $body;

        $mail->send();
        return "Email notification sent successfully.";
    } catch (Exception $e) {
        error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        return "Failed to send email notification. Error: {$mail->ErrorInfo}";
    }
}
?>

