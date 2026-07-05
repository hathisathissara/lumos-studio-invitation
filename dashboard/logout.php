<?php
session_start();

// Session එකේ තියෙන සියලුම දත්ත මකා දැමීම
$_SESSION = array();

// Session cookie එකක් තියෙනවා නම් එයත් මකා දැමීම
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Session එක විනාශ කිරීම
session_destroy();

// නැවත Login පිටුවට යැවීම
header("Location: login.php");
exit();
?>