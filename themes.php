<?php
$page_title = "Themes — 9 Elegant Wedding Invitation Themes";
// Root layouts/header.php එක Load කිරීම
require 'layouts/header.php'; 
?>

<!-- themes.php පිටුවට පමණක් අදාල විශේෂිත CSS මෝස්තරයන් -->
<style>
    /* =========== THEME GALLERY =========== */
    .theme-gallery-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 28px;
        margin-top: 60px;
    }
    .theme-card {
        border-radius: 20px;
        overflow: hidden;
        border: 1px solid rgba(201,169,110,0.15);
        background: rgba(255,255,255,0.02);
        transition: transform 0.35s ease, box-shadow 0.35s ease, border-color 0.35s ease;
    }
    .theme-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 24px 45px rgba(0,0,0,0.4);
        border-color: rgba(201,169,110,0.3);
    }
    .theme-phone-outer {
        padding: 30px 20px 22px;
        display: flex;
        justify-content: center;
    }
    .theme-phone-frame {
        width: 100%;
        max-width: 230px;
        background: var(--dark-2);
        border-radius: 34px;
        border: 2px solid rgba(201,169,110,0.2);
        padding: 16px 12px;
        box-shadow: 0 25px 50px rgba(0,0,0,0.5), 0 0 0 1px rgba(201,169,110,0.05);
    }
    .theme-phone-notch {
        width: 60px;
        height: 5px;
        background: rgba(255,255,255,0.12);
        border-radius: 10px;
        margin: 0 auto 14px;
    }
    .theme-phone-screen {
        border-radius: 20px;
        padding: 20px 14px;
        text-align: center;
        min-height: 400px;
        display: flex;
        flex-direction: column;
        align-items: center;
    }
    .theme-label {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 14px 18px;
        background: rgba(255,255,255,0.03);
        border-top: 1px solid rgba(201,169,110,0.1);
    }
    .theme-swatch {
        width: 14px;
        height: 14px;
        border-radius: 50%;
        flex-shrink: 0;
        box-shadow: 0 0 0 3px rgba(255,255,255,0.05);
    }
    .theme-label .theme-name {
        font-family: 'Cormorant Garamond', serif;
        font-size: 1.05rem;
        font-weight: 600;
        color: var(--white);
    }

    /* Dummy Phone Screen elements styling */
    .mock-names {
        font-family: 'Great Vibes', cursive;
        font-size: 1.8rem;
        color: #b78a44;
        line-height: 1.3;
        margin: 10px 0 6px;
    }
    .mock-date { font-size: 0.65rem; color: #888; letter-spacing: 1.5px; text-transform: uppercase; margin-bottom: 12px; font-family: sans-serif; }
    .mock-countdown {
        display: flex;
        gap: 6px;
        margin-bottom: 12px;
    }
    .mock-box {
        background: white;
        border: 1px solid #f0e6d2;
        border-radius: 8px;
        padding: 6px 8px;
        text-align: center;
        min-width: 40px;
    }
    .mock-box span { display: block; font-size: 1rem; font-weight: bold; color: #b78a44; font-family: sans-serif; }
    .mock-box small { font-size: 0.5rem; text-transform: uppercase; color: #aaa; font-family: sans-serif; }
    .mock-event {
        background: #fdfaf5;
        border: 1px solid #f0e6d2;
        border-radius: 10px;
        padding: 10px 12px;
        width: 100%;
        text-align: left;
        margin-bottom: 8px;
    }
    .mock-event-name { font-family: sans-serif; font-size: 0.7rem; font-weight: bold; color: #d63384; margin-bottom: 2px; }
    .mock-event-time { font-family: sans-serif; font-size: 0.6rem; color: #888; }
    .mock-rsvp {
        margin-top: 10px;
        background: #1a1a2e;
        color: white;
        border-radius: 20px;
        padding: 8px 24px;
        font-family: sans-serif;
        font-size: 0.7rem;
        font-weight: bold;
        letter-spacing: 1px;
    }

    @media (max-width: 768px) {
        .theme-gallery-grid { grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; }
        .theme-phone-frame { max-width: 200px; }
    }
    @media (max-width: 480px) {
        .theme-gallery-grid { grid-template-columns: 1fr; gap: 12px; }
        .theme-phone-outer { padding: 20px 10px; }
        .theme-phone-frame { max-width: 160px; padding: 12px 10px; }
        .theme-label { padding: 10px 12px; }
        .theme-name { font-size: 0.95rem; }
    }
</style>

<!-- THEME GALLERY SECTION -->
<section id="themes" style="padding-top:150px;">
    <div style="text-align:center; margin-bottom:20px;">
        <span class="section-tag reveal">Live Preview</span>
        <h2 class="section-title reveal">Choose your theme<br><em>see it before you pick it</em></h2>
        <div class="divider"></div>
        <p class="section-subtitle reveal" style="margin: 0 auto;">9 elegant themes, each with its own colours and personality. Switch anytime while building your invitation.</p>
    </div>
    
    <div class="theme-gallery-grid">

        <!-- 1. Premium Gold -->
        <div class="theme-card reveal">
            <div class="theme-phone-outer">
                <div class="theme-phone-frame">
                    <div class="theme-phone-notch"></div>
                    <div class="theme-phone-screen" style="background:#fdfaf5;">
                        <div style="font-size:0.55rem; color:#2d2115; font-family:'Inter',sans-serif; opacity:0.6; margin-bottom:4px;">You're Invited</div>
                        <div style="font-size:0.68rem; color:#b78a44; font-weight:600; font-family:'Inter',sans-serif; margin-bottom:4px;">Dear Guest,</div>
                        <div class="mock-names" style="color:#8a6520;">Amara<br><span style="font-size:1.1rem; color:#2d2115; opacity:0.4;">&amp;</span><br>Sithum</div>
                        <div class="mock-date" style="color:#2d2115;">We are getting married on</div>
                        <div style="font-size:0.65rem; color:#8a6520; font-weight:bold; font-family:'Inter',sans-serif; margin-bottom:10px;">Saturday, 14 February 2026</div>
                        <div class="mock-countdown">
                            <div class="mock-box" style="border-color:#e8d5a3;"><span class="t-cd-days" style="color:#8a6520;">00</span><small>Days</small></div>
                            <div class="mock-box" style="border-color:#e8d5a3;"><span class="t-cd-hrs" style="color:#8a6520;">00</span><small>Hrs</small></div>
                            <div class="mock-box" style="border-color:#e8d5a3;"><span class="t-cd-min" style="color:#8a6520;">00</span><small>Min</small></div>
                            <div class="mock-box" style="border-color:#e8d5a3;"><span class="t-cd-sec" style="color:#8a6520;">00</span><small>Sec</small></div>
                        </div>
                        <div class="mock-event" style="background:#f9f5ee; border-color:#e8d5a3;">
                            <div class="mock-event-name" style="color:#b78a44;">🌸 Poruwa Ceremony</div>
                            <div class="mock-event-time" style="color:#2d2115;">📍 Hotel Galadari, Colombo</div>
                        </div>
                        <div class="mock-event" style="background:#f9f5ee; border-color:#e8d5a3;">
                            <div class="mock-event-name" style="color:#b78a44;">✨ Reception</div>
                            <div class="mock-event-time" style="color:#2d2115;">📍 Cinnamon Grand, Colombo</div>
                        </div>
                        <div class="mock-rsvp" style="background:#8a6520; color:#fdfaf5;">RSVP — Confirm Attendance</div>
                    </div>
                </div>
            </div>
            <div class="theme-label">
                <span class="theme-swatch" style="background:#b78a44;"></span>
                <span class="theme-name">Premium Gold</span>
            </div>
        </div>

        <!-- 2. Minimal Light -->
        <div class="theme-card reveal">
            <div class="theme-phone-outer">
                <div class="theme-phone-frame">
                    <div class="theme-phone-notch"></div>
                    <div class="theme-phone-screen" style="background:#ffffff;">
                        <div style="font-size:0.55rem; color:#333333; font-family:'Inter',sans-serif; opacity:0.6; margin-bottom:4px;">You're Invited</div>
                        <div style="font-size:0.68rem; color:#8ba888; font-weight:600; font-family:'Inter',sans-serif; margin-bottom: 2px;">Dear Guest,</div>
                        <div class="mock-names" style="color:#5c755a; font-family:'Fraunces', serif; font-weight:600;">Amal &amp; Ruwani</div>
                        <div class="mock-date" style="color:#333333;">We are getting married on</div>
                        <div style="font-size:0.65rem; color:#5c755a; font-weight:bold; font-family:'Inter',sans-serif; margin-bottom:10px;">Saturday, 14 February 2026</div>
                        <div class="mock-countdown">
                            <div class="mock-box" style="border-color:#d6e2d4;"><span class="t-cd-days" style="color:#5c755a;">00</span><small>Days</small></div>
                            <div class="mock-box" style="border-color:#d6e2d4;"><span class="t-cd-hrs" style="color:#5c755a;">00</span><small>Hrs</small></div>
                            <div class="mock-box" style="border-color:#d6e2d4;"><span class="t-cd-min" style="color:#5c755a;">00</span><small>Min</small></div>
                            <div class="mock-box" style="border-color:#d6e2d4;"><span class="t-cd-sec" style="color:#5c755a;">00</span><small>Sec</small></div>
                        </div>
                        <div class="mock-event" style="background:#f8f9fa; border-color:#d6e2d4;">
                            <div class="mock-event-name" style="color:#8ba888;">🌿 Poruwa Ceremony</div>
                            <div class="mock-event-time" style="color:#333333;">📍 Hotel Galadari, Colombo</div>
                        </div>
                        <div class="mock-event" style="background:#f8f9fa; border-color:#d6e2d4;">
                            <div class="mock-event-name" style="color:#8ba888;">✨ Reception</div>
                            <div class="mock-event-time" style="color:#333333;">📍 Cinnamon Grand, Colombo</div>
                        </div>
                        <div class="mock-rsvp" style="background:#5c755a; color:#ffffff;">RSVP — Confirm Attendance</div>
                    </div>
                </div>
            </div>
            <div class="theme-label">
                <span class="theme-swatch" style="background:#8ba888;"></span>
                <span class="theme-name">Minimal Light</span>
            </div>
        </div>

        <!-- 3. Terracotta Bloom -->
        <div class="theme-card reveal">
            <div class="theme-phone-outer">
                <div class="theme-phone-frame">
                    <div class="theme-phone-notch"></div>
                    <div class="theme-phone-screen" style="background:#faf5ec;">
                        <div style="font-size:0.55rem; color:#362b21; font-family:'Inter',sans-serif; opacity:0.6; margin-bottom:4px;">You're Invited</div>
                        <div style="font-size:0.68rem; color:#c1633d; font-weight:600; font-family:'Inter',sans-serif; margin-bottom:4px;">Dear Guest,</div>
                        <div class="mock-names" style="color:#8f4526;">Amara<br><span style="font-size:1.1rem; color:#362b21; opacity:0.4;">&amp;</span><br>Sithum</div>
                        <div class="mock-date" style="color:#362b21;">We are getting married on</div>
                        <div style="font-size:0.65rem; color:#8f4526; font-weight:bold; font-family:'Inter',sans-serif; margin-bottom:10px;">Saturday, 14 February 2026</div>
                        <div class="mock-countdown">
                            <div class="mock-box" style="border-color:#e3a880;"><span class="t-cd-days" style="color:#8f4526;">00</span><small>Days</small></div>
                            <div class="mock-box" style="border-color:#e3a880;"><span class="t-cd-hrs" style="color:#8f4526;">00</span><small>Hrs</small></div>
                            <div class="mock-box" style="border-color:#e3a880;"><span class="t-cd-min" style="color:#8f4526;">00</span><small>Min</small></div>
                            <div class="mock-box" style="border-color:#e3a880;"><span class="t-cd-sec" style="color:#8f4526;">00</span><small>Sec</small></div>
                        </div>
                        <div class="mock-event" style="background:#f4ece0; border-color:#e3a880;">
                            <div class="mock-event-name" style="color:#c1633d;">🌿 Poruwa Ceremony</div>
                            <div class="mock-event-time" style="color:#362b21;">📍 Hotel Galadari, Colombo</div>
                        </div>
                        <div class="mock-event" style="background:#f4ece0; border-color:#e3a880;">
                            <div class="mock-event-name" style="color:#c1633d;">✨ Reception</div>
                            <div class="mock-event-time" style="color:#362b21;">📍 Cinnamon Grand, Colombo</div>
                        </div>
                        <div class="mock-rsvp" style="background:#8f4526; color:#faf5ec;">RSVP — Confirm Attendance</div>
                    </div>
                </div>
            </div>
            <div class="theme-label">
                <span class="theme-swatch" style="background:#c1633d;"></span>
                <span class="theme-name">Terracotta Bloom</span>
            </div>
        </div>

        <!-- 4. Plum Parchment -->
        <div class="theme-card reveal">
            <div class="theme-phone-outer">
                <div class="theme-phone-frame">
                    <div class="theme-phone-notch"></div>
                    <div class="theme-phone-screen" style="background:#f8f2e9;">
                        <div style="font-size:0.55rem; color:#2e2a28; font-family:'Inter',sans-serif; opacity:0.6; margin-bottom:4px;">You're Invited</div>
                        <div style="font-size:0.68rem; color:#8a9a7e; font-weight:600; font-family:'Inter',sans-serif; margin-bottom:4px;">Dear Guest,</div>
                        <div class="mock-names" style="color:#4a2c3b;">Amara<br><span style="font-size:1.1rem; color:#2e2a28; opacity:0.4;">&amp;</span><br>Sithum</div>
                        <div class="mock-date" style="color:#2e2a28;">We are getting married on</div>
                        <div style="font-size:0.65rem; color:#4a2c3b; font-weight:bold; font-family:'Inter',sans-serif; margin-bottom:10px;">Saturday, 14 February 2026</div>
                        <div class="mock-countdown">
                            <div class="mock-box" style="border-color:#b7c3ac;"><span class="t-cd-days" style="color:#4a2c3b;">00</span><small>Days</small></div>
                            <div class="mock-box" style="border-color:#b7c3ac;"><span class="t-cd-hrs" style="color:#4a2c3b;">00</span><small>Hrs</small></div>
                            <div class="mock-box" style="border-color:#b7c3ac;"><span class="t-cd-min" style="color:#4a2c3b;">00</span><small>Min</small></div>
                            <div class="mock-box" style="border-color:#b7c3ac;"><span class="t-cd-sec" style="color:#4a2c3b;">00</span><small>Sec</small></div>
                        </div>
                        <div class="mock-event" style="background:#f0e6d6; border-color:#b7c3ac;">
                            <div class="mock-event-name" style="color:#8a9a7e;">🌸 Poruwa Ceremony</div>
                            <div class="mock-event-time" style="color:#2e2a28;">📍 Hotel Galadari, Colombo</div>
                        </div>
                        <div class="mock-event" style="background:#f0e6d6; border-color:#b7c3ac;">
                            <div class="mock-event-name" style="color:#8a9a7e;">✨ Reception</div>
                            <div class="mock-event-time" style="color:#2e2a28;">📍 Cinnamon Grand, Colombo</div>
                        </div>
                        <div class="mock-rsvp" style="background:#4a2c3b; color:#f8f2e9;">RSVP — Confirm Attendance</div>
                    </div>
                </div>
            </div>
            <div class="theme-label">
                <span class="theme-swatch" style="background:#8a9a7e;"></span>
                <span class="theme-name">Plum Parchment</span>
            </div>
        </div>

        <!-- 5. Floral Garden -->
        <div class="theme-card reveal">
            <div class="theme-phone-outer">
                <div class="theme-phone-frame">
                    <div class="theme-phone-notch"></div>
                    <div class="theme-phone-screen" style="background:#fffdf8;">
                        <div style="font-size:0.55rem; color:#40352f; font-family:'Inter',sans-serif; opacity:0.6; margin-bottom:4px;">You're Invited</div>
                        <div style="font-size:0.68rem; color:#a9607c; font-weight:600; font-family:'Inter',sans-serif; margin-bottom:4px;">Dear Guest,</div>
                        <div class="mock-names" style="color:#a9607c;">Amara<br><span style="font-size:1.1rem; color:#40352f; opacity:0.4;">&amp;</span><br>Sithum</div>
                        <div class="mock-date" style="color:#40352f;">We are getting married on</div>
                        <div style="font-size:0.65rem; color:#9caf88; font-weight:bold; font-family:'Inter',sans-serif; margin-bottom:10px;">Saturday, 14 February 2026</div>
                        <div class="mock-countdown">
                            <div class="mock-box" style="border-color:#c3d3b1;"><span class="t-cd-days" style="color:#9caf88;">00</span><small>Days</small></div>
                            <div class="mock-box" style="border-color:#c3d3b1;"><span class="t-cd-hrs" style="color:#9caf88;">00</span><small>Hrs</small></div>
                            <div class="mock-box" style="border-color:#c3d3b1;"><span class="t-cd-min" style="color:#9caf88;">00</span><small>Min</small></div>
                            <div class="mock-box" style="border-color:#c3d3b1;"><span class="t-cd-sec" style="color:#9caf88;">00</span><small>Sec</small></div>
                        </div>
                        <div class="mock-event" style="background:#fbf3ea; border-color:#c3d3b1;">
                            <div class="mock-event-name" style="color:#a9607c;">🌸 Poruwa Ceremony</div>
                            <div class="mock-event-time" style="color:#40352f;">📍 Hotel Galadari, Colombo</div>
                        </div>
                        <div class="mock-event" style="background:#fbf3ea; border-color:#c3d3b1;">
                            <div class="mock-event-name" style="color:#a9607c;">✨ Reception</div>
                            <div class="mock-event-time" style="color:#40352f;">📍 Cinnamon Grand, Colombo</div>
                        </div>
                        <div class="mock-rsvp" style="background:#a9607c; color:#fffdf8;">RSVP — Confirm Attendance</div>
                    </div>
                </div>
            </div>
            <div class="theme-label">
                <span class="theme-swatch" style="background:#9caf88;"></span>
                <span class="theme-name">Floral Garden</span>
            </div>
        </div>

        <!-- 6. Beach Tropical -->
        <div class="theme-card reveal">
            <div class="theme-phone-outer">
                <div class="theme-phone-frame">
                    <div class="theme-phone-notch"></div>
                    <div class="theme-phone-screen" style="background:#fffdf9;">
                        <div style="font-size:0.55rem; color:#2b3a42; font-family:'Inter',sans-serif; opacity:0.6; margin-bottom:4px;">You're Invited</div>
                        <div style="font-size:0.68rem; color:#ef8264; font-weight:600; font-family:'Inter',sans-serif; margin-bottom:4px;">Dear Guest,</div>
                        <div class="mock-names" style="color:#2f7d9c;">Amara<br><span style="font-size:1.1rem; color:#2b3a42; opacity:0.4;">&amp;</span><br>Sithum</div>
                        <div class="mock-date" style="color:#2b3a42;">We are getting married on</div>
                        <div style="font-size:0.65rem; color:#2f7d9c; font-weight:bold; font-family:'Inter',sans-serif; margin-bottom:10px;">Saturday, 14 February 2026</div>
                        <div class="mock-countdown">
                            <div class="mock-box" style="border-color:#f4a688;"><span class="t-cd-days" style="color:#2f7d9c;">00</span><small>Days</small></div>
                            <div class="mock-box" style="border-color:#f4a688;"><span class="t-cd-hrs" style="color:#2f7d9c;">00</span><small>Hrs</small></div>
                            <div class="mock-box" style="border-color:#f4a688;"><span class="t-cd-min" style="color:#2f7d9c;">00</span><small>Min</small></div>
                            <div class="mock-box" style="border-color:#f4a688;"><span class="t-cd-sec" style="color:#2f7d9c;">00</span><small>Sec</small></div>
                        </div>
                        <div class="mock-event" style="background:#fbf1e2; border-color:#f4a688;">
                            <div class="mock-event-name" style="color:#ef8264;">🌴 Poruwa Ceremony</div>
                            <div class="mock-event-time" style="color:#2b3a42;">📍 Hotel Galadari, Colombo</div>
                        </div>
                        <div class="mock-event" style="background:#fbf1e2; border-color:#f4a688;">
                            <div class="mock-event-name" style="color:#ef8264;">✨ Reception</div>
                            <div class="mock-event-time" style="color:#2b3a42;">📍 Cinnamon Grand, Colombo</div>
                        </div>
                        <div class="mock-rsvp" style="background:#2f7d9c; color:#fffdf9;">RSVP — Confirm Attendance</div>
                    </div>
                </div>
            </div>
            <div class="theme-label">
                <span class="theme-swatch" style="background:#ef8264;"></span>
                <span class="theme-name">Beach Tropical</span>
            </div>
        </div>

        <!-- 7. Rustic Boho -->
        <div class="theme-card reveal">
            <div class="theme-phone-outer">
                <div class="theme-phone-frame">
                    <div class="theme-phone-notch"></div>
                    <div class="theme-phone-screen" style="background:#faf3e7;">
                        <div style="font-size:0.55rem; color:#3b2a1e; font-family:'Inter',sans-serif; opacity:0.6; margin-bottom:4px;">You're Invited</div>
                        <div style="font-size:0.68rem; color:#d99b6f; font-weight:600; font-family:'Inter',sans-serif; margin-bottom:4px;">Dear Guest,</div>
                        <div class="mock-names" style="color:#7a4225;">Amara<br><span style="font-size:1.1rem; color:#3b2a1e; opacity:0.4;">&amp;</span><br>Sithum</div>
                        <div class="mock-date" style="color:#3b2a1e;">We are getting married on</div>
                        <div style="font-size:0.65rem; color:#7a4225; font-weight:bold; font-family:'Inter',sans-serif; margin-bottom:10px;">Saturday, 14 February 2026</div>
                        <div class="mock-countdown">
                            <div class="mock-box" style="border-color:#e6bd97;"><span class="t-cd-days" style="color:#7a4225;">00</span><small>Days</small></div>
                            <div class="mock-box" style="border-color:#e6bd97;"><span class="t-cd-hrs" style="color:#7a4225;">00</span><small>Hrs</small></div>
                            <div class="mock-box" style="border-color:#e6bd97;"><span class="t-cd-min" style="color:#7a4225;">00</span><small>Min</small></div>
                            <div class="mock-box" style="border-color:#e6bd97;"><span class="t-cd-sec" style="color:#7a4225;">00</span><small>Sec</small></div>
                        </div>
                        <div class="mock-event" style="background:#f0e3ce; border-color:#e6bd97;">
                            <div class="mock-event-name" style="color:#d99b6f;">🪶 Poruwa Ceremony</div>
                            <div class="mock-event-time" style="color:#3b2a1e;">📍 Hotel Galadari, Colombo</div>
                        </div>
                        <div class="mock-event" style="background:#f0e3ce; border-color:#e6bd97;">
                            <div class="mock-event-name" style="color:#d99b6f;">✨ Reception</div>
                            <div class="mock-event-time" style="color:#3b2a1e;">📍 Cinnamon Grand, Colombo</div>
                        </div>
                        <div class="mock-rsvp" style="background:#7a4225; color:#faf3e7;">RSVP — Confirm Attendance</div>
                    </div>
                </div>
            </div>
            <div class="theme-label">
                <span class="theme-swatch" style="background:#d99b6f;"></span>
                <span class="theme-name">Rustic Boho</span>
            </div>
        </div>

        <!-- 8. Royal Classic -->
        <div class="theme-card reveal">
            <div class="theme-phone-outer">
                <div class="theme-phone-frame">
                    <div class="theme-phone-notch"></div>
                    <div class="theme-phone-screen" style="background:#faf7f0;">
                        <div style="font-size:0.55rem; color:#1c2340; font-family:'Inter',sans-serif; opacity:0.6; margin-bottom:4px;">You're Invited</div>
                        <div style="font-size:0.68rem; color:#c6a15b; font-weight:600; font-family:'Inter',sans-serif; margin-bottom:4px;">Dear Guest,</div>
                        <div class="mock-names" style="color:#4d1219;">Amara<br><span style="font-size:1.1rem; color:#1c2340; opacity:0.4;">&amp;</span><br>Sithum</div>
                        <div class="mock-date" style="color:#1c2340;">We are getting married on</div>
                        <div style="font-size:0.65rem; color:#4d1219; font-weight:bold; font-family:'Inter',sans-serif; margin-bottom:10px;">Saturday, 14 February 2026</div>
                        <div class="mock-countdown">
                            <div class="mock-box" style="border-color:#dcc189;"><span class="t-cd-days" style="color:#4d1219;">00</span><small>Days</small></div>
                            <div class="mock-box" style="border-color:#dcc189;"><span class="t-cd-hrs" style="color:#4d1219;">00</span><small>Hrs</small></div>
                            <div class="mock-box" style="border-color:#dcc189;"><span class="t-cd-min" style="color:#4d1219;">00</span><small>Min</small></div>
                            <div class="mock-box" style="border-color:#dcc189;"><span class="t-cd-sec" style="color:#4d1219;">00</span><small>Sec</small></div>
                        </div>
                        <div class="mock-event" style="background:#f1e9d8; border-color:#dcc189;">
                            <div class="mock-event-name" style="color:#c6a15b;">♛ Poruwa Ceremony</div>
                            <div class="mock-event-time" style="color:#1c2340;">📍 Hotel Galadari, Colombo</div>
                        </div>
                        <div class="mock-event" style="background:#f1e9d8; border-color:#dcc189;">
                            <div class="mock-event-name" style="color:#c6a15b;">✨ Reception</div>
                            <div class="mock-event-time" style="color:#1c2340;">📍 Cinnamon Grand, Colombo</div>
                        </div>
                        <div class="mock-rsvp" style="background:#1c2340; color:#faf7f0;">RSVP — Confirm Attendance</div>
                    </div>
                </div>
            </div>
            <div class="theme-label">
                <span class="theme-swatch" style="background:#c6a15b;"></span>
                <span class="theme-name">Royal Classic</span>
            </div>
        </div>

        <!-- 9. Indian Royal -->
        <div class="theme-card reveal">
            <div class="theme-phone-outer">
                <div class="theme-phone-frame">
                    <div class="theme-phone-notch"></div>
                    <div class="theme-phone-screen" style="background:#fff8ec;">
                        <div style="font-size:0.55rem; color:#3a1015; font-family:'Inter',sans-serif; opacity:0.6; margin-bottom:4px;">You're Invited</div>
                        <div style="font-size:0.68rem; color:#e0a527; font-weight:600; font-family:'Inter',sans-serif; margin-bottom:4px;">Dear Guest,</div>
                        <div class="mock-names" style="color:#6e1626;">Amara<br><span style="font-size:1.1rem; color:#3a1015; opacity:0.4;">&amp;</span><br>Sithum</div>
                        <div class="mock-date" style="color:#3a1015;">We are getting married on</div>
                        <div style="font-size:0.65rem; color:#6e1626; font-weight:bold; font-family:'Inter',sans-serif; margin-bottom:10px;">Saturday, 14 February 2026</div>
                        <div class="mock-countdown">
                            <div class="mock-box" style="border-color:#edc873;"><span class="t-cd-days" style="color:#6e1626;">00</span><small>Days</small></div>
                            <div class="mock-box" style="border-color:#edc873;"><span class="t-cd-hrs" style="color:#6e1626;">00</span><small>Hrs</small></div>
                            <div class="mock-box" style="border-color:#edc873;"><span class="t-cd-min" style="color:#6e1626;">00</span><small>Min</small></div>
                            <div class="mock-box" style="border-color:#edc873;"><span class="t-cd-sec" style="color:#6e1626;">00</span><small>Sec</small></div>
                        </div>
                        <div class="mock-event" style="background:#fbecc9; border-color:#edc873;">
                            <div class="mock-event-name" style="color:#e0a527;">❈ Poruwa Ceremony</div>
                            <div class="mock-event-time" style="color:#3a1015;">📍 Hotel Galadari, Colombo</div>
                        </div>
                        <div class="mock-event" style="background:#fbecc9; border-color:#edc873;">
                            <div class="mock-event-name" style="color:#e0a527;">✨ Reception</div>
                            <div class="mock-event-time" style="color:#3a1015;">📍 Cinnamon Grand, Colombo</div>
                        </div>
                        <div class="mock-rsvp" style="background:#6e1626; color:#fff8ec;">RSVP — Confirm Attendance</div>
                    </div>
                </div>
            </div>
            <div class="theme-label">
                <span class="theme-swatch" style="background:#e0a527;"></span>
                <span class="theme-name">Indian Royal</span>
            </div>
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