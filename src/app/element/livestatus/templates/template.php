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

class LiveStatusElement {
    public static function getData($platform, $username) {
        try {
            $cacheManager = CacheManager::getInstance();
            $rateLimitManager = RateLimitManager::getInstance();
            
            // Check cache first
            $cached_data = $cacheManager->get($platform, $username);
            if ($cached_data !== false) {
                return $cached_data;
            }

            // Check rate limit
            if (!$rateLimitManager->checkLimit($platform)) {
                $remaining_time = $rateLimitManager->getRemainingRequests($platform);
                return [
                    'error' => 'Rate limit exceeded',
                    'details' => "Please try again later. Remaining requests: {$remaining_time}"
                ];
            }

            // Create platform instance and fetch data
            $data = self::getPlatformInstance($platform, $username)->fetchData();
            
            // Store in cache
            $cacheManager->store($data, $platform, $username);

            return $data;
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    private static function getPlatformInstance(string $platform, string $username): Platform {
        switch (strtolower($platform)) {
            case 'tiktok':
                return new TikTok($username);
            case 'youtube':
                return new YouTube($username);
            case 'twitch':
                return new Twitch($username);
            case 'facebook':
                return new FacebookLive($username);
            case 'instagram':
                return new InstagramLive($username);
            case 'kick':
                return new Kick($username);
            default:
                throw new \Exception("Platform not supported");
        }
    }
}

function getPlatformUrl($platform, $username) {
    switch ($platform) {
        case 'tiktok':
            return "https://www.tiktok.com/@{$username}/live";
        case 'youtube':
            if (strpos($username, 'UC') === 0 && strlen($username) === 24) {
                return "https://www.youtube.com/channel/{$username}";
            }
            return "https://www.youtube.com/@{$username}";
        case 'twitch':
            return "https://www.twitch.tv/{$username}";
        case 'facebook':
            return "https://www.facebook.com/{$username}";
        case 'instagram':
            return "https://www.instagram.com/{$username}";
        case 'kick':
            return "https://kick.com/{$username}";
        default:
            return '#';
    }
}

// Initialize the LiveStatusElement class
// Removed initialization as it's not needed anymore

// Build element container
$el = $this->el('div', [
    'class' => [
        'el-livestatus'
    ]
]);

// Get platform data
$data = LiveStatusElement::getData($props['platform'], $props['username']);

// Get alignment class
$alignment = isset($props['text_alignment']) ? $props['text_alignment'] : 'left';
$alignmentClass = 'uk-flex uk-flex-' . $alignment;

?>

<style>
.el-livestatus {
    display: inline-flex;
}

.el-livestatus .uk-label {
    background: #666;
    transition: background-color 0.3s ease;
}

.el-livestatus .uk-label.is-live {
    background: #32d296;
}

.el-livestatus .uk-label.is-live[data-platform="tiktok"] {
    background: #fe2c55;
}

.el-livestatus .uk-label.is-live[data-platform="youtube"] {
    background: #ff0000;
}

.el-livestatus .uk-label.is-live[data-platform="twitch"] {
    background: #9147ff;
}

.el-livestatus .uk-label.is-live[data-platform="facebook"] {
    background: #4267b2;
}

.el-livestatus .uk-label.is-live[data-platform="instagram"] {
    background: #f26798;
}

.el-livestatus .uk-label.is-live[data-platform="kick"] {
    background: #53fc18;
}

.el-livestatus .el-livestatus-error {
    color: #ff3545;
    font-style: italic;
    text-align: center;
}
</style>

<?= $el($props, $attrs) ?>
    <div class="livestatus-container <?= $alignmentClass ?>">
        <?php if (!isset($data['error'])) : ?>
            <?php if (!$props['hide_when_offline'] || $data['live']) : ?>
                <a href="<?= htmlspecialchars(getPlatformUrl($data['platform'], $data['username'])) ?>" target="_blank" rel="noopener">
                    <span class="uk-label <?= $data['live'] ? 'is-live' : '' ?>" data-platform="<?= $data['platform'] ?>">
                        <?php if ($props['show_icon']) : ?>
                            <span uk-icon="icon: <?= $data['platform'] ?>"></span>
                        <?php endif ?>
                        <?= htmlspecialchars($data['live'] ? $props['status_text_live'] : $props['status_text_offline']) ?>
                    </span>
                </a>
            <?php endif ?>
        <?php else : ?>
            <div class="el-livestatus-error">
                <?= $data['error'] ?>
            </div>
        <?php endif ?>
    </div>
<?= $el->end() ?>


<?php
$id = $element['id'];
$platform = $props['platform_name'];
$username = $props['channel_username'];
$isLive = $props['is_live'];
$showOffline = $props['show_offline'];
$offlineText = $props['offline_text'];
$liveText = $props['live_text'];

// Don't show anything if offline and show_offline is false
if (!$isLive && !$showOffline) {
    return;
}

// Get platform URL
$platformUrl = getPlatformUrl($platform, $username);

// Build classes
$el = $this->el('div', [
    'class' => [
        'el-livestatus',
        'uk-flex uk-flex-middle',
    ]
]);

?>

<?= $el($props, $attrs) ?>
    <a href="<?= $platformUrl ?>" target="_blank" class="uk-link-reset">
        <span class="uk-label<?= $isLive ? ' is-live' : '' ?>" data-platform="<?= $platform ?>">
            <?= $isLive ? $liveText : $offlineText ?>
        </span>
    </a>
</div>
