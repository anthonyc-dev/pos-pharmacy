<?php
session_start();
require '../config.php';
require '../db.php';     

// 1. Get email from form
$email = $_POST['email'];

// 2. Check if user exists
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: forgot-password.php?error=No account found with that email.");
    exit;
}

$user = $result->fetch_assoc();
$userId = $user['id'];

// 3. Generate a 6-digit code
$code = random_int(100000, 999999);

// 4. Store the code in the database reset_code column
$stmt = $conn->prepare("UPDATE users SET reset_code = ? WHERE id = ?");
$stmt->bind_param("si", $code, $userId);
$stmt->execute();

// 5. Store email in session for verification page
$_SESSION['reset_email'] = $email;

// 6. Load PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

// 7. Send the code using PHPMailer
$mail = new PHPMailer(true);

try {
    // SMTP Settings
    $mail->isSMTP();
    $mail->Host       = $_ENV['MAIL_HOST'];
    $mail->SMTPAuth   = true;
    $mail->Username   = $_ENV['MAIL_USERNAME'];
    $mail->Password   = $_ENV['MAIL_PASSWORD'];
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = $_ENV['MAIL_PORT'];

    // Email
    $mail->setFrom($_ENV['MAIL_FROM'], $_ENV['MAIL_FROM_NAME']);
    $mail->addAddress($email);

    $mail->isHTML(true);
    $mail->Subject = 'Your Password Reset Code';
    $mail->Body    = "
        <h3>Your Password Reset Code</h3>
        <p>Enter this verification code to continue:</p>
        <h2 style='font-size: 32px; letter-spacing: 3px;'>$code</h2>
    ";

    $mail->send();

    header("Location: verify_code.php?msg=We sent a verification code to your email.");
    exit;

} catch (Exception $e) {
    echo "Email could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
