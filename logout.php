<?php
session_start();
require_once __DIR__ . '/config/database.php';

// Destroy session only — KEEP display cookies for auto-fill
$_SESSION = array();
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy();

// Only clear these — NOT display_uid and display_pwd
setcookie("remember_uid",   "", time() - 3600, "/");
setcookie("remember_token", "", time() - 3600, "/");
setcookie("university_id",  "", time() - 3600, "/");
setcookie("saved_password", "", time() - 3600, "/");

header("Location: index.php");
exit();
?>
```

**What happens now:**

| Scenario | Result |
|----------|--------|
| Login with Remember Me | Saves encrypted credentials in cookie |
| Logout | Session destroyed, **cookies kept** |
| Come back (any time within 30 days) | **Form pre-fills ID + password** |
| User must click Sign In | ✅ Always manual |
| Hacker sees cookie | `display_pwd = aGVsbG8=...` — encrypted, useless |

The cookie now shows:
```
display_uid = BS/2023/001
display_pwd = [AES-256 encrypted string]  ← safe