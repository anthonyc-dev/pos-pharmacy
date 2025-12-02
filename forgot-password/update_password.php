<?php
session_start();
require '../db.php';

// Check if user_id exists in session (set by check_code.php after verification)
if (!isset($_SESSION['reset_user_id'])) {
    header("Location: forgot-password.php?error=Session expired. Please try again.");
    exit;
}

$pass = $_POST['password'];
$confirm = $_POST['confirm'];
$userId = $_SESSION['reset_user_id'];

// Validate passwords match
if ($pass !== $confirm) {
    header("Location: reset_password_form.php?error=Passwords do not match!");
    exit;
}

// Hash the new password
$hashed = password_hash($pass, PASSWORD_DEFAULT);

// Update password using prepared statement
$stmt = $conn->prepare("UPDATE users SET password = ?, reset_code = NULL WHERE id = ?");
$stmt->bind_param("si", $hashed, $userId);
$query = $stmt->execute();

if ($query) {
    // Clear reset session variables
    unset($_SESSION['reset_email']);
    unset($_SESSION['reset_user_id']);

    // Redirect to success page
    header("Location: password_reset_success.php");
    exit;
} else {
    header("Location: reset_password_form.php?error=Something went wrong. Please try again.");
    exit;
}
