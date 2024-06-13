<?php
session_start();
require_once 'connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'];
    $new_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);

    // Update user's password and clear reset_token fields in the database
    $stmt = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE reset_token = ?");
    $stmt->bind_param("ss", $new_password, $token);
    $stmt->execute();
    $stmt->close();

    $_SESSION['status'] = "Password reset successfully. You can now login with your new password.";
}

header('Location: index.php');  // Redirect to login page
exit;
?>

