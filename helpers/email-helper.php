<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load Composer dependencies (if using PHPMailer via Composer)
require_once __DIR__ . '/../vendor/autoload.php';

function sendEmail($to, $subject, $body, $from = 'noreply@besims.site') {
  $mail = new PHPMailer(true);

  // 🔍 Enable SMTP debugging
  $mail->SMTPDebug = 2; // Use 0 in production
  $mail->Debugoutput = 'error_log';

  try {
    // Server settings
    $mail->isSMTP();
    $mail->Host       = 'smtp.hostinger.com'; // Confirm in Hostinger dashboard
    $mail->SMTPAuth   = true;
    $mail->Username   = 'noreply@besims.site';
    $mail->Password   = 'Group@42'; // Replace with actual password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // Or STARTTLS if using port 587
    $mail->Port       = 465; // Use 587 if STARTTLS

    // Recipients
    $mail->setFrom($from, 'Burol Elementary School');
    $mail->addAddress($to);

    // Content
    $mail->isHTML(false); // Set to true if you want HTML formatting
    $mail->Subject = $subject;
    $mail->Body    = $body;
    $mail->Timeout = 10;

    $mail->send();
    return true;
  } catch (Exception $e) {
    error_log("Email could not be sent. Mailer Error: {$mail->ErrorInfo}");
    return false;
  }
}