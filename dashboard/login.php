<?php
session_start();
require '../config/config.php';

// --- reCAPTCHA Settings ---
// ඔයාගේ Google reCAPTCHA (v2 Checkbox) Site Key එක සහ Secret Key එක මෙතනට දාන්න.
$RECAPTCHA_SITE_KEY   = $_ENV['RECAPTCHA_SITE_KEY']; 
$RECAPTCHA_SECRET_KEY = $_ENV['RECAPTCHA_SECRET_KEY'];

if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        header("Location: admin/index.php");
    } else {
        header("Location: user/index.php");
    }
    exit();
}
$error = "";

// --- Rate Limiting Settings ---
$MAX_ATTEMPTS = 5; // උපරිම වැරදි අවස්ථා ගණන
$LOCKOUT_TIME = 15; // විනාඩි 15ක් block වෙනවා

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $ip_address = $_SERVER['REMOTE_ADDR'];

    // 1. Check Rate Limit
    $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM login_attempts WHERE ip_address = ? AND attempt_time > (NOW() - INTERVAL ? MINUTE)");
    $stmtCheck->execute([$ip_address, $LOCKOUT_TIME]);
    $attempts = $stmtCheck->fetchColumn();

    if ($attempts >= $MAX_ATTEMPTS) {
        $error = "Too many failed attempts. Please try again after {$LOCKOUT_TIME} minutes.";
    } else {
        // 2. Verify reCAPTCHA (පහල keys මාරු කරාට පස්සේ මේක වැඩ කරාවි)
        $recaptcha_response = $_POST['g-recaptcha-response'] ?? '';
        $recaptcha_valid = true;

        if ($RECAPTCHA_SITE_KEY !== 'YOUR_SITE_KEY_HERE') {
            $recaptcha_url = 'https://www.google.com/recaptcha/api/siteverify';
            $recaptcha_data = [
                'secret' => $RECAPTCHA_SECRET_KEY,
                'response' => $recaptcha_response,
                'remoteip' => $ip_address
            ];
            
            $options = [
                'http' => [
                    'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                    'method'  => 'POST',
                    'content' => http_build_query($recaptcha_data)
                ]
            ];
            $context  = stream_context_create($options);
            $verify = file_get_contents($recaptcha_url, false, $context);
            $captcha_success = json_decode($verify);
            if (!$captcha_success->success) {
                $recaptcha_valid = false;
                $error = "Please complete the reCAPTCHA correctly.";
            }
        }

        if ($recaptcha_valid) {
            // 3. Process Login
            $stmt = $pdo->prepare("SELECT users.*, weddings.id as wedding_id FROM users 
                                   LEFT JOIN weddings ON users.id = weddings.user_id 
                                   WHERE users.email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Login Success! Clear previous failed attempts
                $pdo->prepare("DELETE FROM login_attempts WHERE ip_address = ?")->execute([$ip_address]);

                $_SESSION['user_id']    = $user['id'];
                $_SESSION['user_name']  = $user['name'];
                $_SESSION['wedding_id'] = $user['wedding_id'];
                $_SESSION['status']     = $user['status'];
                $_SESSION['role']       = $user['role'];
                
                if ($user['role'] === 'admin') {
                    header("Location: admin/index.php");
                } else {
                    header("Location: user/index.php");
                }
                exit();
            } else {
                // Record Failed Attempt
                $pdo->prepare("INSERT INTO login_attempts (ip_address, email, attempt_time) VALUES (?, ?, NOW())")->execute([$ip_address, $email]);
                $error = "Incorrect email or password. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In — Lumus Studio</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;1,400&family=Great+Vibes&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', sans-serif;
            background: #0f0f1a;
            min-height: 100vh;
            display: flex;
            overflow: hidden;
        }

        /* Left panel */
        .panel-left {
            display: none;
            width: 45%;
            background: linear-gradient(160deg, #1a1a2e 0%, #0f0f1a 60%);
            border-right: 1px solid rgba(201,169,110,0.1);
            padding: 60px 50px;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
        }
        @media (min-width: 900px) { .panel-left { display: flex; } }

        .panel-left::before {
            content: '';
            position: absolute;
            width: 400px; height: 400px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(201,169,110,0.08), transparent);
            bottom: -100px; right: -100px;
        }
        .panel-logo {
            font-family: 'Great Vibes', cursive;
            font-size: 2.4rem;
            color: #c9a96e;
            text-decoration: none;
        }
        .panel-quote {
            font-family: 'Cormorant Garamond', serif;
            font-size: 2rem;
            font-style: italic;
            color: rgba(232,228,220,0.8);
            line-height: 1.5;
        }
        .panel-quote em { color: #c9a96e; font-style: normal; }
        .panel-footer-text {
            font-size: 0.78rem;
            color: rgba(201,169,110,0.3);
        }

        /* Right: form */
        .panel-right {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 24px;
        }
        .form-box {
            width: 100%;
            max-width: 420px;
        }
        .form-box-title {
            font-size: 1.6rem;
            font-weight: 700;
            color: #e8e4dc;
            margin-bottom: 6px;
        }
        .form-box-sub {
            font-size: 0.88rem;
            color: #9e9aaa;
            margin-bottom: 36px;
        }
        .form-box-sub a { color: #c9a96e; text-decoration: none; }
        .form-box-sub a:hover { text-decoration: underline; }

        .form-group { margin-bottom: 18px; }
        label {
            display: block;
            font-size: 0.78rem;
            font-weight: 600;
            color: #9e9aaa;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            margin-bottom: 8px;
        }
        .input-wrap { position: relative; }
        .input-wrap i {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(201,169,110,0.4);
            font-size: 0.9rem;
        }
        input[type="email"], input[type="password"], input[type="text"] {
            width: 100%;
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(201,169,110,0.15);
            border-radius: 12px;
            padding: 13px 16px 13px 44px;
            color: #e8e4dc;
            font-family: 'Inter', sans-serif;
            font-size: 0.9rem;
            outline: none;
            transition: all 0.2s;
        }
        input:focus {
            border-color: rgba(201,169,110,0.45);
            background: rgba(201,169,110,0.04);
        }
        input::placeholder { color: rgba(232,228,220,0.2); }

        .error-box {
            background: rgba(239,68,68,0.08);
            border: 1px solid rgba(239,68,68,0.2);
            border-radius: 12px;
            padding: 12px 16px;
            color: #fca5a5;
            font-size: 0.84rem;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .success-box {
            background: rgba(34,197,94,0.08);
            border: 1px solid rgba(34,197,94,0.2);
            border-radius: 12px;
            padding: 12px 16px;
            color: #86efac;
            font-size: 0.84rem;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-login {
            width: 100%;
            background: linear-gradient(135deg, #c9a96e, #a07840);
            color: #0f0f1a;
            border: none;
            border-radius: 12px;
            padding: 14px;
            font-family: 'Inter', sans-serif;
            font-size: 0.92rem;
            font-weight: 700;
            cursor: pointer;
            letter-spacing: 0.5px;
            transition: all 0.3s;
            margin-top: 8px;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(201,169,110,0.3);
        }

        .divider-or {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 24px 0;
            color: rgba(201,169,110,0.3);
            font-size: 0.75rem;
        }
        .divider-or::before, .divider-or::after {
            content: '';
            flex: 1;
            height: 1px;
            background: rgba(201,169,110,0.12);
        }

        .back-home {
            text-align: center;
            margin-top: 28px;
            font-size: 0.8rem;
            color: rgba(201,169,110,0.4);
        }
        .back-home a { color: rgba(201,169,110,0.6); text-decoration: none; }
        .back-home a:hover { color: #c9a96e; }
    </style>
</head>
<body>
<div class="panel-left">
    <a href="../index.php" class="panel-logo">Lumus Studio</a>
    <div>
        <p class="panel-quote">
            "Where every love story gets a<br><em>beautiful beginning.</em>"
        </p>
    </div>
    <p class="panel-footer-text">© <?php echo date('Y'); ?> Lumus Studio · Digital Wedding Invitations</p>
</div>

<div class="panel-right">
    <div class="form-box">
        <h1 class="form-box-title">Welcome back</h1>
        <p class="form-box-sub">
            Don't have an account? <a href="register.php">Create one free →</a>
        </p>

        <?php if (isset($_GET['registered'])): ?>
        <div class="success-box"><i class="fas fa-check-circle"></i> Account created! You can now sign in.</div>
        <?php endif; ?>
        <?php if (isset($_GET['reset']) && $_GET['reset'] == 'success'): ?>
        <div class="success-box"><i class="fas fa-check-circle"></i> Password reset successfully! You can now sign in.</div>
        <?php endif; ?>

        <?php if ($error): ?>
        <div class="error-box"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Email Address</label>
                <div class="input-wrap">
                    <i class="fas fa-envelope"></i>
                    <input type="email" name="email" id="email" placeholder="you@example.com" required autocomplete="email">
                </div>
            </div>
            <div class="form-group">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <label style="margin-bottom: 0;">Password</label>
                    <a href="forgot_password.php" style="font-size: 0.75rem; color: #c9a96e; text-decoration: none;">Forgot password?</a>
                </div>
                <div class="input-wrap">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" id="password" placeholder="••••••••" required autocomplete="current-password">
                </div>
            </div>

            <?php if ($RECAPTCHA_SITE_KEY !== 'YOUR_SITE_KEY_HERE'): ?>
            <div class="form-group" style="margin-top: 20px; display: flex; justify-content: center;">
                <div class="g-recaptcha" data-sitekey="<?php echo htmlspecialchars($RECAPTCHA_SITE_KEY); ?>" data-theme="dark"></div>
            </div>
            <?php endif; ?>

            <button type="submit" class="btn-login">Sign In to Dashboard</button>
        </form>

        <div class="divider-or">or</div>
        <div style="text-align:center;">
            <a href="register.php" style="font-size:0.88rem; color:#c9a96e; text-decoration:none; font-weight:500;">
                <i class="fas fa-plus-circle" style="margin-right:6px;"></i> Create a New Invitation
            </a>
        </div>

        <div class="back-home">
            <a href="../index.php"><i class="fas fa-arrow-left" style="margin-right:4px;"></i> Back to Lumus Studio</a>
        </div>
    </div>
</div>
</body>
</html>