<?php
$page_title = "Pricing — Choose Your Wedding Plan";
// Root layouts/header.php එක Load කිරීම
require 'layouts/header.php'; 
?>

<!-- pricing.php පිටුවට පමණක් අදාල විශේෂිත CSS මෝස්තරයන් -->
<style>
    /* =========== PRICING GRID LAYOUT =========== */
    .pricing-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 28px;
        max-width: 1200px;
        margin: 40px auto 0;
        align-items: stretch;
    }
    
    .pricing-card {
        position: relative;
        background: linear-gradient(180deg, rgba(255,255,255,0.03), rgba(255,255,255,0.01));
        border: 1px solid rgba(201,169,110,0.15);
        border-radius: 24px;
        padding: 44px 28px 36px;
        text-align: center;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        transition: transform 0.35s ease, box-shadow 0.35s ease, border-color 0.35s ease;
        box-shadow: 0 15px 40px rgba(0,0,0,0.2);
    }
    .pricing-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 24px 50px rgba(0,0,0,0.35);
        border-color: rgba(201,169,110,0.3);
    }

    /* Highlighted Card (Standard/Popular) */
    .pricing-card.popular {
        border: 2px solid var(--pink);
        background: linear-gradient(180deg, rgba(214,51,132,0.05), rgba(255,255,255,0.01));
    }
    .pricing-card.popular:hover {
        border-color: #f43f5e;
        box-shadow: 0 24px 50px rgba(214,51,132,0.15);
    }

    .pricing-ribbon {
        position: absolute;
        top: 15px; left: 50%;
        transform: translateX(-50%);
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: linear-gradient(135deg, var(--pink), #b91c1c);
        color: var(--white);
        font-size: 0.65rem;
        font-weight: 800;
        letter-spacing: 1.5px;
        text-transform: uppercase;
        padding: 5px 14px;
        border-radius: 50px;
        box-shadow: 0 4px 10px rgba(214,51,132,0.3);
    }
    
    .pricing-plan-name {
        font-family: 'Cormorant Garamond', serif;
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--white);
        margin-bottom: 4px;
    }
    .pricing-plan-sub {
        color: var(--text-muted);
        font-size: 0.78rem;
        margin-bottom: 20px;
    }
    .price-amount {
        display: flex;
        align-items: baseline;
        justify-content: center;
        gap: 4px;
        margin-bottom: 4px;
    }
    .price-currency {
        font-family: 'Cormorant Garamond', serif;
        font-size: 1.5rem;
        font-weight: 600;
        color: var(--gold);
    }
    .price-number {
        font-family: 'Cormorant Garamond', serif;
        font-size: 3.5rem;
        font-weight: 600;
        line-height: 1;
        background: linear-gradient(135deg, var(--gold-light), var(--gold));
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
    }
    .price-period {
        color: var(--text-muted);
        font-size: 0.8rem;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        margin-bottom: 24px;
    }
    .price-period strong { color: var(--gold); }
    
    .pricing-divider {
        width: 100%;
        height: 1px;
        background: linear-gradient(to right, transparent, rgba(201,169,110,0.2), transparent);
        margin: 8px 0 20px;
    }
    .pricing-features-list {
        list-style: none;
        text-align: left;
        display: flex;
        flex-direction: column;
        gap: 12px;
        margin-bottom: 28px;
        flex-grow: 1;
    }
    .pricing-features-list li {
        display: flex;
        align-items: flex-start;
        gap: 8px;
        color: var(--text-light);
        font-size: 0.84rem;
        line-height: 1.4;
    }
    .pricing-features-list i {
        color: var(--gold);
        margin-top: 3px;
        flex-shrink: 0;
        font-size: 0.75rem;
    }
    .pricing-card .btn-primary-gold {
        width: 100%;
        justify-content: center;
        font-size: 0.88rem;
        padding: 12px 24px;
    }
    .pricing-note {
        margin-top: 14px;
        color: var(--text-muted);
        font-size: 0.72rem;
    }

    /* =========== WHY THIS PRICE =========== */
    .why-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 24px;
        margin-top: 60px;
    }
    .why-card {
        background: rgba(255,255,255,0.03);
        border: 1px solid rgba(201,169,110,0.12);
        border-radius: 18px;
        padding: 28px 24px;
        text-align: center;
    }
    .why-card i { color: var(--gold); font-size: 1.4rem; margin-bottom: 14px; display: block; }
    .why-card h4 { font-family: 'Cormorant Garamond', serif; font-size: 1.15rem; color: var(--white); margin-bottom: 8px; }
    .why-card p { color: var(--text-muted); font-size: 0.85rem; line-height: 1.6; }

    /* =========== FAQ =========== */
    .faq-list { max-width: 720px; margin: 50px auto 0; }
    .faq-item {
        border-bottom: 1px solid rgba(201,169,110,0.12);
        padding: 22px 4px;
    }
    .faq-item h4 {
        font-family: 'Cormorant Garamond', serif;
        font-size: 1.15rem;
        color: var(--white);
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .faq-item h4 i { color: var(--gold); font-size: 0.95rem; }
    .faq-item p { color: var(--text-muted); font-size: 0.88rem; line-height: 1.7; padding-left: 24px; }

    @media (max-width: 768px) {
        .pricing-grid { grid-template-columns: 1fr; }
        .btn-primary-gold, .btn-ghost { width: 100%; justify-content: center; }
    }
    @media (max-width: 480px) {
        .pricing-card { padding: 24px 16px; }
    }
</style>

<!-- PAGE HEADER -->
<section style="padding:150px 5% 20px; text-align:center;">
    <span class="section-tag reveal">Simple, Transparent Pricing</span>
    <h1 class="section-title reveal">Choose your wedding plan.<br><em>Keep it forever.</em></h1>
    <div class="divider"></div>
    <p class="section-subtitle reveal" style="margin:0 auto;">Build and preview your full wedding invitation for free. Pay once, only when you're ready to publish.</p>
</section>

<!-- PRICING CARDS GRID -->
<section style="padding-top:20px;">
    <div class="pricing-grid">
        
        <!-- 1. BASIC CARD -->
        <div class="pricing-card reveal">
            <div>
                <div class="pricing-plan-name">Basic</div>
                <div class="pricing-plan-sub">Simple & elegant digital card</div>

                <div class="price-amount">
                    <span class="price-currency">Rs</span>
                    <span class="price-number">2,500</span>
                </div>
                <div class="price-period">One-time payment · <strong>Lifetime</strong></div>
                <div class="pricing-divider"></div>

                <ul class="pricing-features-list">
                    <li><i class="fas fa-check-circle"></i> 1 Elegant Invitation Template</li>
                    <li><i class="fas fa-check-circle"></i> Up to 150 guests (seats)</li>
                    <li><i class="fas fa-check-circle"></i> Live RSVP dashboard & guest tracking</li>
                    <li><i class="fas fa-check-circle"></i> WhatsApp personalized direct sending</li>
                    <li><i class="fas fa-check-circle"></i> Photo gallery & love story section</li>
                    <li><i class="fas fa-check-circle"></i> Live countdown timer & Google Maps</li>
                    <li><i class="fas fa-check-circle"></i> Wedding planning tasks checklist</li>
                    <li><i class="fas fa-check-circle"></i> Guest Gallery (Rs. 2,000 add-on)</li>
                </ul>
            </div>
            <div>
                <a href="dashboard/register.php" class="btn-primary-gold">
                    <i class="fas fa-heart"></i> Select Basic Plan
                </a>
                <div class="pricing-note">Free to build · Pay only when you publish</div>
            </div>
        </div>

        <!-- 2. STANDARD CARD (Most Popular Highlighted) -->
        <div class="pricing-card popular reveal">
            <span class="pricing-ribbon"><i class="fas fa-crown"></i> Most couples pick this</span>
            <div>
                <div class="pricing-plan-name" style="margin-top:12px;">Standard</div>
                <div class="pricing-plan-sub">Best for most Sri Lankan weddings</div>

                <div class="price-amount">
                    <span class="price-currency">Rs</span>
                    <span class="price-number">5,000</span>
                </div>
                <div class="price-period">One-time payment · <strong>Lifetime</strong></div>
                <div class="pricing-divider"></div>

                <ul class="pricing-features-list">
                    <li><i class="fas fa-check-circle"></i> 2 Elegant Invitation Templates</li>
                    <li><i class="fas fa-check-circle"></i> <strong>Up to 300 guests (seats)</strong></li>
                    <li><i class="fas fa-check-circle"></i> Live RSVP dashboard & guest tracking</li>
                    <li><i class="fas fa-check-circle"></i> WhatsApp personalized direct sending</li>
                    <li><i class="fas fa-check-circle"></i> Photo gallery & love story section</li>
                    <li><i class="fas fa-check-circle"></i> Live countdown timer & Google Maps</li>
                    <li><i class="fas fa-check-circle"></i> Wedding planning tasks checklist</li>
                    <li><i class="fas fa-check-circle"></i> Free support to edit template</li>
                    <li><i class="fas fa-check-circle"></i> Guest Gallery (Rs. 2,000 add-on)</li>
                </ul>
            </div>
            <div>
                <a href="dashboard/register.php" class="btn-primary-gold" style="background: linear-gradient(135deg, var(--pink), #be185d); color: white;">
                    <i class="fas fa-heart"></i> Select Standard Plan
                </a>
                <div class="pricing-note">Free to build · Pay only when you publish</div>
            </div>
        </div>

        <!-- 3. PREMIUM CARD -->
        <div class="pricing-card reveal">
            <div>
                <div class="pricing-plan-name">Premium</div>
                <div class="pricing-plan-sub">Custom designer services & interactive album</div>

                <div class="price-amount">
                    <span class="price-currency">Rs</span>
                    <span class="price-number">10,000</span>
                </div>
                <div class="price-period">One-time payment · <strong>Lifetime</strong></div>
                <div class="pricing-divider"></div>

                <ul class="pricing-features-list">
                    <li><i class="fas fa-check-circle"></i> 2 templates - including <strong>1 fully custom</strong></li>
                    <li><i class="fas fa-check-circle"></i> <strong>Unlimited guests & seats</strong></li>
                    <li><i class="fas fa-check-circle"></i> **Guest Gallery included (Free!)**</li>
                    <li><i class="fas fa-check-circle"></i> Custom design built from scratch</li>
                    <li><i class="fas fa-check-circle"></i> Priority WhatsApp support</li>
                    <li><i class="fas fa-check-circle"></i> Live RSVP dashboard & guest tracking</li>
                    <li><i class="fas fa-check-circle"></i> Photo gallery & love story section</li>
                    <li><i class="fas fa-check-circle"></i> Wedding planning tasks checklist</li>
                </ul>
            </div>
            <div>
                <a href="dashboard/register.php" class="btn-primary-gold">
                    <i class="fas fa-heart"></i> Select Premium Plan
                </a>
                <div class="pricing-note">Free to build · Pay only when you publish</div>
            </div>
        </div>

    </div>
</section>

<!-- WHY THIS PRICE -->
<section>
    <div style="text-align:center; margin-bottom:20px;">
        <span class="section-tag reveal">Why Couples Choose Us</span>
        <h2 class="section-title reveal">Built to be worry-free<br><em>from day one</em></h2>
        <div class="divider"></div>
    </div>
    <div class="why-grid">
        <div class="why-card reveal">
            <i class="fas fa-eye"></i>
            <h4>Try Before You Pay</h4>
            <p>Build your complete invitation and preview it exactly as your guests will see it — before spending a single rupee.</p>
        </div>
        <div class="why-card reveal">
            <i class="fas fa-university"></i>
            <h4>Easy Bank Transfer</h4>
            <p>Just upload your bank transfer slip. No online cards, no complicated checkout, activated within hours.</p>
        </div>
        <div class="why-card reveal">
            <i class="fas fa-infinity"></i>
            <h4>Upgrade Anytime, Fairly</h4>
            <p>Started on Basic but need more guest seats? Upgrade your plan at any time by paying only the price difference!</p>
        </div>
        <div class="why-card reveal">
            <i class="fas fa-headset"></i>
            <h4>Real Human Support</h4>
            <p>We're a local Sri Lankan team on WhatsApp. Have a question or template request? We'll answer it personally.</p>
        </div>
    </div>
</section>

<!-- FAQ -->
<section>
    <div style="text-align:center; margin-bottom:0;">
        <span class="section-tag reveal">Common Questions</span>
        <h2 class="section-title reveal">Pricing, <em>answered</em></h2>
        <div class="divider"></div>
    </div>
    <div class="faq-list">
        <div class="faq-item reveal">
            <h4><i class="fas fa-question-circle"></i> Are these prices really one-time payments?</h4>
            <p>Yes, absolutely. Every plan is a one-time flat fee. There are no monthly subscriptions, no hosting renewal fees, and no surprise charges. You own your link forever.</p>
        </div>
        <div class="faq-item reveal">
            <h4><i class="fas fa-question-circle"></i> Can I change/upgrade my plan after paying?</h4>
            <p>Yes. If you activate the Basic plan and realize you have more than 150 guests, you can upgrade to Standard or Premium directly from your dashboard by paying only the balance price difference.</p>
        </div>
        <div class="faq-item reveal">
            <h4><i class="fas fa-question-circle"></i> What is the Guest Gallery add-on?</h4>
            <p>It allows guests to upload photos they take at your wedding directly onto your invitation site. It's included free in the Premium plan, and can be added to Basic/Standard for just Rs. 2,000.</p>
        </div>
        <div class="faq-item reveal">
            <h4><i class="fas fa-question-circle"></i> Can I edit my invitation after publishing?</h4>
            <p>Yes. You can edit names, event dates, timings, planning tasks, or uploaded photos at any time. When you save, all guests see the updated version instantly without needing a new link.</p>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="cta-section" id="cta">
    <span class="section-tag reveal">Ready to Begin?</span>
    <h2 class="reveal">Start for <em>free</em> today</h2>
    <p class="reveal">Build your full invitation and preview it completely — no payment until you're ready.</p>
    <div class="reveal">
        <a href="dashboard/register.php" class="btn-primary-gold" style="font-size:1rem; padding:16px 40px;">
            <i class="fas fa-heart"></i> Create My Wedding Invitation
        </a>
    </div>
    <div class="cta-features reveal">
        <div class="cta-feature"><i class="fas fa-check"></i> Free to build & preview</div>
        <div class="cta-feature"><i class="fas fa-check"></i> Pay only when ready</div>
        <div class="cta-feature"><i class="fas fa-check"></i> Edit forever after</div>
        <div class="cta-feature"><i class="fas fa-check"></i> Beautiful on any phone</div>
    </div>
</section>

<?php 
// Root layouts/footer.php එක Load කිරීම
require 'layouts/footer.php'; 
?>