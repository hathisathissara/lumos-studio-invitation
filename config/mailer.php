<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    // Composer install
    require_once __DIR__ . '/../vendor/autoload.php';
} else {
    // Manual install fallback
    require_once __DIR__ . '/PHPMailer/Exception.php';
    require_once __DIR__ . '/PHPMailer/PHPMailer.php';
    require_once __DIR__ . '/PHPMailer/SMTP.php';
}

// ---------------- Fill these in ----------------
define('MAIL_SMTP_HOST', 'smtp.gmail.com');
define('MAIL_SMTP_PORT', 587);
define('MAIL_SMTP_USERNAME', 'noreply.sysmail.lk@gmail.com');   // Your Gmail address
define('MAIL_SMTP_PASSWORD', 'loce vyvk dyea kxlm');    // 16-char Gmail App Password
define('MAIL_FROM_EMAIL', 'noreply.sysmail.lk@gmail.com');
define('MAIL_FROM_NAME', 'Lumos Invitation Studio');
// -------------------------------------------------

/**
 * Low-level sender. Returns true/false, never throws.
 */
function send_app_mail($to_email, $to_name, $subject, $html_body) {
    if (empty($to_email)) return false;

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = MAIL_SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = MAIL_SMTP_USERNAME;
        $mail->Password   = MAIL_SMTP_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = MAIL_SMTP_PORT;

        $mail->setFrom(MAIL_FROM_EMAIL, MAIL_FROM_NAME);
        $mail->addAddress($to_email, $to_name);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $html_body;
        $mail->AltBody = trim(strip_tags(str_replace(['<br>', '<br/>', '<br />', '</p>'], "\n", $html_body)));

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('Mail send failed to ' . $to_email . ': ' . $mail->ErrorInfo);
        return false;
    }
}

/**
 * Wraps email body content in a simple branded layout.
 */
function mail_template_wrapper($title, $body_html) {
    return '
    <div style="font-family: Arial, Helvetica, sans-serif; max-width:560px; margin:0 auto; background:#ffffff; border-radius:12px; overflow:hidden; border:1px solid #e8ecf0;">
        <div style="background:linear-gradient(135deg,#1a1a2e,#2d2d50); padding:26px 32px;">
            <h2 style="color:#c9a96e; margin:0; font-size:1.15rem; font-family:Georgia, serif;">Lumos Invitation Studio</h2>
        </div>
        <div style="padding:32px; color:#1a1a2e; line-height:1.6; font-size:0.95rem;">
            <h3 style="margin-top:0; color:#1a1a2e;">' . htmlspecialchars($title) . '</h3>
            ' . $body_html . '
        </div>
        <div style="padding:16px 32px; background:#f8fafc; font-size:0.72rem; color:#9ea3b0;">
            This is an automated message from Lumos Invitation Studio. Please do not reply directly to this email.
        </div>
    </div>';
}

/**
 * 1) Sent when the admin activates a couple's invitation.
 */
function send_activation_mail($to_email, $couple_name, $invite_url) {
    $body = '
        <p>Dear ' . htmlspecialchars($couple_name) . ',</p>
        <p>Great news — your wedding invitation is now <strong>active</strong> and ready to share with your guests!</p>
        <p style="margin:20px 0;">
            <a href="' . htmlspecialchars($invite_url) . '" style="background:linear-gradient(135deg,#c9a96e,#a07840); color:#0f0f1a; padding:11px 22px; border-radius:8px; text-decoration:none; font-weight:700; display:inline-block;">View Your Invitation</a>
        </p>
        <p style="font-size:0.82rem; color:#6b7280; word-break:break-all;">' . htmlspecialchars($invite_url) . '</p>
        <p>Wishing you a beautiful wedding celebration.</p>';

    return send_app_mail(
        $to_email,
        $couple_name,
        'Your Invitation is Now Active',
        mail_template_wrapper('Invitation Activated', $body)
    );
}

/**
 * 2) Sent when the admin flags a past-wedding-date account for
 *    deletion — gives the couple a 7-day heads-up.
 */
function send_deletion_notice_mail($to_email, $couple_name) {
    $body = '
        <p>Dear ' . htmlspecialchars($couple_name) . ',</p>
        <p>We hope your wedding day was everything you dreamed of!</p>
        <p>Since your wedding date has now passed, your invitation and account will be automatically removed from our platform in <strong>7 days</strong>. This includes your guest list, photo gallery, events, and checklist.</p>
        <p>If you would like to keep your account or need more time, please contact us as soon as possible.</p>';

    return send_app_mail(
        $to_email,
        $couple_name,
        'Your Invitation Will Be Deleted in 7 Days',
        mail_template_wrapper('Account Deletion Notice', $body)
    );
}

