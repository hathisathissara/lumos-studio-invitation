<?php
$page_title = "Return & Refund Policy";
// Root layouts/header.php එක Load කිරීම
require 'layouts/header.php'; 
?>

<!-- refund.php පිටුවට පමණක් අදාල විශේෂිත CSS මෝස්තරයන් -->
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
    <h1 class="page-title">Return & Refund Policy</h1>
    <div class="content-divider"></div>
    <p style="color: var(--text-muted);">Last updated: April 2026</p>
</section>

<section class="content-container">
    <h2>1. Overview</h2>
    <p>Lumus Studio is a digital service. Every paid plan unlocks instant access to themes, editing tools, and your live invitation link. Because the product is delivered digitally and cannot be physically returned, refunds are handled on a case-by-case basis under the conditions below.</p>

    <h2>2. When you can request a refund</h2>
    <p>You are eligible for a full refund if all of the following are true:</p>
    <ul>
        <li>Your payment was completed within the last 7 days.</li>
        <li>Your invitation has not been published or shared with guests (the invitation link has not been distributed via WhatsApp, email, or any other channel).</li>
        <li>You have not sent RSVP links or bulk messages from your guest list.</li>
    </ul>
    <p>If your invitation has already been published or shared, we are unable to offer a refund - the service has already been delivered.</p>

    <h2>3. What is not refundable</h2>
    <ul>
        <li>Invitations that have been published or shared with guests.</li>
        <li>Payments older than 7 days from the date of purchase.</li>
        <li>Change of mind after the wedding date has passed.</li>
        <li>Charges made for add-ons that have already been consumed (e.g. bulk WhatsApp sends, SMS credits).</li>
    </ul>

    <h2>4. Failed or duplicate payments</h2>
    <p>If you were charged twice for the same plan, or a payment was debited but your plan was not upgraded, contact us within 30 days and we will investigate and refund the duplicate or failed charge in full. Please include your payment reference or bank receipt so we can locate the transaction quickly.</p>

    <h2>5. Bank transfers pending review</h2>
    <p>Bank transfers are manually verified before your plan is activated. If your transfer is still pending review and you no longer wish to proceed, email us and we will cancel the order before activation at no cost. Once a bank transfer has been approved and your plan activated, the standard refund conditions in Section 2 apply.</p>

    <h2>6. How to request a refund</h2>
    <p>Email hatheesha6504@gmail.com from the email address on your Lumus Studio account and include:</p>
    <ul>
        <li>Your full name and the plan you purchased.</li>
        <li>Date of payment and payment method (bank transfer).</li>
        <li>Payment reference and bank receipt</li>
        <li>A brief reason for the refund request.</li>
    </ul>
    <p>We will acknowledge your request within 2 business days and let you know the outcome.</p>

    <h2>7. Processing time</h2>
    <ul>
        <li><strong>Bank transfer refunds:</strong> 3–7 business days to the same account the payment was received from.</li>
    </ul>
    <p>Refunds are issued in Sri Lankan Rupees (LKR) for the exact amount originally paid. We do not refund currency conversion fees charged by your card issuer or bank.</p>

    <h2>8. Cancellations and account closure</h2>
    <p>All Lumus Studio plans are one-time payments - there are no recurring charges to cancel. If you would like your account and associated data deleted, you may request account closure at any time by contacting us. Account closure does not, by itself, entitle you to a refund outside the conditions in Section 2.</p>

    <h2>9. Related policies</h2>
    <p>This policy should be read alongside our Terms of Service and Privacy Policy. Where this policy conflicts with the Terms of Service, the terms of this Return & Refund Policy apply to refund matters only.</p>

    <h2>10. Contact us</h2>
    <p>Questions about this policy or an existing refund request?</p>
    <ul>
          <li><strong>Email:</strong> hatheesha6504@gmail.com</li>
        <li><strong>Location:</strong> Bandarawela, Sri Lanka</li>
    </ul>
</section>

<?php 
// Root layouts/footer.php එක Load කිරීම
require 'layouts/footer.php'; 
?>