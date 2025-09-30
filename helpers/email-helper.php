<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load Composer dependencies (if using PHPMailer via Composer)
require_once __DIR__ . '/../vendor/autoload.php';

function sendEmail($to, $subject, $body, $from = 'noreply@burolschool.edu.ph') {
  $mail = new PHPMailer(true);

  // 🔍 Enable SMTP debugging
  $mail->SMTPDebug = 2; // Use 3 for even more verbose output
  $mail->Debugoutput = 'error_log'; // Logs to PHP error log

  try {
    // Server settings
    $mail->isSMTP();
    $mail->Host       = 'smtp.yourmailserver.com'; // e.g., smtp.gmail.com
    $mail->SMTPAuth   = true;
    $mail->Username   = 'your-email@example.com';
    $mail->Password   = 'your-email-password';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    // Recipients
    $mail->setFrom($from, 'Burol Elementary School');
    $mail->addAddress($to);

    // Content
    $mail->isHTML(false);
    $mail->Subject = $subject;
    $mail->Body    = $body;
    $mail->Timeout = 10; // seconds
    
    $mail->send();
    return true;
  } catch (Exception $e) {
    error_log("Email could not be sent. Mailer Error: {$mail->ErrorInfo}");
    return false;
  }
}