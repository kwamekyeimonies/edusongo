<?php
session_start();
require_once 'connection.php';

if (empty($_GET['token'])) {
    header('Location: forgot_password.php');
    exit;
}

$token = $_GET['token'];

// Check if the token exists in the database and is still valid
$stmt = $conn->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_token_expiry > NOW()");
$stmt->bind_param("s", $token);
$stmt->execute();
$stmt->store_result();
$count = $stmt->num_rows;
$stmt->close();

if ($count == 0) {
    $_SESSION['error'] = "Invalid or expired token.";
    header('Location: forgot_password.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
</head>
<body>
    <h2>Reset Password</h2>
    <?php if (isset($_SESSION['error'])): ?>
        <div><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>
    <form action="update_password.php" method="post">
        <input type="hidden" name="token" value="<?php echo $token; ?>">
        <label for="new_password">Enter your new password:</label><br>
        <input type="password" id="new_password" name="new_password" required><br><br>
        <button type="submit" name="submit">Reset Password</button>
    </form>
</body>
</html>

