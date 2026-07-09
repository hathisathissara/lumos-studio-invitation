<?php
$page_title = "Terms of Service";
// Root layouts/header.php එක Load කිරීම
require 'layouts/header.php'; 
?>

<!-- terms.php පිටුවට පමණක් අදාල විශේෂිත CSS මෝස්තරයන් -->
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
    <h1 class="page-title">Terms of Service</h1>
    <div class="content-divider"></div>
    <p style="color: var(--text-muted);">Last updated: July 2026</p>
</section>

<section class="content-container">
    <h2>1. Acceptance of Terms</h2>
    <p>By accessing or using Lumus Studio ("the Service"), you agree to be bound by these Terms of Service ("Terms"). If you do not agree to these Terms, you may not access or use the Service. These Terms constitute a legally binding agreement between you and Lumus Studio, operated from Colombo, Sri Lanka.</p>

    <h2>2. Description of Service</h2>
    <p>Lumus Studio is a digital wedding invitation platform that enables users to create, customize, and share beautiful wedding invitations online. Our services include:</p>
    <ul>
        <li>Creating and customizing digital wedding invitations from professionally designed themes.</li>
        <li>Managing guest lists with contact information and grouping.</li>
        <li>Collecting and tracking RSVP responses from guests.</li>
        <li>Sharing invitations via unique links and QR codes.</li>
        <li>Providing analytics on invitation views and guest engagement.</li>
    </ul>

    <h2>3. User Accounts</h2>
    <p>To use certain features of the Service, you must register for an account. When creating an account, you agree to:</p>
    <ul>
        <li>Provide accurate, current, and complete information during registration.</li>
        <li>Maintain the security of your password and account credentials.</li>
        <li>Accept responsibility for all activities that occur under your account.</li>
        <li>Maintain only one account per person. Duplicate accounts may be terminated.</li>
        <li>Notify us immediately of any unauthorized use of your account.</li>
    </ul>
    <p>We reserve the right to suspend or terminate accounts that violate these Terms or that have been inactive for an extended period.</p>

    <h2>4. Plans and Pricing</h2>
    <p>Lumus Studio offers the following plans and add-ons with fixed, one-time flat fees:</p>
    <ul>
        <li><strong>Free Preview (Trial Mode):</strong> Build, configure, and preview your complete invitation layout inside your dashboard at no cost before publication.</li>
        <li><strong>Basic Plan - Rs. 2,500:</strong> Standard digital card supporting up to 150 guest seat reservations, dynamic RSVP dashboard, countdown timer, checklist, photo gallery, and Google Maps integration.</li>
        <li><strong>Standard Plan - Rs. 5,000:</strong> Our most popular plan, supporting up to 300 guest seat reservations, 2 dynamic templates, free support to edit, and all core planning tools.</li>
        <li><strong>Premium Plan - Rs. 10,000:</strong> Offers fully customized design built from scratch by our team, unlimited guest seats, priority support, and the interactive Guest Shared Gallery included at no extra cost.</li>
        <li><strong>Guest Gallery Add-on - Rs. 2,000:</strong> An optional add-on available for Basic and Standard plans, allowing invitees to upload their own wedding photos directly onto your live invitation.</li>
    </ul>
    <p>All prices are listed in Sri Lankan Rupees (LKR) and are strict, one-time payments—there are no recurring subscriptions or surprise maintenance fees. Active accounts can be upgraded to a higher tier plan at any time directly from the dashboard by paying only the balance difference between the plans.</p>
    <p>We reserve the right to modify plans and pricing at any time. Any future pricing adjustments will not affect already paid, active invitations.</p>

    <h2>5. Payment Terms</h2>
    <p>We accept payments exclusively through the following method:</p>
    <ul>
        <li><strong>Bank Transfer:</strong> Direct bank deposit, internet banking, or mobile bank app transfer to our designated bank account. Account activation and package upgrades are subject to manual verification of your uploaded bank transfer slip or screenshot receipt.</li>
    </ul>
    <p><strong>Refund Policy:</strong> Payments are strictly non-refundable once your invitation link has been activated, published, or shared with guests. If you have not yet published or distributed your invitation link, you may officially submit a refund request directly through your dashboard payment panel or contact us at hatheesha6504@gmail.com within 7 days of your payment date.</p>

    <h2>6. User Content</h2>
    <p>You retain full ownership of any content you upload or create on the Service, including text, images, and wedding details ("User Content"). By using the Service, you grant Lumus Studio a non-exclusive, worldwide, royalty-free license to use, display, and distribute your User Content solely for the purpose of providing and operating the Service.</p>
    <p>You represent and warrant that you own or have the necessary rights and permissions to use and authorize us to display your User Content, and that your content does not infringe on the intellectual property rights of any third party.</p>

    <h2>7. Acceptable Use</h2>
    <p>You agree not to use the Service to:</p>
    <ul>
        <li>Upload, share, or distribute any illegal, harmful, threatening, abusive, or otherwise objectionable content.</li>
        <li>Send unsolicited messages, spam, or bulk communications to individuals who have not consented.</li>
        <li>Impersonate any person or entity or misrepresent your affiliation.</li>
        <li>Attempt to gain unauthorized access to the Service or its related systems.</li>
        <li>Introduce malware, viruses, or any other harmful code.</li>
        <li>Use the Service for any purpose other than creating legitimate wedding or event invitations.</li>
        <li>Scrape, crawl, or collect data from the Service through automated means without our consent.</li>
    </ul>

    <h2>8. Service Availability</h2>
    <p>We strive to keep Lumus Studio available at all times. However, we do not guarantee 100% uptime. The Service may be temporarily unavailable due to maintenance, updates, server issues, or circumstances beyond our control. We will make reasonable efforts to notify users of planned downtime in advance. We are not liable for any loss or inconvenience caused by service interruptions.</p>

    <h2>9. Intellectual Property</h2>
    <p>The Lumus Studio platform, including its design, themes, source code, logos, and all related intellectual property, is owned by Lumus Studio and is protected by applicable copyright and trademark laws. You may not copy, modify, distribute, sell, or lease any part of the Service or its underlying technology without our prior written consent. Themes provided by the Service are licensed for use within the platform only and may not be extracted or repurposed.</p>

    <h2>10. Limitation of Liability</h2>
    <p>To the fullest extent permitted by law, Lumus Studio and its operators, employees, and affiliates shall not be liable for any indirect, incidental, special, consequential, or punitive damages arising out of or relating to your use of the Service.</p>
    <p>Our total liability for any claim arising from or relating to the Service shall not exceed the amount you paid to us in the 12 months preceding the claim. The Service is provided on an "as is" and "as available" basis without warranties of any kind, either express or implied.</p>

    <h2>11. Termination</h2>
    <p>We may suspend or terminate your access to the Service at any time, with or without notice, for conduct that we believe violates these Terms or is harmful to other users, the Service, or third parties.</p>
    <p>You may terminate your account at any time by contacting us. Upon termination, your right to use the Service will cease immediately. Any data associated with your account may be deleted after termination, subject to our data retention policy.</p>

    <h2>12. Governing Law</h2>
    <p>These Terms shall be governed by and construed in accordance with the laws of Sri Lanka. Any disputes arising under or in connection with these Terms shall be subject to the exclusive jurisdiction of the courts of Sri Lanka.</p>

    <h2>13. Contact Information</h2>
    <p>If you have any questions about these Terms of Service, please contact us:</p>
    <ul>
        <li><strong>Email:</strong> hatheesha6504@gmail.com</li>
        <li><strong>Location:</strong> Bandarawela, Sri Lanka</li>
    </ul>
</section>

<?php 
// Root layouts/footer.php එක Load කිරීම
require 'layouts/footer.php'; 
?>