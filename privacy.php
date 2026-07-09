<?php
$page_title = "Privacy Policy";
// Root layouts/header.php එක Load කිරීම
require 'layouts/header.php'; 
?>

<!-- privacy.php පිටුවට පමණක් අදාල විශේෂිත CSS මෝස්තරයන් -->
<style>
    .page-header {
        padding: 140px 5% 60px;
        text-align: center;
        background: radial-gradient(ellipse 80% 60% at 50% 40%, rgba(201,169,110,0.12) 0%, transparent 70%);
    }
    .page-title {
        font-family: 'Cormorant Garamond', serif;
        font-size: clamp(2rem, 5vw, 3.2rem);
        font-weight: 400;
        line-height: 1.2;
        color: var(--white);
        margin-bottom: 16px;
    }
    .content-divider {
        width: 60px;
        height: 1px;
        background: linear-gradient(to right, transparent, var(--gold), transparent);
        margin: 20px auto;
    }
    .content-container {
        max-width: 800px;
        margin: 0 auto;
        padding: 0 20px 80px;
    }
    .content-container h2 {
        font-family: 'Cormorant Garamond', serif;
        color: var(--gold);
        font-size: 1.8rem;
        margin-top: 40px;
        margin-bottom: 16px;
    }
    .content-container p { margin-bottom: 16px; color: var(--text-muted); }
    .content-container ul { margin-bottom: 16px; margin-left: 20px; color: var(--text-muted); }
    .content-container li { margin-bottom: 8px; }

    @media (max-width: 1024px) {
        .page-header { padding: 120px 4% 50px; }
        .page-title { font-size: 2.5rem; }
    }

    @media (max-width: 768px) {
        .page-header { padding: 100px 3% 40px; }
        .page-title { font-size: 2rem; margin-bottom: 12px; }
        .content-container { padding: 0 15px 60px; max-width: 100%; }
        .content-container h2 { font-size: 1.5rem; margin-top: 30px; margin-bottom: 12px; }
        .content-container p { font-size: 0.95rem; margin-bottom: 12px; }
        .content-container ul { margin-left: 16px; font-size: 0.95rem; }
    }

    @media (max-width: 480px) {
        .page-header { padding: 80px 3% 30px; }
        .page-title { font-size: 1.5rem; margin-bottom: 8px; }
        .content-container { padding: 0 12px 45px; }
        .content-container h2 { font-size: 1.25rem; margin-top: 24px; margin-bottom: 10px; }
        .content-container p { font-size: 0.9rem; line-height: 1.5; margin-bottom: 10px; }
        .content-container ul { margin-left: 14px; font-size: 0.9rem; }
        .content-container li { margin-bottom: 6px; }
    }

    @media (max-width: 360px) {
        .page-header { padding: 70px 2% 25px; }
        .page-title { font-size: 1.3rem; }
        .content-container { padding: 0 10px 40px; }
    }
</style>

<section class="page-header">
    <h1 class="page-title">Privacy Policy</h1>
    <div class="content-divider"></div>
    <p style="color: var(--text-muted);">Last updated: April 2026</p>
</section>

