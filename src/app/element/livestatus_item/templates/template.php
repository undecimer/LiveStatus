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

if (!function_exists('getPlatformGradient')) {
    function getPlatformGradient($platform) {
        switch (strtolower($platform)) {
            case 'tiktok':
                return 'linear-gradient(-45deg, #00f2fe, #fe2c55, #25f4ee, #fe2c55, #00f2fe)';
            case 'youtube':
                return 'linear-gradient(-45deg, #ff0000, #ff5757, #cc0000, #ff5757, #ff0000)';
            case 'twitch':
                return 'linear-gradient(-45deg, #9147ff, #df80ff, #772ce8, #df80ff, #9147ff)';
            case 'facebook':
                return 'linear-gradient(-45deg, #1877f2, #00c6ff, #4267b2, #00c6ff, #1877f2)';
            case 'instagram':
                return 'linear-gradient(-45deg, #405de6, #c13584, #fd1d1d, #fcaf45, #fd1d1d, #c13584, #405de6)';
            case 'kick':
                return 'linear-gradient(-45deg, #53fc18, #00ff87, #24bd04, #00ff87, #53fc18)';
            default:
                return 'linear-gradient(-45deg, #e5e5e5, #ffffff, #888888, #ffffff, #e5e5e5)';
        }
    }
}

