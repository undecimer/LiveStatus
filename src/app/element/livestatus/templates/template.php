<?php

namespace YOOtheme\LiveStatus\Element;

use YOOtheme\LiveStatus\Element\LiveStatus\Platforms\Platform;
use YOOtheme\LiveStatus\Element\LiveStatus\Platforms\TikTok;
use YOOtheme\LiveStatus\Element\LiveStatus\Platforms\Twitch;
use YOOtheme\LiveStatus\Element\LiveStatus\Platforms\YouTube;
use YOOtheme\LiveStatus\Element\LiveStatus\Platforms\FacebookLive;
use YOOtheme\LiveStatus\Element\LiveStatus\Platforms\InstagramLive;
use YOOtheme\LiveStatus\Element\LiveStatus\Platforms\Kick;
use YOOtheme\LiveStatus\Cache\CacheManager;
use YOOtheme\LiveStatus\RateLimit\RateLimitManager;

function getPlatformUrl($platform, $username) {
    switch (strtolower($platform)) {
        case 'tiktok':
            return "https://www.tiktok.com/@{$username}";
        case 'youtube':
            return "https://www.youtube.com/@{$username}";
        case 'twitch':
            return "https://www.twitch.tv/{$username}";
        case 'facebook':
            return "https://www.facebook.com/{$username}/live";
        case 'instagram':
            return "https://www.instagram.com/{$username}";
        case 'kick':
            return "https://kick.com/{$username}";
        default:
            return '#';
    }
}

function getPlatformColors($platform) {
    switch (strtolower($platform)) {
        case 'tiktok':
            return [
                'rgba(37, 244, 238, 1)',    // Bright teal
                'rgba(254, 44, 85, 1)',     // Hot pink
                'rgba(254, 44, 85, 0.8)'    // Semi-transparent pink
            ];
        case 'youtube':
            return [
                'rgba(255, 0, 0, 1)',       // Pure red
                'rgba(255, 255, 255, 0.8)', // White glow
                'rgba(255, 0, 0, 0.8)'      // Semi-transparent red
            ];
        case 'twitch':
            return [
                'rgba(145, 71, 255, 1)',    // Twitch purple
                'rgba(255, 255, 255, 0.8)', // White glow
                'rgba(188, 137, 255, 0.8)'  // Light purple
            ];
        case 'facebook':
            return [
                'rgba(24, 119, 242, 1)',    // Facebook blue
                'rgba(255, 255, 255, 0.8)', // White glow
                'rgba(66, 183, 255, 0.8)'   // Light blue
            ];
        case 'instagram':
            return [
                'rgba(225, 48, 108, 1)',    // Instagram pink
                'rgba(255, 220, 128, 0.8)', // Golden yellow
                'rgba(131, 58, 180, 0.8)'   // Purple
            ];
        case 'kick':
            return [
                'rgba(83, 252, 24, 1)',     // Bright green
                'rgba(255, 255, 255, 0.8)', // White glow
                'rgba(162, 255, 128, 0.8)'  // Light green
            ];
        default:
            return [
                'rgba(255, 255, 255, 1)',
                'rgba(200, 200, 200, 0.8)',
                'rgba(150, 150, 150, 0.8)'
            ];
    }
}

// Build element container
$el = $this->el('div', [
    'class' => [
        'el-livestatus',
        'uk-text-{alignment}'
    ]
]);

// Get platform data from node props
$data = $props['platformData'] ?? [];
$platform = strtolower($props['platform'] ?? 'tiktok');
$username = $props['username'] ?? '';
$animated = $props['animated_bg'] ?? false;

error_log("Template data: " . print_r($data, true));
error_log("Template platform: {$platform}");
error_log("Template username: {$username}");
error_log("Template props: " . print_r($props, true));

$colors = getPlatformColors($platform);
?>

<style>
.el-livestatus {
    display: block;
    width: 100%;
}

.el-livestatus.uk-text-center {
    text-align: center;
}

.el-livestatus.uk-text-right {
    text-align: right;
}

.el-livestatus a {
    text-decoration: none;
    display: inline-block;
}

.el-livestatus a:hover .uk-label {
    filter: brightness(90%);
}

.el-livestatus .uk-label {
    display: inline-flex;
    align-items: center;
    gap: 0.4em;
    transition: filter 0.2s ease;
    position: relative;
    overflow: hidden;
    line-height: 1;
}

.el-livestatus .uk-label.uk-label-large {
    padding: 8px 16px;
    font-size: 1.1rem;
}

.el-livestatus .uk-label.uk-label-large .label-content [uk-icon] {
    width: 24px;
    height: 24px;
}

.el-livestatus .uk-label .label-content {
    display: inline-flex;
    align-items: center;
    gap: 0.4em;
    line-height: 1;
}

.el-livestatus .uk-label .label-content [uk-icon] {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 16px;
    height: 16px;
    margin-top: -1px;
}

.el-livestatus .uk-label.is-live[data-platform="tiktok"] {
    background: #25F4EE;
    color: #000;
}

.el-livestatus .uk-label.is-live[data-platform="youtube"] {
    background: #c4302b;
}

.el-livestatus .uk-label.is-live[data-platform="twitch"] {
    background: #9147ff;
}

.el-livestatus .uk-label.is-live[data-platform="facebook"] {
    background: #1877f2;
}

.el-livestatus .uk-label.is-live[data-platform="instagram"] {
    background: #c13584;
}

.el-livestatus .uk-label.is-live[data-platform="kick"] {
    background: #53fc18;
    color: #000;
}

