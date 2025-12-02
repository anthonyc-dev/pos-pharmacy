<?php
require_once __DIR__ . '/session.php';
require_once "db.php";

$msg = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);
    $role     = $_POST["role"];

    if ($username === "" || $password === "" || $role === "") {
        $error = "All fields are required!";
    } else {
        // Check for duplicate username
        $check = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $check->bind_param("s", $username);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $error = "❌ Username already exists. Please choose another.";
        } else {
            // hash password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $hashedPassword, $role);

            if ($stmt->execute()) {
                $msg = "✅ Account created successfully! You can now <a href='login.php'>Login</a>";
            } else {
                $error = "❌ Error: " . $conn->error;
            }
        }
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Register Account</title>

  <style>
/* Global box-sizing for consistent width */
*, *::before, *::after {
    box-sizing: border-box;
}

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

/* --- REGISTER BOX (blur + border) --- */
.register-box {
    background: rgba(255, 255, 255, 0.15); /* glass effect */
    backdrop-filter: blur(18px);
    -webkit-backdrop-filter: blur(18px);
    width: 380px;
    padding: 25px 30px;
    border-radius: 16px;

    border: 2px solid rgba(255, 0, 0, 0.75);
    box-shadow: 0 0 30px rgba(0, 0, 0, 0.45);

    color: #fff;
    text-align: center;
}

/* Title */
.register-box h2 {
    margin-bottom: 20px;
    color: #fff;
}

/* Input labels */
.input-group label {
    display: block;
    text-align: left;
    margin-bottom: 5px;
    color: #fff;
    font-weight: 600;
}

/* Input fields */
.input-group input,
.input-group select {
    width: 100%;
    padding: 12px;
    background: rgba(255, 255, 255, 0.85);
    border: 1px solid #ccc;
    border-radius: 8px;
    font-size: 16px;
    outline: none;
    transition: 0.3s;
}

.input-group input:focus,
.input-group select:focus {
    border-color: #ff0000;
    box-shadow: 0 0 0 3px rgba(255,0,0,0.3);
}

/* Buttons */
.btn {
    width: 100%;
    padding: 12px;
    background: #ff0000;
    color: white;
    font-size: 16px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: 0.3s;
    margin-top: 15px;
}

.btn:hover {
    background: #b30000;
}

/* Messages */
.msg { 
    color: #00ff00; 
    margin-bottom: 10px; 
}

.error { 
    color: #ffcccc; 
    margin-bottom: 10px; 
}

/* Login link */
.login-link {
    margin-top: 15px;
    color: #fff;
}

.login-link a {
    color: rgba(255, 0, 0, 0.75);
    text-decoration: none;
}

.login-link a:hover {
    text-decoration: underline;
}

  </style>
</head>
<body>

  <div class="register-box">
    <h2>Register Account</h2>

    <?php if ($msg): ?>
      <div class="msg"><?= $msg ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
      <div class="error"><?= $error ?></div>
    <?php endif; ?>

    <form method="post">
      <div class="input-group">
        <label>Username</label>
        <input type="text" name="username" required>
      </div>

      <div class="input-group">
        <label>Password</label>
        <input type="password" name="password" required>
      </div>

      <div class="input-group">
        <label>Role</label>
        <select name="role" required>
          <option value="">-- Select Role --</option>
          <option value="admin">Admin</option>
          <option value="cashier">Cashier</option>
        </select>
      </div>

      <button type="submit" class="btn">Register</button>
    </form>

    <div class="login-link">
      Already have an account? <a href="login.php">Login here</a>
    </div>
  </div>

</body>
</html>
