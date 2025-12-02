<?php
session_start();
require '../db.php';

$entered_code = $_POST['code'];
$email = $_SESSION['reset_email'] ?? '';

if (empty($email)) {
    header("Location: forgot-password.php?error=Session expired. Please try again.");
    exit;
}

// Get the reset code from database
$stmt = $conn->prepare("SELECT id, reset_code FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: forgot-password.php?error=No account found.");
    exit;
}

$user = $result->fetch_assoc();

if ($entered_code == $user['reset_code']) {
    // Code is correct - store user ID for password reset
    $_SESSION['reset_user_id'] = $user['id'];

    // Optional: Clear the reset code from database after successful verification
    $stmt = $conn->prepare("UPDATE users SET reset_code = NULL WHERE id = ?");
    $stmt->bind_param("i", $user['id']);
    $stmt->execute();

    // Redirect to success page with modal
    header("Location: code_verified_success.php");
    exit;
} else {
    header("Location: verify_code.php?msg=Incorrect code, try again.");
    exit;
}
