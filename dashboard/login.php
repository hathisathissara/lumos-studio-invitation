<?php
session_start();
require '../config/config.php';
if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        header("Location: admin_dashboard.php");
    } else {
        header("Location: index.php");
    }
    exit();
}
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email    = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT users.*, weddings.id as wedding_id FROM users 
                           LEFT JOIN weddings ON users.id = weddings.user_id 
                           WHERE users.email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id']    = $user['id'];
        $_SESSION['user_name']  = $user['name'];
        $_SESSION['wedding_id'] = $user['wedding_id'];
        $_SESSION['status']     = $user['status'];
        $_SESSION['role']       = $user['role'];
        if ($user['role'] === 'admin') {
            header("Location: admin_dashboard.php");
        } else {
            header("Location: index.php");
        }
        exit();
    } else {
        $error = "Incorrect email or password. Please try again.";
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
                <label>Password</label>
                <div class="input-wrap">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" id="password" placeholder="••••••••" required autocomplete="current-password">
                </div>
            </div>
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