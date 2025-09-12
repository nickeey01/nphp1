<?php
// Show all errors
error_reporting(E_ALL);
ini_set('display_errors', 1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../plugins/PHPMailer/vendor/autoload.php';

// --- Capture form input ---
$user_email = $_POST['email'] ?? null;  // email entered by user
$user_name  = $_POST['name'] ?? null;   // name entered by user

// --- Validate the email address ---
if (!$user_email || !filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
    die("❌ Invalid email address provided.");
}

$mail = new PHPMailer(true);

try {
    // Enable SMTP debugging (set to 0 in production)
    $mail->SMTPDebug  = 2; 
    $mail->Debugoutput = 'html';

    // SMTP settings for Gmail / Google Workspace
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'nicole.omuoyo@strathmore.edu';  // your Gmail
    $mail->Password   = 'koolnnfuphjmmocg';              // App Password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    // Email headers
    $mail->setFrom('nicole.omuoyo@strathmore.edu', 'ICS 2.2');
    $mail->addAddress($user_email, $user_name);

    // Email content (customized greeting)
    $mail->isHTML(true);
    $mail->Subject = 'Welcome to ICS 2.2! Account Verification';
    $mail->Body    = "
        <p>Hello <b>{$user_name}</b>,</p>
        <p>You requested an account on ICS 2.2.</p>
        <p>To complete registration, please <a href='http://localhost/verify.php?email={$user_email}'>click here</a>.</p>
        <br>
        <p>Regards,<br>Systems Admin<br>ICS 2.2</p>
    ";
    $mail->AltBody = "Hello {$user_name},\n\nYou requested an account on ICS 2.2.\nTo complete registration, visit: http://localhost/verify.php?email={$user_email}\n\nRegards,\nSystems Admin, ICS 2.2";

    // Send the email
    $mail->send();
    echo "✅ Welcome email has been sent to {$user_email}";

} catch (Exception $e) {
    echo "❌ Message could not be sent.<br>";
    echo "Error Info: " . $mail->ErrorInfo . "<br>";
    echo "Exception: " . $e->getMessage() . "<br>";
}
