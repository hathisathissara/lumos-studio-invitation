<?php
/**
 * Shared translation helper for guest-facing invitation UI labels.
 *
 * NOTE: only static headings / labels are translated (RSVP, Countdown,
 * Programme etc). Couple-typed content (love story text, venue names,
 * event names, guest notes...) is never touched — it stays exactly as
 * the couple typed it, regardless of the selected language.
 *
 * Usage in a template file:
 *   require_once __DIR__ . '/includes/lang.php';
 *   set_invite_lang($wedding['invite_language'] ?? 'en');
 *   echo t('rsvp_title');
 */

$GLOBALS['__invite_lang'] = 'en';

function set_invite_lang($lang) {
    $lang = in_array($lang, ['en', 'si', 'ta'], true) ? $lang : 'en';
    $GLOBALS['__invite_lang'] = $lang;
}

function t($key) {
    global $LANG_STRINGS, $__invite_lang;
    $lang = $GLOBALS['__invite_lang'] ?? 'en';
    if (isset($LANG_STRINGS[$key][$lang])) {
        return $LANG_STRINGS[$key][$lang];
    }
    // fallback to English, then raw key so nothing ever renders blank
    return $LANG_STRINGS[$key]['en'] ?? $key;
}

/**
 * Localized date formatting helpers (day/month names in Sinhala & Tamil,
 * since PHP's date() only knows English names).
 */
function _invite_date_maps() {
    return [
        'days' => [
            'si' => ['රවිදා', 'සඳුදා', 'කුජදා', 'බුධදා', 'ගුරුදා', 'කිවිදා', 'ශනිදා'],
            'ta' => ['ஞாயிறு', 'திங்கள்', 'செவ்வாய்', 'புதன்', 'வியாழன்', 'வெள்ளி', 'சனி'],
        ],
        'months' => [
            'si' => ['ජනවාරි', 'පෙබරවාරි', 'මාර්තු', 'අප්‍රේල්', 'මැයි', 'ජූනි', 'ජූලි', 'නිකිණි', 'සැප්තැම්බර්', 'ඔක්තෝබර්', 'නොවැම්බර්', 'දෙසැම්බර්'],
            'ta' => ['ஜனவரி', 'பிப்ரவரி', 'மார்ச்', 'ஏப்ரல்', 'மே', 'ஜூன்', 'ஜூலை', 'ஆகஸ்ட்', 'செப்டம்பர்', 'அக்டோபர்', 'நவம்பர்', 'டிசம்பர்'],
        ],
    ];
}
// AM/PM localized time map
function _invite_time_maps() {
    return [
        'si' => ['am' => 'පුර්වභාග', 'pm' => 'අපරභාග '],
        'ta' => ['am' => 'முற்பகல்', 'pm' => 'பிற்பகல்'],
    ];
}
// Full localized date. Sinhala uses a formal invitation-style order:
// "2026 ජූලි මස 15 වන බදාදා". Tamil/English keep "Weekday, DD Month YYYY".
function t_date($datetime) {
    $lang = $GLOBALS['__invite_lang'] ?? 'en';
    $ts = is_int($datetime) ? $datetime : strtotime($datetime);
    if ($ts === false) return '';
    if ($lang === 'en' || !in_array($lang, ['si', 'ta'], true)) {
        return date("l, d F Y", $ts);
    }
    $maps = _invite_date_maps();
    $day_name = $maps['days'][$lang][(int) date('w', $ts)];
    $month_name = $maps['months'][$lang][(int) date('n', $ts) - 1];
    if ($lang === 'si') {
        return date('Y', $ts) . ' ' . $month_name . ' මස ' . date('d', $ts) . ' වන ' . $day_name;
    }
    return $day_name . ', ' . date('d', $ts) . ' ' . $month_name . ' ' . date('Y', $ts);
}