<section class="content-container">
    <p>Welcome to Lumus Studio ("we", "us", or "our"). We are a digital wedding invitation platform based in Colombo, Sri Lanka. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you visit our website and use our services. Please read this policy carefully. By using Lumus Studio, you consent to the practices described in this policy.</p>

    <h2>1. Information We Collect</h2>
    <p>We collect information that you provide directly to us when you create an account, build an invitation, or interact with our services:</p>
    <ul>
        <li><strong>Account Information:</strong> Your name, email address, phone number, and password when you register.</li>
        <li><strong>Wedding Details:</strong> Bride and groom names, wedding date, venue details, ceremony information, and any other content you include in your invitation.</li>
        <li><strong>Guest Data:</strong> Names, email addresses, phone numbers, and RSVP responses of guests you add to your guest list.</li>
        <li><strong>Payment Information:</strong> Billing details processed securely. We only require manual bank transfer slips and do not store credit card numbers on our servers.</li>
        <li><strong>Usage Data:</strong> Information about how you interact with our platform, including page views, features used, and device information.</li>
    </ul>

    <h2>2. How We Use Your Information</h2>
    <p>We use the information we collect for the following purposes:</p>
    <ul>
        <li>To provide, maintain, and improve our digital invitation services.</li>
        <li>To create and manage your account and process your invitations.</li>
        <li>To send RSVP notifications, reminders, and service-related communications.</li>
        <li>To process payments and send transaction confirmations.</li>
        <li>To respond to your inquiries, support requests, and feedback.</li>
        <li>To monitor and analyze usage trends to improve user experience.</li>
        <li>To detect, prevent, and address fraud or technical issues.</li>
    </ul>

    <h2>3. Data Storage and Security</h2>
    <p>Your data is stored securely in a database hosted on reliable cloud infrastructure. We implement industry-standard security measures to protect your information, including:</p>
    <ul>
        <li>Encrypted data transmission using SSL/TLS protocols.</li>
        <li>Encrypted storage for sensitive data at rest.</li>
        <li>Regular security audits and updates to our infrastructure.</li>
        <li>Access controls limiting data access to authorized personnel only.</li>
    </ul>
    <p>While we strive to use commercially acceptable means to protect your personal information, no method of transmission over the Internet or electronic storage is 100% secure. We cannot guarantee absolute security.</p>

    <h2>4. Third-Party Services</h2>
    <p>We use trusted third-party services to operate our platform:</p>
    <ul>
        <li><strong>SMTP Mail Delivery:</strong> For delivering transactional emails such as RSVP notifications and account confirmations.</li>
        <li><strong>Cloud Hosting:</strong> For hosting and database infrastructure.</li>
    </ul>
    <p>These third parties have access to your information only to perform specific tasks on our behalf and are obligated to protect it.</p>

    <h2>5. Cookies and Tracking</h2>
    <p>We use cookies and similar technologies to enhance your experience:</p>
    <ul>
        <li><strong>Essential Cookies:</strong> Required for authentication and session management.</li>
        <li><strong>Analytics:</strong> We collect anonymous page view data to understand how visitors use our platform and to improve our services.</li>
    </ul>
    <p>We do not use third-party advertising trackers or sell your browsing data to advertisers.</p>

    <h2>6. Your Rights</h2>
    <p>You have the following rights regarding your personal data:</p>
    <ul>
        <li><strong>Access:</strong> Request a copy of the personal data we hold about you.</li>
        <li><strong>Correction:</strong> Request correction of any inaccurate or incomplete data.</li>
        <li><strong>Deletion:</strong> Request deletion of your account and all associated data.</li>
        <li><strong>Export:</strong> Request an export of your data in a portable format.</li>
        <li><strong>Withdraw Consent:</strong> Withdraw your consent for data processing at any time by contacting us.</li>
    </ul>
    <p>To exercise any of these rights, please contact us at hatheesha6504@gmail.com. We will respond to your request within 30 days.</p>

    <h2>7. Data Retention</h2>
    <p>We retain your personal data and invitation content for the duration of your active account. After your wedding date, we retain your data for an additional 6 months to allow you to access your guest list, RSVP data, and invitation analytics. After this retention period, your data will be permanently deleted unless you request an extension or export your data beforehand. You may request early deletion of your data at any time.</p>

    <h2>8. Children's Privacy</h2>
    <p>Lumus Studio is intended for users who are at least 18 years of age. We do not knowingly collect personal information from children under 18. If we become aware that we have collected data from a child under 18, we will take steps to delete that information promptly. If you believe a child has provided us with personal data, please contact us at hatheesha6504@gmail.com.</p>

    <h2>9. Changes to This Policy</h2>
    <p>We may update this Privacy Policy from time to time. We will notify you of any material changes by posting the new policy on this page and updating the "Last updated" date. We encourage you to review this Privacy Policy periodically for any changes. Continued use of our services after changes are posted constitutes acceptance of the revised policy.</p>

    <h2>10. Contact Us</h2>
    <p>If you have any questions or concerns about this Privacy Policy, please contact us:</p>
    <ul>
        <li><strong>Email:</strong> hatheesha6504@gmail.com</li>
        <li><strong>Location:</strong> Bandarawela, Sri Lanka</li>
    </ul>
</section>

<?php 
// Root layouts/footer.php එක Load කිරීම
require 'layouts/footer.php'; 
?>