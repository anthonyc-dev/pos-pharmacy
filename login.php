<?php
require_once __DIR__ . "/session.php";
require_once "db.php";

$error = "";

// If already logged in, redirect to appropriate dashboard
if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: admin/index.php");
        exit;
    } elseif ($_SESSION['role'] === 'cashier') {
        header("Location: cashier/index.php");
        exit;
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 1) {
        $user = $res->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            // Regenerate session ID on successful login
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];

            // Set persistent remember-me cookie (7 days)
            if (function_exists('setRememberMeCookie')) {
                setRememberMeCookie((int)$user['id'], (string)$user['username'], (string)$user['role']);
            }

            if ($user['role'] === 'admin') {
                header("Location: admin/index.php");
            } else {
                header("Location: cashier/index.php");
            }
            exit;
        } else {
            $error = "Invalid password!";
        }
    } else {
        $error = "No account found with that email!";
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>POS Login</title>

  <style>
/* Moving animated background */
body {
    margin: 0;
    padding: 0;
    font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
    background: linear-gradient(-45deg, #ff0000, #000000, #ffffff, #b30000);
    background-size: 400% 400%;
    animation: gradientBG 15s ease infinite;
    height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
}

@keyframes gradientBG {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

/* --- LOGIN BOX (blur + border) --- */
.login-box {
    background: rgba(255, 255, 255, 0.12); /* transparent glass */
    backdrop-filter: blur(18px);
    -webkit-backdrop-filter: blur(18px);
    padding: 40px 30px;
    border-radius: 16px;

    /* red border */
    border: 2px solid rgba(255, 0, 0, 0.7);

    /* shadow */
    box-shadow: 0 0 30px rgba(0, 0, 0, 0.5);

    width: 100%;
    max-width: 400px;
    text-align: center;
}

/* Heading */
.login-box h2 {
    color: #fff;
    margin-bottom: 20px;
}

/* Labels */
.login-box label {
    display: block;
    text-align: left;
    margin: 10px 0 5px;
    color: #fff;
    font-weight: 600;
}

.login-box input:focus {
    border-color: #ff0000;
    box-shadow: 0 0 0 3px rgba(255,0,0,0.3);
}

/* Buttons */
.login-box input {
    width: 100%;
    padding: 12px;
    border-radius: 8px;
    font-size: 16px;
    border: 1px solid #ddd;
    outline: none;
    transition: 0.3s;
    box-sizing: border-box; /* make padding included in width */
    margin-bottom: 15px; /* space between inputs and button */
}

.btn {
    width: 100%; /* same width as input */
    padding: 12px;
    background: #ff0000;
    color: white;
    font-size: 16px;
    border: none;
    border-radius: 8px;
    margin-top: 10px; /* space from input above */
    cursor: pointer;
    transition: 0.3s;
    box-sizing: border-box;
}

.btn:hover {
    background: #b30000;
}


.my-input {
    width: 100%;
    padding: 12px;
    color: "black"
}

.btn:hover {
    background: #b30000;
}

/* Error message */
.error {
    background: rgba(255, 0, 0, 0.6);
    padding: 10px;
    color: white;
    border-radius: 6px;
    margin-bottom: 15px;
}

.forgot-password {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    font-size: 15px;
    color: #000000;
    text-decoration: none;
    margin-bottom: 10px;
    width: 100%;
    padding-right: 10px;
}

.forgot-password:hover {
    text-decoration: underline;
    color: #ff0000;
}

  </style>
</head>
<body>

  <div class="login-box">
    <h2>POS Login</h2>

    <?php if (!empty($error)): ?>
      <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post">
      <label>Email</label>
      <input type="email" name="email"  required>

      <label>Password</label>
      <input type="password" name="password"  required>

      <a href="forgot-password/forgot-password.php" class="forgot-password">
        Forgot Password?
      </a>

      <button type="submit" class="btn">Login</button>
    </form>

    <!-- Register Button
    <form action="register.php" method="get">
      <button type="submit" class="btn">Register</button>
    </form> -->
  </div>

</body>
</html>