// Just the month name, e.g. for small "15 / Aug" style markers
function t_month($datetime) {
    $lang = $GLOBALS['__invite_lang'] ?? 'en';
    $ts = is_int($datetime) ? $datetime : strtotime($datetime);
    if ($ts === false) return '';
    if ($lang === 'en' || !in_array($lang, ['si', 'ta'], true)) {
        return date('M', $ts);
    }
    $maps = _invite_date_maps();
    return $maps['months'][$lang][(int) date('n', $ts) - 1];
}
function t_time($datetime) {
    $lang = $GLOBALS['__invite_lang'] ?? 'en';
    $ts = is_int($datetime) ? $datetime : strtotime($datetime);
    if ($ts === false) return '';
    if ($lang === 'en' || !in_array($lang, ['si', 'ta'], true)) {
        return date("h:i A", $ts);
    }
    $maps = _invite_time_maps();
    $ampm = (date('A', $ts) === 'AM') ? $maps[$lang]['am'] : $maps[$lang]['pm'];
    return date('h:i', $ts) . ' ' . $ampm;
}
$LANG_STRINGS = [
    // ---- Gate / envelope page (invite.php) ----
    'the_wedding_of' => [
        'en' => 'The Wedding Of',
        'si' => 'අපගේ විවාහ මංගල්‍යය',
        'ta' => 'திருமணம்',
    ],
    'enter_whatsapp' => [
        'en' => 'Enter your WhatsApp number to open<br>your personal invitation',
        'si' => 'ඔබ වෙනුවෙන් වෙන් කළ ආරාධනා පත්‍රය විවෘත කිරීමට<br>WhatsApp අංකය ඇතුළත් කරන්න',
        'ta' => 'உங்கள் தனிப்பட்ட அழைப்பிதழைத் திறக்க<br>உங்கள் WhatsApp எண்ணை உள்ளிடவும்',
    ],
    'open_invitation_btn' => [
        'en' => 'Open My Invitation',
        'si' => 'මගේ ආරාධනා පත්‍රය විවෘත කරන්න',
        'ta' => 'எனது அழைப்பிதழைத் திறக்கவும்',
    ],
    'number_privacy_hint' => [
        'en' => "Your number is only used to show you your invitation. It's not shared with anyone.",
        'si' => 'ඔබගේ WhatsApp අංකය භාවිතා කරනු ලබන්නේ ඔබගේ ආරාධනා පත්‍රය පෙන්වීම සඳහා පමණි.',
        'ta' => 'உங்கள் எண் உங்கள் அழைப்பிதழைக் காட்ட மட்டுமே பயன்படுத்தப்படும். இது யாருடனும் பகிரப்படாது.',
    ],
    'tap_seal_to_open' => [
        'en' => 'Tap the seal to open',
        'si' => 'විවෘත කිරීමට මුද්‍රාව ස්පර්ශ කරන්න',
        'ta' => 'திறக்க முத்திரையைத் தொடவும்',
    ],
    'opening_invitation' => [
        'en' => 'Opening your invitation…',
        'si' => 'ඔබගේ ආරාධනා පත්‍රය සූදානම් කරමින්...',
        'ta' => 'உங்கள் அழைப்பிதழ் திறக்கப்படுகிறது…',
    ],
    'pending_save_the_date' => [
        'en' => 'Save The Date',
        'si' => 'දිනය මතක තබා ගන්න',
        'ta' => 'தேதியை குறித்து வையுங்கள்',
    ],
    'pending_title' => [
        'en' => 'Their invitation is being written',
        'si' => 'ඔබගේ ආරාධනා පත්‍රය සකස් කරමින් පවතී',
        'ta' => 'அவர்களின் அழைப்பிதழ் தயாராகி வருகிறது',
    ],
    'pending_desc' => [
        'en' => 'This wedding invitation is being carefully prepared.<br>Please check back shortly — it will be ready soon.',
        'si' => 'ඔබ වෙනුවෙන් මෙම ආරාධනා පත්‍රය සූදානම් කරමින් පවතී. කරුණාකර ටික වේලාවකින් නැවත පැමිණෙන්න.<br>කරුණාකර ටික වේලාවකින් නැවත පරීක්ෂා කරන්න — එය ඉක්මනින් සූදානම් වේ.',
        'ta' => 'இந்த திருமண அழைப்பிதழ் கவனமாக தயாரிக்கப்பட்டு வருகிறது.<br>சிறிது நேரத்தில் மீண்டும் பாருங்கள் — இது விரைவில் தயாராகும்.',
    ],
    'pending_badge' => [
        'en' => 'Being prepared',
        'si' => 'සූදානම් වෙමින් පවතී',
        'ta' => 'தயாராகி வருகிறது',
    ],
    'stage_you_are_invited' => [
        'en' => 'You Are Invited',
        'si' => 'ඔබට ආදරයෙන් ඇරයුම් කරමු',
        'ta' => 'நீங்கள் அழைக்கப்படுகிறீர்கள்',
    ],
    'envelope_dear_guest' => [
        'en' => 'Dear Guest',
        'si' => 'ආදරණීය ඔබ වෙත',
        'ta' => 'அன்புள்ள விருந்தினரே',
    ],

    // ---- Hero (invitation opening screen) ----
    'hero_eyebrow' => [
        'en' => "You're Warmly Invited",
        'si' => 'ආදරයෙන් ඇරයුම් කරමු',
        'ta' => 'உங்களை அன்புடன் அழைக்கிறோம்',
    ],
    'hero_dear' => [
        'en' => 'Dear',
        'si' => 'ආදරණීය',
        'ta' => 'அன்புள்ள',
    ],
    'hero_getting_married' => [
        'en' => "We're getting married",
        'si' => 'පගේ විවාහ මංගල්‍යයට ඔබගේ සම්භාවනීය පැමිණීම අපේක්ෂා කරමු',
        'ta' => 'நாங்கள் திருமணம் செய்கிறோம்',
    ],
    'hero_scroll_cue' => [
        'en' => 'Scroll',
        'si' => 'පහළට යන්න',
        'ta' => 'கீழே செல்லவும்',
    ],

    // ---- Countdown ----
    'countdown_label' => [
        'en' => 'Counting down to the big day',
        'si' => 'අපගේ විවාහ මංගල්‍යය ඇරඹෙන තෙක්',
        'ta' => 'சிறப்பு நாளுக்கான கவுண்ட்டவுன்',
    ],
    'cd_days' => ['en' => 'Days', 'si' => 'දින', 'ta' => 'நாட்கள்'],
    'cd_hours' => ['en' => 'Hours', 'si' => 'පැය', 'ta' => 'மணி'],
    'cd_mins' => ['en' => 'Minutes', 'si' => 'මිනිත්තු', 'ta' => 'நிமிடங்கள்'],
    'cd_secs' => ['en' => 'Seconds', 'si' => 'තත්පර', 'ta' => 'விநாடிகள்'],
    'just_married' => [
        'en' => 'Just Married! ❧',
        'si' => 'අපි විවාහ දිවියට පිවිසියෙමු! ❧',
        'ta' => 'இப்போதுதான் திருமணம்! ❧',
    ],

    // ---- Love story ----
    'love_story_tag' => [
        'en' => 'How It All Began',
        'si' => 'අපගේ ආදරයේ ඇරඹුම',
        'ta' => 'எங்களின் அன்புப் பயணம்',
    ],
    'love_story_title' => [
        'en' => 'Our <em>Love Story</em>',
        'si' => 'අපගේ <em>ආදර කතාව</em>',
        'ta' => 'எங்கள் <em>காதல் கதை</em>',
    ],

    // ---- Programme ----
    'programme_tag' => [
        'en' => 'Join Us For These Celebrations',
        'si' => 'අපගේ මංගල උත්සවයේ මෙම අවස්ථාවන් සඳහා එක්වන්න',
        'ta' => 'இந்த விழாக்களில் எங்களுடன் இணையுங்கள்',
    ],
    'programme_title' => [
        'en' => '<em>Wedding</em> Programme',
        'si' => '<em>මංගල</em> උත්සව වැඩසටහන',
        'ta' => '<em>திருமண</em> நிகழ்ச்சி நிரல்',
    ],
    'event_details_soon' => [
        'en' => 'Event details will be updated soon.',
        'si' => 'උත්සවයේ විස්තර ඉක්මනින් ප්‍රකාශයට පත් කෙරේ.',
        'ta' => 'நிகழ்வு விவரங்கள் விரைவில் புதுப்பிக்கப்படும்.',
    ],
    'get_directions' => [
        'en' => 'Get Directions',
        'si' => 'ස්ථානය බලා යන්න',
        'ta' => 'வழி காட்டு',
    ],
    'add_to_calendar' => [
        'en' => 'Add to Calendar',
        'si' => 'දින දර්ශනයට එක් කරන්න',
        'ta' => 'நாட்காட்டியில் சேர்க்க',
    ],

    // ---- Gallery ----
    'gallery_tag' => [
        'en' => 'Our Engagement Memories',
        'si' => 'අපගේ සුන්දර මතකයන්',
        'ta' => 'எங்கள் நிச்சயதார்த்த நினைவுகள்',
    ],
    'gallery_title' => [
        'en' => '<em>Sweet</em> Moments',
        'si' => '<em>අමතක නොවන</em> මතකයන්',
        'ta' => '<em>இனிமையான</em> தருணங்கள்',
    ],
    'guest_gallery_title' => [
        'en' => '<em>Guest</em> Shared Moments',
        'si' => '<em>ඔබ සැම බෙදාගත්</em> මතකයන්',
        'ta' => '<em>விருந்தினர்</em> பகிர்ந்த தருணங்கள்',
    ],
    'upload_photo' => [
        'en' => 'Share a Photo',
        'si' => 'ඡායාරූපයක් බෙදාගන්න',
        'ta' => 'புகைப்படத்தைப் பகிரவும்',
    ],
    'share_photo_heading' => [
        'en' => 'Share a Photo from Your Phone',
        'si' => 'ඔබගේ දුරකථනයෙන් ඡායාරූපයක් බෙදාගන්න',
        'ta' => 'உங்கள் மொபைலில் இருந்து ஒரு புகைப்படத்தைப் பகிரவும்',
    ],
    'share_photo_desc' => [
        'en' => 'Did you take some candid photos of the couple? Upload them here to share with everyone!',
        'si' => 'ඔබ ලබාගත් සුන්දර ඡායාරූප අප සමඟ බෙදාගන්න!',
        'ta' => 'ஜோடியின் இயல்பான புகைப்படங்களை எடுத்தீர்களா? எல்லோருடனும் பகிர இங்கே பதிவேற்றவும்!',
    ],
    'upload_wedding_photo_btn' => [
        'en' => 'Upload Wedding Photo',
        'si' => 'විවාහ ඡායාරූපය උඩුගත කරන්න',
        'ta' => 'திருமண புகைப்படத்தைப் பதிவேற்றவும்',
    ],
    'upload_disabled_preview' => [
        'en' => 'Photo upload is disabled in Preview Mode.',
        'si' => 'පෙරදසුන් ප්‍රකාරයේදී ඡායාරූප උඩුගත කිරීම අක්‍රියයි.',
        'ta' => 'முன்னோட்ட முறையில் புகைப்பட பதிவேற்றம் முடக்கப்பட்டுள்ளது.',
    ],
    'preview_mode_label' => [
        'en' => 'PREVIEW MODE',
        'si' => 'පෙරදසුන් ප්‍රකාරය',
        'ta' => 'முன்னோட்ட முறை',
    ],
    'preview_mode_note' => [
        'en' => 'This is how your guests will see the invitation.',
        'si' => 'ඔබේ අමුත්තන්ට ආරාධනාව පෙනෙන්නේ මෙසේය.',
        'ta' => 'உங்கள் விருந்தினர்கள் இந்த அழைப்பிதழை இவ்வாறு காண்பார்கள்.',
    ],
    'back_to_dashboard' => [
        'en' => '← Back to Dashboard',
        'si' => '← පාලක පුවරුව වෙත ආපසු',
        'ta' => '← டாஷ்போர்டுக்குத் திரும்பு',
    ],

    // ---- RSVP ----
    'rsvp_quote' => [
        'en' => 'Every love story is beautiful, but ours is our favorite. Come celebrate this new chapter with us.',
        'si' => 'අපගේ ජීවිතයේ මෙම සුවිශේෂී දිනය ඔබගේ පැමිණීමෙන් තවත් අලංකාර වේ.',
        'ta' => 'ஒவ்வொரு காதல் கதையும் அழகானது, ஆனால் எங்களுடையது எங்களுக்குப் பிடித்தது. இந்தப் புதிய அத்தியாயத்தை எங்களுடன் கொண்டாட வாருங்கள்.',
    ],
    'rsvp_title' => [
        'en' => 'RSVP',
        'si' => 'ඔබගේ සහභාගිත්වය තහවුරු කරන්න',
        'ta' => 'வருகையை உறுதிசெய்க',
    ],
    'rsvp_subtitle' => [
        'en' => 'Will you be joining us?',
        'si' => 'ඔබ සහභාගී වන්නේදැයි කරුණාකර දැනුම් දෙන්න.',
        'ta' => 'நீங்கள் எங்களுடன் இணைவீர்களா?',
    ],
    'rsvp_accept' => [
        'en' => 'Joyfully Accept',
        'si' => 'සතුටින් සහභාගී වන්නෙමි',
        'ta' => 'மகிழ்ச்சியுடன் ஏற்கிறேன்',
    ],
    'rsvp_decline' => [
        'en' => 'Regretfully Decline',
        'si' => 'කනගාටුවෙන් සහභාගී විය නොහැක',
        'ta' => 'வருத்தத்துடன் மறுக்கிறேன்',
    ],
    'rsvp_note_placeholder' => [
        'en' => 'Leave a note for the couple (optional)',
        'si' => 'අලුත් යුවළ වෙනුවෙන් සුබ පැතුමක් තබන්න (අවශ්‍ය නම්)',
        'ta' => 'தம்பதியருக்கு ஒரு குறிப்பை விடுங்கள் (விரும்பினால்)',
    ],
    'rsvp_submit' => [
        'en' => 'Send My RSVP',
        'si' => 'මගේ පිළිතුර යවන්න',
        'ta' => 'எனது பதிலை அனுப்பவும்',
    ],
    'seats_reserved' => [
        'en' => 'Seats Reserved',
        'si' => 'ඔබ වෙනුවෙන් වෙන්කර ඇති ආසන',
        'ta' => 'ஒதுக்கப்பட்ட இருக்கைகள்',
    ],
];