/**
 * 3) Sent right after the admin permanently deletes the account.
 */
function send_deletion_confirmed_mail($to_email, $couple_name) {
    $body = '
        <p>Dear ' . htmlspecialchars($couple_name) . ',</p>
        <p>As previously notified, your wedding invitation and all associated data have now been permanently deleted from our platform.</p>
        <p>Thank you for using Lumos Invitation Studio for your special day. We wish you a lifetime of happiness together.</p>';

    return send_app_mail(
        $to_email,
        $couple_name,
        'Your Invitation Has Been Deleted',
        mail_template_wrapper('Account Deleted', $body)
    );
}

/**
 * 4) Sent when the admin approves a couple's refund. (Branded & Safe)
 */
function send_refund_approved_mail($to_email, $couple_name) {
    $subject = "Refund Approved - Lumos Studio";
    
    $body = '
        <p>Dear <strong>' . htmlspecialchars($couple_name) . '</strong>,</p>
        <p>We are writing to inform you that your refund request has been reviewed and successfully approved by our administrative team.</p>
        <p><strong>What happens next?</strong></p>
        <ul>
            <li>Your wedding invitation link has been deactivated (Status set back to pending).</li>
            <li>Your bank transfer refund is being processed and will be reversed shortly.</li>
            <li>Enter your Bank Details in your profile to receive the refund.</li>
        </ul>
        <p>Thank you for choosing Lumos Studio. If you have any further questions, feel free to contact our support.</p>';

    return send_app_mail(
        $to_email,
        $couple_name,
        $subject,
        mail_template_wrapper('Refund Request Approved', $body)
    );
}

/**
 * 5) Sent when the admin declines/rejects a couple's refund. (Branded & Safe)
 */
function send_refund_rejected_mail($to_email, $couple_name) {
    $subject = "Refund Request Update - Lumos Studio";
    
    $body = '
        <p>Dear <strong>' . htmlspecialchars($couple_name) . '</strong>,</p>
        <p>We have reviewed your request for a refund regarding your wedding invitation activation fee.</p>
        <p>Unfortunately, we are unable to process a refund at this time because your invitation link has already been opened or distributed to guests, or RSVP responses have been logged on your platform.</p>
        <p>According to our policy, once an invitation link is published or shared, the service is deemed consumed and is non-refundable.</p>
        <p>Your invitation link remains fully active and live for your guests to view and RSVP.</p>
        <p>If you have any further questions, feel free to contact our support.</p>';

    return send_app_mail(
        $to_email,
        $couple_name,
        $subject,
        mail_template_wrapper('Refund Request Declined', $body)
    );
}

/**
 * 6) Sent when the admin marks the bank payout as fully completed.
 */
function send_refund_completed_mail($to_email, $couple_name) {
    $subject = "Refund Completed - Lumos Studio";
    
    $body = '
        <p>Dear <strong>' . htmlspecialchars($couple_name) . '</strong>,</p>
        <p>We are pleased to inform you that your refund has been **fully processed and completed**.</p>
        <p>The activation fee of **Rs. 1000** has been transferred back to the bank account details you provided.</p>
        <p>Please allow some time for the funds to reflect in your account, depending on your bank\'s processing times.</p>
        <p>Thank you for your cooperation throughout this process. We wish you all the very best.</p>';

    return send_app_mail(
        $to_email,
        $couple_name,
        $subject,
        mail_template_wrapper('Refund Completed 💸', $body)
    );
}

/**
 * 7) Sent when the admin approves a couple's package upgrade.
 */
function send_upgrade_success_mail($to_email, $couple_name, $new_plan_name) {
    $subject = "Your Package has been Upgraded! - Lumos Studio";
    
    $body = '
        <p>Dear <strong>' . htmlspecialchars($couple_name) . '</strong>,</p>
        <p>Congratulations! Your request to upgrade your digital wedding invitation package has been approved.</p>
        <p>Your account has been successfully upgraded to the **' . htmlspecialchars($new_plan_name) . '**. All new features, higher guest limits, and customized options are now fully unlocked inside your dashboard.</p>
        <p>Log in to your dashboard now to explore your new tools!</p>';

    return send_app_mail(
        $to_email,
        $couple_name,
        $subject,
        mail_template_wrapper('Package Upgraded Successfully 💎', $body)
    );
}