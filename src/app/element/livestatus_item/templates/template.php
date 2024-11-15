<?php

// Helper functions
if (!function_exists('getPlatformUrl')) {
    function getPlatformUrl($platform, $username) {
        if (empty($platform) || empty($username)) {
            error_log("LiveStatus: Empty platform or username in getPlatformUrl");
            return '#';
        }

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
                error_log("LiveStatus: Unknown platform '{$platform}' in getPlatformUrl");
                return '#';
        }
    }
}

if (!function_exists('getPlatformColors')) {
    function getPlatformColors($platform) {
        if (empty($platform)) {
            error_log("LiveStatus: Empty platform in getPlatformColors");
            return [
                'rgba(255, 255, 255, 1)',
                'rgba(200, 200, 200, 0.8)',
                'rgba(150, 150, 150, 0.8)'
            ];
        }

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
                error_log("LiveStatus: Unknown platform '{$platform}' in getPlatformColors");
                return [
                    'rgba(255, 255, 255, 1)',
                    'rgba(200, 200, 200, 0.8)',
                    'rgba(150, 150, 150, 0.8)'
                ];
        }
    }
}

if (!function_exists('getPlatformIcon')) {
    function getPlatformIcon($platform) {
        switch (strtolower($platform)) {
            case 'tiktok':
                return 'tiktok';  // UIkit has tiktok icon
            case 'youtube':
                return 'youtube';
            case 'twitch':
                return 'twitch';  // UIkit has twitch icon
            case 'facebook':
                return 'facebook';
            case 'instagram':
                return 'instagram';
            case 'kick':
                return 'play-circle';  // Best approximation for Kick
            default:
                return 'question';
        }
    }
}

// Get platform data from node props
$data = $props['platformData'] ?? [];
$platform = strtolower($props['platform'] ?? 'tiktok');
$username = $props['username'] ?? '';
$animated = $props['animated_bg'] ?? false;
$show_icon = $props['show_icon'] ?? true;

// Get parent element props
$element = $props['element'] ?? [];

// Debug logging
error_log("LiveStatus item - Full props: " . print_r($props, true));
error_log("LiveStatus item - Element props: " . print_r($element, true));

// Get size from parent element
$size = '';
if (isset($element['size']) && !empty($element['size'])) {
    $size = $element['size'];
} elseif (isset($props['size']) && !empty($props['size'])) {
    $size = $props['size'];
}

error_log("LiveStatus item - Final size value: '{$size}'");

$colors = getPlatformColors($platform);
$uniqueId = uniqid('livestatus-');

// Build element container
$el = $this->el('div', [
    'class' => [
        'el-livestatus-item',
        'livestatus-' . $uniqueId
    ]
]);

?>

<style>
.livestatus-<?= $uniqueId ?> {
    display: inline-flex;
    align-items: center;
    justify-content: flex-start;
}

.livestatus-<?= $uniqueId ?> a {
    text-decoration: none;
    display: inline-flex;
    align-items: center;
}

.livestatus-<?= $uniqueId ?> .uk-label {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    position: relative;
    overflow: hidden;
    padding: 5px 10px;
    border-radius: 3px;
    font-size: 12px;
    text-transform: uppercase;
    transition: all 0.2s ease;
}

/* Small size */
.livestatus-<?= $uniqueId ?> .uk-label.uk-label-small {
    padding: 2px 8px;
    font-size: 11px;
}
.livestatus-<?= $uniqueId ?> .uk-label.uk-label-small .label-content [uk-icon] {
    width: 14px;
    height: 14px;
}

/* Large size */
.livestatus-<?= $uniqueId ?> .uk-label.uk-label-large {
    padding: 7px 14px;
    font-size: 14px;
}
.livestatus-<?= $uniqueId ?> .uk-label.uk-label-large .label-content [uk-icon] {
    width: 18px;
    height: 18px;
}

.livestatus-<?= $uniqueId ?> .uk-label .label-content {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    position: relative;
    z-index: 2;
}

.livestatus-<?= $uniqueId ?> .uk-label .label-content [uk-icon] {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 16px;
    height: 16px;
}

.livestatus-<?= $uniqueId ?> .uk-label.is-live[data-platform="tiktok"] {
    background: #25F4EE;
    color: #000;
}

.livestatus-<?= $uniqueId ?> .uk-label.is-live[data-platform="youtube"] {
    background: #c4302b;
}

.livestatus-<?= $uniqueId ?> .uk-label.is-live[data-platform="twitch"] {
    background: #9147ff;
}

.livestatus-<?= $uniqueId ?> .uk-label.is-live[data-platform="facebook"] {
    background: #1877f2;
}

.livestatus-<?= $uniqueId ?> .uk-label.is-live[data-platform="instagram"] {
    background: #c13584;
}

.livestatus-<?= $uniqueId ?> .uk-label.is-live[data-platform="kick"] {
    background: #53fc18;
    color: #000;
}

.livestatus-<?= $uniqueId ?> .uk-label.is-live.animated-bg {
    z-index: 1;
}

