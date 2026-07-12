<?php
require '../config/config.php';
$msg = "";
$step = 1;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $bride  = trim($_POST['bride_name']);
    $groom  = trim($_POST['groom_name']);
    $date   = $_POST['wedding_date'];
    $venue  = trim($_POST['venue']);
    $email  = trim($_POST['email']);
    $pass   = password_hash($_POST['password'], PASSWORD_DEFAULT);

    try {
        $pdo->beginTransaction();

        $sql1 = "INSERT INTO users (name, email, password, status) VALUES (?, ?, ?, 'pending')";
        $stmt1 = $pdo->prepare($sql1);
        $stmt1->execute([$bride . " & " . $groom, $email, $pass]);
        $user_id = $pdo->lastInsertId();

        $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $bride . '-' . $groom));
        $slug = trim($slug, '-');
        
        // Ensure uniqueness
        $check = $pdo->prepare("SELECT COUNT(*) FROM weddings WHERE slug = ?");
        $check->execute([$slug]);
        if ($check->fetchColumn() > 0) {
            $slug .= '-' . rand(100, 999);
        }

        $sql2 = "INSERT INTO weddings (user_id, bride_name, groom_name, wedding_date, slug, venue) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt2 = $pdo->prepare($sql2);
        $stmt2->execute([$user_id, $bride, $groom, $date, $slug, $venue]);

        $pdo->commit();
        header("Location: login.php?registered=success");
        exit();
    } catch (Exception $e) {
        $pdo->rollBack();
        $msg = "Registration failed. " . ($e->getCode() == 23000 ? "Email already registered." : $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Your Invitation — Lumus Studio</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;1,400&family=Great+Vibes&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', sans-serif;
            background: #0f0f1a;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        /* Background */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background: radial-gradient(ellipse 80% 50% at 50% 30%, rgba(201,169,110,0.07), transparent);
            pointer-events: none;
        }

        .register-container {
            width: 100%;
            max-width: 580px;
            position: relative;
            z-index: 1;
        }

        /* Top branding */
        .reg-brand {
            text-align: center;
            margin-bottom: 32px;
        }
        .reg-logo {
            font-family: 'Great Vibes', cursive;
            font-size: 2.4rem;
            color: #c9a96e;
            text-decoration: none;
            display: block;
        }
        .reg-tagline {
            font-size: 0.82rem;
            color: #9e9aaa;
            margin-top: 4px;
        }

        /* Step indicator */
        .steps-indicator {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0;
            margin-bottom: 32px;
        }
        .step-dot {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px;
        }
        .step-circle {
            width: 36px; height: 36px;
            border-radius: 50%;
            border: 2px solid rgba(201,169,110,0.2);
            background: rgba(255,255,255,0.03);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            font-weight: 700;
            color: #9e9aaa;
            transition: all 0.3s;
        }
        .step-circle.active {
            background: linear-gradient(135deg, #c9a96e, #a07840);
            border-color: #c9a96e;
            color: #0f0f1a;
            box-shadow: 0 4px 15px rgba(201,169,110,0.3);
        }
        .step-circle.done {
            background: rgba(201,169,110,0.12);
            border-color: rgba(201,169,110,0.4);
            color: #c9a96e;
        }
        .step-label {
            font-size: 0.62rem;
            color: #9e9aaa;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            white-space: nowrap;
        }
        .step-line {
            width: 60px; height: 1px;
            background: rgba(201,169,110,0.15);
            margin: 0 8px;
            margin-bottom: 22px;
        }

        /* Card */
        .reg-card {
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(201,169,110,0.12);
            border-radius: 24px;
            padding: 40px 44px;
            backdrop-filter: blur(20px);
            box-shadow: 0 20px 60px rgba(0,0,0,0.3), inset 0 1px 0 rgba(201,169,110,0.08);
        }
        @media (max-width: 480px) { .reg-card { padding: 32px 24px; } }

        .step-panel { display: none; }
        .step-panel.active { display: block; }

        .step-heading {
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.6rem;
            font-weight: 600;
            color: #e8e4dc;
            margin-bottom: 4px;
        }
        .step-sub {
            font-size: 0.83rem;
            color: #9e9aaa;
            margin-bottom: 28px;
        }

        .form-group { margin-bottom: 18px; }
        label {
            display: block;
            font-size: 0.75rem;
            font-weight: 600;
            color: #9e9aaa;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-bottom: 8px;
        }
        .required { color: rgba(201,169,110,0.7); margin-left: 3px; }
        .input-wrap { position: relative; }
        .input-wrap i {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(201,169,110,0.35);
            font-size: 0.9rem;
        }
        input[type="text"], input[type="email"], input[type="password"], input[type="date"] {
            width: 100%;
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(201,169,110,0.15);
            border-radius: 12px;
            padding: 13px 16px 13px 44px;
            color: #e8e4dc;
            font-family: 'Inter', sans-serif;
            font-size: 0.9rem;
            outline: none;
            transition: all 0.25s;
        }
        input:focus {
            border-color: rgba(201,169,110,0.45);
            background: rgba(201,169,110,0.04);
            box-shadow: 0 0 0 3px rgba(201,169,110,0.07);
        }
        input::placeholder { color: rgba(232,228,220,0.2); }
        input[type="date"]::-webkit-calendar-picker-indicator { filter: invert(0.6); cursor: pointer; }

        .btn-next, .btn-prev, .btn-submit {
            border: none;
            border-radius: 12px;
            padding: 13px 28px;
            font-family: 'Inter', sans-serif;
            font-size: 0.88rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn-next, .btn-submit {
            background: linear-gradient(135deg, #c9a96e, #a07840);
            color: #0f0f1a;
        }
        .btn-next:hover, .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(201,169,110,0.3);
        }
        .btn-prev {
            background: rgba(255,255,255,0.05);
            color: #9e9aaa;
            border: 1px solid rgba(255,255,255,0.08);
        }
        .btn-prev:hover { background: rgba(255,255,255,0.08); color: #e8e4dc; }

        .btn-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 28px;
            gap: 12px;
        }
        .btn-row.end { justify-content: flex-end; }

        .error-box {
            background: rgba(239,68,68,0.08);
            border: 1px solid rgba(239,68,68,0.2);
            border-radius: 12px;
            padding: 12px 16px;
            color: #fca5a5;
            font-size: 0.84rem;
            margin-bottom: 20px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }
        @media (max-width: 480px) { .form-row { grid-template-columns: 1fr; } }

        .signin-link {
            text-align: center;
            margin-top: 20px;
            font-size: 0.82rem;
            color: #9e9aaa;
        }
        .signin-link a { color: #c9a96e; text-decoration: none; }
        .signin-link a:hover { text-decoration: underline; }
    </style>
</head>
<body>
<div class="register-container">

    <div class="reg-brand">
        <a href="../index.php" class="reg-logo">Lumus Studio</a>
        <p class="reg-tagline">Create your beautiful wedding invitation</p>
    </div>

    <!-- Step indicator -->
    <div class="steps-indicator" id="steps-indicator">
        <div class="step-dot">
            <div class="step-circle active" id="s1c">1</div>
            <span class="step-label">Couple</span>
        </div>
        <div class="step-line"></div>
        <div class="step-dot">
            <div class="step-circle" id="s2c">2</div>
            <span class="step-label">Wedding</span>
        </div>
        <div class="step-line"></div>
        <div class="step-dot">
            <div class="step-circle" id="s3c">3</div>
            <span class="step-label">Account</span>
        </div>
    </div>

    <div class="reg-card">
        <?php if ($msg): ?>
        <div class="error-box"><i class="fas fa-exclamation-circle" style="margin-right:8px;"></i><?php echo htmlspecialchars($msg); ?></div>
        <?php endif; ?>

        <form method="POST" id="reg-form">
            <!-- STEP 1: Couple Names -->
            <div class="step-panel active" id="step-1">
                <h2 class="step-heading">Introduce the couple</h2>
                <p class="step-sub">The names that will grace your invitation</p>

                <div class="form-row">
                    <div class="form-group">
                        <label>Bride's Name <span class="required">*</span></label>
                        <div class="input-wrap">
                            <i class="fas fa-heart"></i>
                            <input type="text" name="bride_name" id="bride_name" placeholder="e.g. Amara" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Groom's Name <span class="required">*</span></label>
                        <div class="input-wrap">
                            <i class="fas fa-heart"></i>
                            <input type="text" name="groom_name" id="groom_name" placeholder="e.g. Sithum" required>
                        </div>
                    </div>
                </div>

                <div class="btn-row end">
                    <button type="button" class="btn-next" onclick="goStep(2)">
                        Wedding Details <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </div>

            <!-- STEP 2: Wedding Details -->
            <div class="step-panel" id="step-2">
                <h2 class="step-heading">When & Where?</h2>
                <p class="step-sub">Your wedding date and venue</p>

                <div class="form-group">
                    <label>Wedding Date <span class="required">*</span></label>
                    <div class="input-wrap">
                        <i class="far fa-calendar"></i>
                        <input type="date" name="wedding_date" id="wedding_date" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Main Venue <span class="required">*</span></label>
                    <div class="input-wrap">
                        <i class="fas fa-map-marker-alt"></i>
                        <input type="text" name="venue" id="venue" placeholder="Hotel or location name" required>
                    </div>
                </div>

                <div class="btn-row">
                    <button type="button" class="btn-prev" onclick="goStep(1)">
                        <i class="fas fa-arrow-left"></i> Back
                    </button>
                    <button type="button" class="btn-next" onclick="goStep(3)">
                        Your Account <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </div>

            <!-- STEP 3: Account -->
            <div class="step-panel" id="step-3">
                <h2 class="step-heading">Your Account</h2>
                <p class="step-sub">Sign in details to manage your invitation</p>

                <div class="form-group">
                    <label>Email Address <span class="required">*</span></label>
                    <div class="input-wrap">
                        <i class="fas fa-envelope"></i>
                        <input type="email" name="email" id="email" placeholder="you@example.com" required autocomplete="email">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Password <span class="required">*</span></label>
                        <div class="input-wrap">
                            <i class="fas fa-lock"></i>
                            <input type="password" name="password" id="password" placeholder="Min. 6 chars" required minlength="6" autocomplete="new-password">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Confirm Password <span class="required">*</span></label>
                        <div class="input-wrap">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="confirm_password" placeholder="••••••" required>
                        </div>
                    </div>
                </div>

                <div class="btn-row">
                    <button type="button" class="btn-prev" onclick="goStep(2)">
                        <i class="fas fa-arrow-left"></i> Back
                    </button>
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-heart"></i> Create My Invitation
                    </button>
                </div>
            </div>
        </form>
    </div>

    <div class="signin-link">
        Already have an account? <a href="login.php">Sign in →</a>
    </div>
</div>

<script>
let currentStep = 1;

function goStep(n) {
    // Validate current step before going forward
    if (n > currentStep) {
        if (currentStep === 1) {
            const bride = document.getElementById('bride_name').value.trim();
            const groom = document.getElementById('groom_name').value.trim();
            if (!bride || !groom) { alert('Please enter both names.'); return; }
        }
        if (currentStep === 2) {
            const date  = document.getElementById('wedding_date').value;
            const venue = document.getElementById('venue').value.trim();
            if (!date || !venue) { alert('Please fill in the wedding date and venue.'); return; }
        }
        if (currentStep === 3) {
            const pw  = document.getElementById('password').value;
            const cpw = document.getElementById('confirm_password').value;
            if (pw !== cpw) { alert('Passwords do not match.'); return; }
        }
    }

    // Hide current, show target
    document.getElementById('step-' + currentStep).classList.remove('active');
    document.getElementById('step-' + n).classList.add('active');

    // Update circles
    for (let i = 1; i <= 3; i++) {
        const c = document.getElementById('s' + i + 'c');
        c.classList.remove('active', 'done');
        if (i < n) { c.classList.add('done'); c.innerHTML = '<i class="fas fa-check" style="font-size:0.75rem;"></i>'; }
        else if (i === n) { c.classList.add('active'); c.textContent = i; }
        else { c.textContent = i; }
    }

    currentStep = n;
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// Final form validation
document.getElementById('reg-form').addEventListener('submit', function(e) {
    const pw  = document.getElementById('password').value;
    const cpw = document.getElementById('confirm_password').value;
    if (pw !== cpw) {
        e.preventDefault();
        alert('Passwords do not match!');
    }
});

<?php if ($msg): ?>
// If server-side error, show step 3
goStep(3);
<?php endif; ?>
</script>
</body>
</html>