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

error_log("Template data: " . print_r($data, true));
error_log("Template platform: {$platform}");
error_log("Template username: {$username}");
error_log("Template props: " . print_r($props, true));

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
}

/* Platform-specific colors when live */
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
                <span class="uk-label <?= ($data['live'] ?? false) ? 'is-live' : '' ?>" data-platform="<?= $platform ?>">
                    <?php if (isset($props['show_icon']) && $props['show_icon']) : ?>
                        <span uk-icon="icon: <?= $platform ?>"></span>
                    <?php endif ?>
                    <?= htmlspecialchars(($data['live'] ?? false) ? $props['live_text'] : $props['offline_text']) ?>
                </span>
            </a>
        <?php endif ?>
    <?php else : ?>
        <div class="el-livestatus-error">
            <?= $data['error'] ?>
        </div>
    <?php endif ?>
<?= $el->end() ?>