.livestatus-<?= $uniqueId ?> .uk-label.is-live.animated-bg .label-content {
    position: relative;
    z-index: 3;
    text-shadow: 0 0 1px rgba(0,0,0,0.3);
    mix-blend-mode: normal;
}

.livestatus-<?= $uniqueId ?> .uk-label.is-live.animated-bg .animated-background {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 1;
    mix-blend-mode: screen;
    opacity: 1;
}

.livestatus-<?= $uniqueId ?> .uk-label.has-error {
    background: #f0506e;
    color: #fff;
}
</style>

<?= $el($props, $attrs) ?>
    <?php if (!isset($data['error'])) : ?>
        <a href="<?= htmlspecialchars(getPlatformUrl($platform, $username)) ?>" target="_blank" rel="noopener">
            <span class="uk-label <?= ($data['live'] ?? false) ? 'is-live' : '' ?> <?= ($animated && ($data['live'] ?? false)) ? 'animated-bg' : '' ?> <?= $size ? "uk-label-{$size}" : '' ?>" data-platform="<?= $platform ?>">
                <?php if ($animated && ($data['live'] ?? false)) : ?>
                    <svg class="animated-background" viewBox="0 0 100 100" preserveAspectRatio="none">
                        <defs>
                            <radialGradient id="Gradient1-<?= $uniqueId ?>" cx="50%" cy="50%" fx="0.441602%" fy="50%" r=".7">
                                <animate attributeName="fx" dur="12s" values="0%;5%;0%" repeatCount="indefinite"></animate>
                                <stop offset="0%" stop-color="<?= $colors[0] ?>"></stop>
                                <stop offset="100%" stop-color="<?= $colors[0] ?>" stop-opacity="0"></stop>
                            </radialGradient>
                            <radialGradient id="Gradient2-<?= $uniqueId ?>" cx="50%" cy="50%" fx="2.68147%" fy="50%" r=".7">
                                <animate attributeName="fx" dur="8s" values="0%;5%;0%" repeatCount="indefinite"></animate>
                                <stop offset="0%" stop-color="<?= $colors[1] ?>"></stop>
                                <stop offset="100%" stop-color="<?= $colors[1] ?>" stop-opacity="0"></stop>
                            </radialGradient>
                            <radialGradient id="Gradient3-<?= $uniqueId ?>" cx="50%" cy="50%" fx="0.836536%" fy="50%" r=".7">
                                <animate attributeName="fx" dur="10s" values="0%;5%;0%" repeatCount="indefinite"></animate>
                                <stop offset="0%" stop-color="<?= $colors[2] ?>"></stop>
                                <stop offset="100%" stop-color="<?= $colors[2] ?>" stop-opacity="0"></stop>
                            </radialGradient>
                        </defs>
                        <rect x="13.744%" y="1.18473%" width="100%" height="100%" fill="url(#Gradient1-<?= $uniqueId ?>)" transform="rotate(334.41 50 50)">
                            <animate attributeName="x" dur="10s" values="25%;0%;25%" repeatCount="indefinite"></animate>
                            <animate attributeName="y" dur="11s" values="0%;25%;0%" repeatCount="indefinite"></animate>
                            <animateTransform attributeName="transform" type="rotate" from="0 50 50" to="360 50 50" dur="5s" repeatCount="indefinite"></animateTransform>
                        </rect>
                        <rect x="-2.17916%" y="35.4267%" width="100%" height="100%" fill="url(#Gradient2-<?= $uniqueId ?>)" transform="rotate(255.072 50 50)">
                            <animate attributeName="x" dur="13s" values="-25%;0%;-25%" repeatCount="indefinite"></animate>
                            <animate attributeName="y" dur="14s" values="0%;50%;0%" repeatCount="indefinite"></animate>
                            <animateTransform attributeName="transform" type="rotate" from="0 50 50" to="360 50 50" dur="7s" repeatCount="indefinite"></animateTransform>
                        </rect>
                        <rect x="9.00483%" y="14.5733%" width="100%" height="100%" fill="url(#Gradient3-<?= $uniqueId ?>)" transform="rotate(139.903 50 50)">
                            <animate attributeName="x" dur="15s" values="0%;25%;0%" repeatCount="indefinite"></animate>
                            <animate attributeName="y" dur="8s" values="0%;25%;0%" repeatCount="indefinite"></animate>
                            <animateTransform attributeName="transform" type="rotate" from="360 50 50" to="0 50 50" dur="6s" repeatCount="indefinite"></animateTransform>
                        </rect>
                    </svg>
                <?php endif ?>
                <span class="label-content">
                    <?php if ($show_icon) : ?>
                        <span uk-icon="icon: <?= getPlatformIcon($platform) ?>"></span>
                    <?php endif ?>
                    <?= htmlspecialchars(($data['live'] ?? false) ? $props['live_text'] : $props['offline_text']) ?>
                </span>
            </span>
        </a>
    <?php else : ?>
        <div class="el-livestatus-error">
            <?= $data['error'] ?>
        </div>
    <?php endif ?>
<?= $el->end() ?>
