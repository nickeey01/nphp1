<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/vendor/autoload.php';

// Function to generate random 6-digit verification code
function generateVerificationCode() {
    return sprintf('%06d', mt_rand(100000, 999999));
}

// Function to send 2FA email
function send2FAEmail($email, $name, $code) {
    $mail = new PHPMailer(true);

    try {
        // SMTP settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'nicole.omuoyo@strathmore.edu';
        $mail->Password   = 'koolnnfuphjmmocg';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Recipients
        $mail->setFrom('nicole.omuoyo@strathmore.edu', 'ICS 2.2 Security');
        $mail->addAddress($email, $name);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'ICS 2.2 - Two-Factor Authentication Code';
        $mail->Body    = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f8f9fa;'>
            <div style='background-color: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);'>
                <h2 style='color: #007cba; text-align: center; margin-bottom: 30px;'>🔐 Two-Factor Authentication</h2>
                
                <p>Hello <strong>{$name}</strong>,</p>
                
                <p>You have requested a two-factor authentication code for your ICS 2.2 account.</p>
                
                <div style='background-color: #e7f3ff; padding: 20px; border-radius: 8px; text-align: center; margin: 20px 0;'>
                    <p style='margin: 0; font-size: 14px; color: #666;'>Your verification code is:</p>
                    <h1 style='color: #007cba; font-size: 36px; letter-spacing: 4px; margin: 10px 0; font-family: monospace;'>{$code}</h1>
                    <p style='margin: 0; font-size: 12px; color: #999;'>This code expires in 10 minutes</p>
                </div>
                
                <p style='color: #666; font-size: 14px;'>
                    <strong>Security Notice:</strong><br>
                    • Do not share this code with anyone<br>
                    • This code is valid for 10 minutes only<br>
                    • If you didn't request this code, please ignore this email
                </p>
                
                <hr style='border: none; border-top: 1px solid #eee; margin: 20px 0;'>
                
                <p style='color: #999; font-size: 12px; text-align: center;'>
                    ICS 2.2 Security Team<br>
                    Generated at: " . date('Y-m-d H:i:s') . "
                </p>
            </div>
        </div>";
        
        $mail->AltBody = "Hello {$name},\n\nYour ICS 2.2 two-factor authentication code is: {$code}\n\nThis code expires in 10 minutes.\n\nICS 2.2 Security Team";

        $mail->send();
        return true;
    } catch (Exception $e) {
        return "Mailer Error: " . $mail->ErrorInfo;
    }
}