.el-livestatus .uk-label.is-live.animated-bg {
    z-index: 1;
}

.el-livestatus .uk-label.is-live.animated-bg .label-content {
    position: relative;
    z-index: 3;
    text-shadow: 0 0 1px rgba(0,0,0,0.3);
    mix-blend-mode: normal;
}

.el-livestatus .uk-label.is-live.animated-bg.uk-label-large .label-content [uk-icon] {
    filter: drop-shadow(0 0 1px rgba(0,0,0,0.3));
}

.el-livestatus .uk-label.is-live.animated-bg .animated-background {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 2;
    mix-blend-mode: screen;
    opacity: 1;
}

.el-livestatus .el-livestatus-error {
    color: #ff3545;
    font-style: italic;
}
</style>

<?= $el($props, $attrs) ?>
    <?php if (!isset($data['error'])) : ?>
        <?php if ($props['show_offline'] || ($data['live'] ?? false)) : ?>
            <?php
                error_log("Template rendering label - Live: " . (($data['live'] ?? false) ? 'true' : 'false'));
                error_log("Template show_offline: " . ($props['show_offline'] ? 'true' : 'false'));
            ?>
            <a href="<?= htmlspecialchars(getPlatformUrl($platform, $username)) ?>" target="_blank" rel="noopener">
                <span class="uk-label <?= ($data['live'] ?? false) ? 'is-live' : '' ?> <?= ($animated && ($data['live'] ?? false)) ? 'animated-bg' : '' ?> <?= $props['size'] ? "uk-label-{$props['size']}" : '' ?>" data-platform="<?= $platform ?>">
                    <?php if ($animated && ($data['live'] ?? false)) : ?>
                        <svg class="animated-background" viewBox="0 0 100 100" preserveAspectRatio="none">
                            <defs>
                                <radialGradient id="Gradient1-<?= $platform ?>" cx="50%" cy="50%" fx="0.441602%" fy="50%" r=".7">
                                    <animate attributeName="fx" dur="12s" values="0%;5%;0%" repeatCount="indefinite"></animate>
                                    <stop offset="0%" stop-color="<?= $colors[0] ?>"></stop>
                                    <stop offset="100%" stop-color="<?= $colors[0] ?>" stop-opacity="0"></stop>
                                </radialGradient>
                                <radialGradient id="Gradient2-<?= $platform ?>" cx="50%" cy="50%" fx="2.68147%" fy="50%" r=".7">
                                    <animate attributeName="fx" dur="8s" values="0%;5%;0%" repeatCount="indefinite"></animate>
                                    <stop offset="0%" stop-color="<?= $colors[1] ?>"></stop>
                                    <stop offset="100%" stop-color="<?= $colors[1] ?>" stop-opacity="0"></stop>
                                </radialGradient>
                                <radialGradient id="Gradient3-<?= $platform ?>" cx="50%" cy="50%" fx="0.836536%" fy="50%" r=".7">
                                    <animate attributeName="fx" dur="10s" values="0%;5%;0%" repeatCount="indefinite"></animate>
                                    <stop offset="0%" stop-color="<?= $colors[2] ?>"></stop>
                                    <stop offset="100%" stop-color="<?= $colors[2] ?>" stop-opacity="0"></stop>
                                </radialGradient>
                            </defs>
                            <rect x="13.744%" y="1.18473%" width="100%" height="100%" fill="url(#Gradient1-<?= $platform ?>)" transform="rotate(334.41 50 50)">
                                <animate attributeName="x" dur="10s" values="25%;0%;25%" repeatCount="indefinite"></animate>
                                <animate attributeName="y" dur="11s" values="0%;25%;0%" repeatCount="indefinite"></animate>
                                <animateTransform attributeName="transform" type="rotate" from="0 50 50" to="360 50 50" dur="5s" repeatCount="indefinite"></animateTransform>
                            </rect>
                            <rect x="-2.17916%" y="35.4267%" width="100%" height="100%" fill="url(#Gradient2-<?= $platform ?>)" transform="rotate(255.072 50 50)">
                                <animate attributeName="x" dur="13s" values="-25%;0%;-25%" repeatCount="indefinite"></animate>
                                <animate attributeName="y" dur="14s" values="0%;50%;0%" repeatCount="indefinite"></animate>
                                <animateTransform attributeName="transform" type="rotate" from="0 50 50" to="360 50 50" dur="7s" repeatCount="indefinite"></animateTransform>
                            </rect>
                            <rect x="9.00483%" y="14.5733%" width="100%" height="100%" fill="url(#Gradient3-<?= $platform ?>)" transform="rotate(139.903 50 50)">
                                <animate attributeName="x" dur="15s" values="0%;25%;0%" repeatCount="indefinite"></animate>
                                <animate attributeName="y" dur="8s" values="0%;25%;0%" repeatCount="indefinite"></animate>
                                <animateTransform attributeName="transform" type="rotate" from="360 50 50" to="0 50 50" dur="6s" repeatCount="indefinite"></animateTransform>
                            </rect>
                        </svg>
                    <?php endif ?>
                    <span class="label-content">
                        <?php if (isset($props['show_icon']) && $props['show_icon']) : ?>
                            <span uk-icon="icon: <?= $platform ?>"></span>
                        <?php endif ?>
                        <?= htmlspecialchars(($data['live'] ?? false) ? $props['live_text'] : $props['offline_text']) ?>
                    </span>
                </span>
            </a>
        <?php endif ?>
    <?php else : ?>
        <div class="el-livestatus-error">
            <?= $data['error'] ?>
        </div>
    <?php endif ?>
<?= $el->end() ?>