if (!function_exists('getPlatformShadowColor')) {
    function getPlatformShadowColor($platform) {
        switch (strtolower($platform)) {
            case 'tiktok':
                return 'rgba(37, 244, 238, 0.65)';
            case 'youtube':
                return 'rgba(255, 0, 0, 0.65)';
            case 'twitch':
                return 'rgba(145, 71, 255, 0.65)';
            case 'facebook':
                return 'rgba(24, 119, 242, 0.65)';
            case 'instagram':
                return 'rgba(225, 48, 108, 0.65)';
            case 'kick':
                return 'rgba(83, 252, 24, 0.65)';
            default:
                return 'rgba(255, 255, 255, 0.3)';
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
$show_offline = $props['show_offline'] ?? true;
$animated = $props['animated_bg'] ?? false;
$show_icon = $props['show_icon'] ?? true;
$size = $props['size'] ?? '';

// Debug logging
error_log("LiveStatus item - Full props: " . print_r($props, true));
error_log("LiveStatus item - Size value: '{$size}'");
error_log("LiveStatus item - Show offline: " . ($show_offline ? 'true' : 'false'));
error_log("LiveStatus item - Is live: " . (($data['live'] ?? false) ? 'true' : 'false'));

// Don't render if offline and show_offline is false
if (!($data['live'] ?? false) && !$show_offline) {
    return;
}

$colors = getPlatformColors($platform);
$uniqueId = uniqid('livestatus-');

// Compute deterministic unique speeds and offsets for the animations based on the username & platform
$hashVal = crc32($username . $platform);
$flowSpeed = 8 + abs($hashVal % 8);            // 8s to 16s
$flowDelay = -1 * abs(($hashVal >> 4) % 20);   // -0s to -20s
$pulseSpeed = 3 + abs(($hashVal >> 8) % 3);    // 3s to 5s
$pulseDelay = -1 * abs(($hashVal >> 12) % 6);  // -0s to -6s

// Build element container
$el = $this->el('div', [
    'class' => [
        'el-livestatus-item',
        'livestatus-' . $uniqueId
    ]
]);

?>

<style>
/* Base styles */
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

/* Label content */
.livestatus-<?= $uniqueId ?> .uk-label {
    position: relative;
    overflow: hidden;
}

.livestatus-<?= $uniqueId ?> .uk-label .label-content {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    position: relative;
    z-index: 2;
}

/* Size variants */
.livestatus-<?= $uniqueId ?> .uk-label.ls-size-small {
    padding: 0 8px;
    font-size: 0.75rem;
}

.livestatus-<?= $uniqueId ?> .uk-label.ls-size-small .label-content [uk-icon] {
    width: 14px;
    height: 14px;
}

.livestatus-<?= $uniqueId ?> .uk-label.ls-size-large {
    padding: 8px 16px;
    font-size: 1rem;
}

.livestatus-<?= $uniqueId ?> .uk-label.ls-size-large .label-content [uk-icon] {
    width: 20px;
    height: 20px;
}

/* Animated background - colorful moving layer */
.livestatus-<?= $uniqueId ?> .uk-label.animated-bg .animated-background {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 1;
    background: <?= getPlatformGradient($platform) ?>;
    background-size: 300% 300%;
    opacity: 1;
    mix-blend-mode: normal;
    animation: flow-<?= $uniqueId ?> <?= $flowSpeed ?>s ease-in-out infinite;
    animation-delay: <?= $flowDelay ?>s;
}

/* Gradient flow keyframes */
@keyframes flow-<?= $uniqueId ?> {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

/* Platform-specific styles */
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

/* High-end glassmorphism overlay for animated background items */
.livestatus-<?= $uniqueId ?> .uk-label.is-live.animated-bg {
    z-index: 1;
    background: transparent !important;
    color: #ffffff !important;
    border: 1px solid rgba(255, 255, 255, 0.35) !important;
    backdrop-filter: blur(4px);
    -webkit-backdrop-filter: blur(4px);
    font-weight: 700;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    animation: pulse-<?= $uniqueId ?> <?= $pulseSpeed ?>s ease-in-out infinite !important;
    animation-delay: <?= $pulseDelay ?>s !important;
}

/* Pulsing outer shadow keyframes with slight scale shift for high-end feel */
@keyframes pulse-<?= $uniqueId ?> {
    0% {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25), 0 0 12px <?= getPlatformShadowColor($platform) ?>, inset 0 1px 2px rgba(255, 255, 255, 0.35) !important;
        transform: scale(1);
    }
    50% {
        box-shadow: 0 6px 24px rgba(0, 0, 0, 0.35), 0 0 24px <?= getPlatformShadowColor($platform) ?>, inset 0 1px 3px rgba(255, 255, 255, 0.5) !important;
        transform: scale(1.02);
    }
    100% {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25), 0 0 12px <?= getPlatformShadowColor($platform) ?>, inset 0 1px 2px rgba(255, 255, 255, 0.35) !important;
        transform: scale(1);
    }
}

.livestatus-<?= $uniqueId ?> .uk-label.is-live.animated-bg[data-platform="tiktok"] {
    border-color: rgba(37, 244, 238, 0.6) !important;
}
.livestatus-<?= $uniqueId ?> .uk-label.is-live.animated-bg[data-platform="youtube"] {
    border-color: rgba(255, 0, 0, 0.6) !important;
}
.livestatus-<?= $uniqueId ?> .uk-label.is-live.animated-bg[data-platform="twitch"] {
    border-color: rgba(145, 71, 255, 0.6) !important;
}
.livestatus-<?= $uniqueId ?> .uk-label.is-live.animated-bg[data-platform="facebook"] {
    border-color: rgba(24, 119, 242, 0.6) !important;
}
.livestatus-<?= $uniqueId ?> .uk-label.is-live.animated-bg[data-platform="instagram"] {
    border-color: rgba(225, 48, 108, 0.6) !important;
}
.livestatus-<?= $uniqueId ?> .uk-label.is-live.animated-bg[data-platform="kick"] {
    border-color: rgba(83, 252, 24, 0.6) !important;
}

.livestatus-<?= $uniqueId ?> .uk-label.is-live.animated-bg .label-content {
    position: relative;
    z-index: 2;
    color: #ffffff !important;
    text-shadow: 0 1px 3px rgba(0, 0, 0, 0.8), 0 0 2px rgba(0, 0, 0, 0.9) !important;
}

.livestatus-<?= $uniqueId ?> .uk-label.has-error {
    background: #f0506e;
    color: #fff;
}
</style>

<?= $el($props, $attrs) ?>
    <?php if (!isset($data['error'])) : ?>
        <a href="<?= htmlspecialchars(getPlatformUrl($platform, $username)) ?>" target="_blank" rel="noopener">
            <span class="uk-label <?= ($data['live'] ?? false) ? 'is-live' : '' ?> <?= ($animated && ($data['live'] ?? false)) ? 'animated-bg' : '' ?> <?= $size ? "ls-size-{$size}" : '' ?>" data-platform="<?= $platform ?>">
                <?php if ($animated && ($data['live'] ?? false)) : ?>
                    <span class="animated-background"></span>
                <?php endif; ?>
                <span class="label-content">
                    <?php if ($show_icon) : ?>
                        <span uk-icon="icon: <?= getPlatformIcon($platform) ?>"></span>
                    <?php endif; ?>
                    <?= ($data['live'] ?? false) ? ($props['live_text'] ?? 'Live') : ($props['offline_text'] ?? 'Offline') ?>
                </span>
            </span>
        </a>
    <?php else : ?>
        <span class="uk-label has-error <?= $size ? "ls-size-{$size}" : '' ?>">
            <span class="label-content">
                <?php if ($show_icon) : ?>
                    <span uk-icon="icon: warning"></span>
                <?php endif; ?>
                <?= htmlspecialchars($data['error']) ?>
            </span>
        </span>
    <?php endif; ?>
<?= $el->end() ?>
