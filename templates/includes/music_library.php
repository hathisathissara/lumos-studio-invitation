<?php

// Works whether the app lives at the domain root (yourdomain.com) or a subfolder
// (e.g. localhost/invite). Change the path below to match your actual project root.
if (!defined('MUSIC_BASE_URL')) {
    define('MUSIC_BASE_URL', '/invite/templates/includes/assets/music/');
}

$MUSIC_LIBRARY = [
    'romantic_piano' => [
        'label' => 'Romantic Piano',
        'file'  => MUSIC_BASE_URL . 'romantic_piano.mp3',
    ],
    'acoustic_love' => [
        'label' => 'Acoustic Love',
        'file'  => MUSIC_BASE_URL . 'acoustic_love.mp3',
    ],
    'soft_strings' => [
        'label' => 'Soft Strings',
        'file'  => MUSIC_BASE_URL . 'soft_strings.mp3',
    ],
    'gentle_piano_two' => [
        'label' => 'Gentle Piano II',
        'file'  => MUSIC_BASE_URL . 'gentle_piano_two.mp3',
    ],
    'elegant_violin' => [
        'label' => 'Elegant Violin',
        'file'  => MUSIC_BASE_URL . 'elegant_violin.mp3',
    ],
];