// Handle form submissions
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'send_code') {
            // Send verification code
            $email = trim($_POST['email'] ?? '');
            $name = trim($_POST['name'] ?? '');
            
            // Validate input
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $message = 'Please enter a valid email address.';
                $messageType = 'error';
            } elseif (empty($name)) {
                $message = 'Please enter your name.';
                $messageType = 'error';
            } else {
                // Generate and store verification code
                $verificationCode = generateVerificationCode();
                $_SESSION['2fa_code'] = $verificationCode;
                $_SESSION['2fa_email'] = $email;
                $_SESSION['2fa_name'] = $name;
                $_SESSION['2fa_expires'] = time() + (10 * 60); // 10 minutes
                
                // Send email
                $result = send2FAEmail($email, $name, $verificationCode);
                
                if ($result === true) {
                    $message = "Verification code sent to {$email}. Please check your email and enter the code below.";
                    $messageType = 'success';
                    $_SESSION['code_sent'] = true;
                } else {
                    $message = "Failed to send email: {$result}";
                    $messageType = 'error';
                }
            }
        } elseif ($_POST['action'] === 'verify_code') {
            // Verify the code
            $enteredCode = trim($_POST['verification_code'] ?? '');
            
            if (empty($enteredCode)) {
                $message = 'Please enter the verification code.';
                $messageType = 'error';
            } elseif (!isset($_SESSION['2fa_code']) || !isset($_SESSION['2fa_expires'])) {
                $message = 'No verification code found. Please request a new code.';
                $messageType = 'error';
            } elseif (time() > $_SESSION['2fa_expires']) {
                $message = 'Verification code has expired. Please request a new code.';
                $messageType = 'error';
                // Clear expired session data
                unset($_SESSION['2fa_code'], $_SESSION['2fa_email'], $_SESSION['2fa_name'], $_SESSION['2fa_expires'], $_SESSION['code_sent']);
            } elseif ($enteredCode === $_SESSION['2fa_code']) {
                $message = "✅ Verification successful! Welcome, {$_SESSION['2fa_name']}!";
                $messageType = 'success';
                // Clear session data after successful verification
                unset($_SESSION['2fa_code'], $_SESSION['2fa_email'], $_SESSION['2fa_name'], $_SESSION['2fa_expires'], $_SESSION['code_sent']);
            } else {
                $message = 'Invalid verification code. Please try again.';
                $messageType = 'error';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Two-Factor Authentication - ICS 2.2</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 20px;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            max-width: 500px;
            width: 100%;
        }
        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
            font-size: 28px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 500;
        }
        input[type="email"], input[type="text"] {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 8px;
            box-sizing: border-box;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        input[type="email"]:focus, input[type="text"]:focus {
            outline: none;
            border-color: #667eea;
        }
        .verification-input {
            text-align: center;
            font-size: 24px;
            letter-spacing: 4px;
            font-family: monospace;
        }
        button {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }
        button:hover {
            transform: translateY(-2px);
        }
        .message {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .step-indicator {
            display: flex;
            justify-content: center;
            margin-bottom: 30px;
        }
        .step {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: #ddd;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 10px;
            font-weight: bold;
        }
        .step.active {
            background: #667eea;
        }
        .step.completed {
            background: #28a745;
        }
        .security-info {
            background: #e7f3ff;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
            font-size: 14px;
            color: #0c5460;
        }
        .new-code-link {
            text-align: center;
            margin-top: 15px;
        }
        .new-code-link a {
            color: #667eea;
            text-decoration: none;
        }
        .new-code-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔐 Two-Factor Authentication</h1>
        
        <!-- Step Indicator -->
        <div class="step-indicator">
            <div class="step <?php echo !isset($_SESSION['code_sent']) ? 'active' : 'completed'; ?>">1</div>
            <div class="step <?php echo isset($_SESSION['code_sent']) ? 'active' : ''; ?>">2</div>
        </div>
        
        <?php if ($message): ?>
        <div class="message <?php echo $messageType; ?>">
            <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
        </div>
        <?php endif; ?>
        
        <?php if (!isset($_SESSION['code_sent'])): ?>
        <!-- Step 1: Request Verification Code -->
        <form method="POST">
            <input type="hidden" name="action" value="send_code">
            
            <div class="form-group">
                <label for="name">Full Name:</label>
                <input type="text" id="name" name="name" required placeholder="Enter your full name">
            </div>
            
            <div class="form-group">
                <label for="email">Email Address:</label>
                <input type="email" id="email" name="email" required placeholder="Enter your email address">
            </div>
            
            <button type="submit">📧 Send Verification Code</button>
        </form>
        
        <div class="security-info">
            <strong>🛡️ Security Information:</strong><br>
            • A 6-digit verification code will be sent to your email<br>
            • The code expires in 10 minutes<br>
            • Keep your code private and secure
        </div>
        
        <?php else: ?>
        <!-- Step 2: Verify Code -->
        <p style="text-align: center; color: #666; margin-bottom: 20px;">
            Enter the 6-digit code sent to:<br>
            <strong><?php echo htmlspecialchars($_SESSION['2fa_email'], ENT_QUOTES, 'UTF-8'); ?></strong>
        </p>
        
        <form method="POST">
            <input type="hidden" name="action" value="verify_code">
            
            <div class="form-group">
                <label for="verification_code">Verification Code:</label>
                <input type="text" id="verification_code" name="verification_code" 
                       class="verification-input" required placeholder="000000" 
                       maxlength="6" pattern="[0-9]{6}">
            </div>
            
            <button type="submit">🔓 Verify Code</button>
        </form>
        
        <div class="new-code-link">
            <a href="?new_code=1" onclick="return confirm('Request a new verification code? The current code will be invalidated.')">
                Didn't receive the code? Request new code
            </a>
        </div>
        
        <div class="security-info">
            <strong>⏰ Code expires in:</strong> 
            <span id="countdown"><?php echo isset($_SESSION['2fa_expires']) ? max(0, $_SESSION['2fa_expires'] - time()) : 0; ?></span> seconds
        </div>
        
        <script>
        // Countdown timer
        let timeLeft = <?php echo isset($_SESSION['2fa_expires']) ? max(0, $_SESSION['2fa_expires'] - time()) : 0; ?>;
        const countdown = document.getElementById('countdown');
        
        const timer = setInterval(function() {
            if (timeLeft <= 0) {
                clearInterval(timer);
                countdown.textContent = 'EXPIRED';
                countdown.style.color = 'red';
                alert('Verification code has expired. Please request a new code.');
            } else {
                countdown.textContent = timeLeft;
                timeLeft--;
            }
        }, 1000);
        
        // Auto-focus on verification input
        if (document.getElementById('verification_code')) {
            document.getElementById('verification_code').focus();
        }
        
        // Auto-submit when 6 digits are entered
        if (document.getElementById('verification_code')) {
            document.getElementById('verification_code').addEventListener('input', function(e) {
                if (e.target.value.length === 6) {
                    e.target.form.submit();
                }
            });
        }
        </script>
        
        <?php endif; ?>
    </div>
</body>
</html>

<?php
// Handle new code request
if (isset($_GET['new_code'])) {
    unset($_SESSION['2fa_code'], $_SESSION['2fa_email'], $_SESSION['2fa_name'], $_SESSION['2fa_expires'], $_SESSION['code_sent']);
    header('Location: 2fa.php');
    exit;
}
?>
?>