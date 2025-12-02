<?php


$lifetime = 7 * 24 * 60 * 60; 

if (!defined('REMEMBER_ME_SECRET')) {
    define('REMEMBER_ME_SECRET', 'change_this_to_a_random_secret_string_32+bytes');
}


ini_set('session.gc_maxlifetime', (string)$lifetime);
ini_set('session.cookie_lifetime', (string)$lifetime);


if (PHP_VERSION_ID >= 70300) {
    session_set_cookie_params([
        'lifetime' => $lifetime,
        'path' => '/',
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
} else {
   
    session_set_cookie_params($lifetime, '/');
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


function setRememberMeCookie(int $userId, string $username, string $role, ?int $lifetimeSeconds = null): void
{
    $ttl = $lifetimeSeconds ?? (7 * 24 * 60 * 60);
    $payload = [
        'uid' => $userId,
        'uname' => $username,
        'role' => $role,
        'exp' => time() + $ttl,
        'nonce' => bin2hex(random_bytes(16)),
    ];
    $token = base64_encode(json_encode($payload)); 
    $sig = hash_hmac('sha256', $token, REMEMBER_ME_SECRET);
    $value = $token . '|' . $sig;

    $params = [
        'expires' => time() + $ttl,
        'path' => '/',
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
        'httponly' => true,
        'samesite' => 'Lax',
    ];
    setcookie('remember', $value, $params);
}


function clearRememberMeCookie(): void
{
    if (isset($_COOKIE['remember'])) {
        setcookie('remember', '', [
            'expires' => time() - 3600,
            'path' => '/',
            'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        unset($_COOKIE['remember']);
    }
}


function tryRestoreSessionFromRememberCookie(): void
{
    if (isset($_SESSION['username']) && isset($_SESSION['role'])) {
        return;
    }
    if (empty($_COOKIE['remember'])) {
        return;
    }
    $parts = explode('|', $_COOKIE['remember'], 2);
    if (count($parts) !== 2) {
        clearRememberMeCookie();
        return;
    }
    [$token, $sig] = $parts;
    $calc = hash_hmac('sha256', $token, REMEMBER_ME_SECRET);
    if (!hash_equals($calc, $sig)) {
        clearRememberMeCookie();
        return;
    }
    $json = base64_decode($token, true);
    if ($json === false) {
        clearRememberMeCookie();
        return;
    }
    $payload = json_decode($json, true);
    if (!is_array($payload) || ($payload['exp'] ?? 0) < time()) {
        clearRememberMeCookie();
        return;
    }
    // Hydrate minimal session
    $_SESSION['user_id'] = (int)$payload['uid'];
    $_SESSION['username'] = (string)$payload['uname'];
    $_SESSION['role'] = (string)$payload['role'];
}


tryRestoreSessionFromRememberCookie();
?>
