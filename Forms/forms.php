<?php
// Show all errors
error_reporting(E_ALL);
ini_set('display_errors', 1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../plugins/PHPMailer/vendor/autoload.php';

class Forms {

    public function signup() {
        // If the form is submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'];
            $email    = $_POST['email'];
            $password = $_POST['password'];

            // Validate email
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                echo "❌ Invalid email address.<br>";
                return;
            }

            // Save user to DB
            $db_host = 'localhost';
            $db_user = 'root';
            $db_pass = '';
            $db_name = 'tol';
            $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
            if ($conn->connect_error) {
                echo "❌ DB Connection failed: " . $conn->connect_error;
            } else {
                // Check if email already exists
                $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $stmt->store_result();
                if ($stmt->num_rows > 0) {
                    echo "❌ Email already registered.<br>";
                    $stmt->close();
                    $conn->close();
                    return;
                }
                $stmt->close();

                // Insert user
                $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $username, $email, $password);
                $stmt->execute();
                $stmt->close();
                $conn->close();
            }

            // Send welcome email
            $mail = new PHPMailer(true);
            try {
                $mail->SMTPDebug  = 0; // Set to 0 for production
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'nicole.omuoyo@strathmore.edu'; // your Gmail
                $mail->Password   = 'YOUR_APP_PASSWORD';            // App Password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                $mail->setFrom('nicole.omuoyo@strathmore.edu', 'Task App Admin');
                $mail->addAddress($email, $username);

                $mail->isHTML(true);
                $mail->Subject = 'Welcome to Task App!';
                $mail->Body    = "Hello <b>{$username}</b>,<br>Welcome to Task App!";
                $mail->AltBody = "Hello {$username}, Welcome to Task App!";

                $mail->send();
                echo "✅ Welcome email has been sent to {$email}<br>";

            } catch (Exception $e) {
                echo "❌ Mailer Error: {$mail->ErrorInfo}<br>";
            }

            // Show user list after signup
            $this->showUserList();
        }

        // Display the form
        ?>
        <form action="" method="post">
            <input type="text" name="username" placeholder="Username" required><br><br>
            <input type="email" name="email" placeholder="Email" required><br><br>
            <input type="password" name="password" placeholder="Password" required><br><br>
            <button type="submit">Sign Up</button> 
            <a href="signin.php">Already have an account? Login</a>
        </form>
        <?php
    }

    // Display a numbered list of users who have signed up, in ascending order
    public function showUserList() {
        $db_host = 'localhost';
        $db_user = 'root';
        $db_pass = '';
        $db_name = 'tol';
        $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
        if ($conn->connect_error) {
            echo "❌ DB Connection failed: " . $conn->connect_error;
            return;
        }
        $sql = "SELECT username, email FROM users ORDER BY username ASC";
        $result = $conn->query($sql);
        if ($result && $result->num_rows > 0) {
            echo "<h3>Signed Up Users:</h3><ol>";
            while ($row = $result->fetch_assoc()) {
                echo "<li>" . htmlspecialchars($row['username']) . " (" . htmlspecialchars($row['email']) . ")</li>";
            }
            echo "</ol>";
        } else {
            echo "No users found.";
        }
        $conn->close();
    }

    public function signin() {
        // If the form is submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email    = $_POST['email'];
            $password = $_POST['password'];

            // Validate email
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                echo "❌ Invalid email address.<br>";
                return;
            }

            // Check credentials in DB
            $db_host = 'localhost';
            $db_user = 'root';
            $db_pass = '';
            $db_name = 'tol';
            $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
            if ($conn->connect_error) {
                echo "❌ DB Connection failed: " . $conn->connect_error;
            } else {
                $stmt = $conn->prepare("SELECT username FROM users WHERE email = ? AND password = ?");
                $stmt->bind_param("ss", $email, $password);
                $stmt->execute();
                $stmt->store_result();
                if ($stmt->num_rows > 0) {
                    $stmt->bind_result($username);
                    $stmt->fetch();
                    echo "✅ Welcome back, " . htmlspecialchars($username) . "!<br>";
                } else {
                    echo "❌ Invalid email or password.<br>";
                }
                $stmt->close();
                $conn->close();
            }
        }

        // Display the form
        ?>
        <form action="" method="post">
            <input type="email" name="email" placeholder="Email" required><br><br>
            <input type="password" name="password" placeholder="Password" required><br><br>
            <button type="submit">Sign In</button> 
            <a href="./">Don't have an account? Sign Up</a>
        </form>
        <?php
    }
}

// Example usage
$form = new Forms();
$form->signup(); // Show signup form & handle submission
