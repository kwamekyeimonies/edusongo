<?php
session_start();
require_once 'connection.php'; // Database connection and PHPMailer autoload

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];

    // Check if the email exists in the database
    $stmt = $conn->prepare("SELECT id, username FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($user_id, $username);
    $stmt->fetch();
    $stmt->close();

    if ($user_id) {
        // Generate a unique token
        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

        // Update user's reset_token and reset_token_expiry in the database
        $stmt = $conn->prepare("UPDATE users SET reset_token = ?, reset_token_expiry = ? WHERE id = ?");
        $stmt->bind_param("ssi", $token, $expiry, $user_id);
        $stmt->execute();
        $stmt->close();

        // Compose email
        $reset_link = "http://example.com/reset_password.php?token=" . $token; // Update with your actual domain
        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.example.com';  // Set the SMTP server to send through
            $mail->SMTPAuth = true;
            $mail->Username = 'your-email@example.com';  // SMTP username
            $mail->Password = 'your-email-password';  // SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Recipients
            $mail->setFrom('your-email@example.com', 'Your Name');
            $mail->addAddress($email, $username);  // User's email

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Password Reset Request';
            $mail->Body = "Hello $username,<br><br>Click <a href='$reset_link'>here</a> to reset your password.<br><br>If you didn't request this, please ignore this email.";

            $mail->send();
            $_SESSION['status'] = "Password reset link sent to your email.";
        } catch (Exception $e) {
            $_SESSION['error'] = "Failed to send password reset email. Error: " . $mail->ErrorInfo;
        }
    } else {
        $_SESSION['error'] = "Email address not found.";
    }
}

header('Location: forgot_password.php');
exit;
?>

