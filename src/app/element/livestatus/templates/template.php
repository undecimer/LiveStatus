<?php

namespace YOOtheme\LiveStatus\Element;

use YOOtheme\LiveStatus\Element\LiveStatus\Platforms\Platform;
use YOOtheme\LiveStatus\Element\LiveStatus\Platforms\TikTok;
use YOOtheme\LiveStatus\Element\LiveStatus\Platforms\Twitch;
use YOOtheme\LiveStatus\Element\LiveStatus\Platforms\YouTube;

class LiveStatusElement {
    private static $cache_dir;
    private static $rate_limit_file;
    private static $cache_duration = 30; // seconds
    private static $rate_limit_window = 60; // seconds
    private static $max_requests = 60; // requests per window

    public static function init() {
        self::$cache_dir = __DIR__ . '/../cache';
        self::$rate_limit_file = self::$cache_dir . '/rate_limit.json';
        
        if (!file_exists(self::$cache_dir)) {
            mkdir(self::$cache_dir, 0755, true);
        }
    }

    public static function getData($platform, $username) {
        try {
            $cache_file = self::$cache_dir . '/' . md5($platform . '_' . $username) . '.json';
            
            // Check cache first
            if (file_exists($cache_file)) {
                $cache_data = json_decode(file_get_contents($cache_file), true);
                if (time() - $cache_data['timestamp'] < self::$cache_duration) {
                    return $cache_data['data'];
                }
            }

            // Check rate limit
            if (!self::checkRateLimit()) {
                return ['error' => 'Rate limit exceeded'];
            }

            // Create platform instance and fetch data
            $data = self::getPlatformInstance($platform, $username)->fetchData();
            
            // Update cache
            file_put_contents($cache_file, json_encode([
                'timestamp' => time(),
                'data' => $data
            ]));

            return $data;
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    private static function checkRateLimit() {
        if (!file_exists(self::$rate_limit_file)) {
            file_put_contents(self::$rate_limit_file, json_encode(['requests' => [], 'window_start' => time()]));
            return true;
        }

        $rate_data = json_decode(file_get_contents(self::$rate_limit_file), true);
        
        // Reset if window has passed
        if (time() - $rate_data['window_start'] >= self::$rate_limit_window) {
            file_put_contents(self::$rate_limit_file, json_encode(['requests' => [time()], 'window_start' => time()]));
            return true;
        }

        // Check if limit exceeded
        if (count($rate_data['requests']) >= self::$max_requests) {
            return false;
        }

        // Add new request
        $rate_data['requests'][] = time();
        file_put_contents(self::$rate_limit_file, json_encode($rate_data));
        return true;
    }

    private static function getPlatformInstance(string $platform, string $username): Platform {
        switch (strtolower($platform)) {
            case 'tiktok':
                return new TikTok($username);
            case 'youtube':
                return new YouTube($username);
            case 'twitch':
                return new Twitch($username);
            default:
                throw new \Exception("Platform not supported");
        }
    }
}

function getPlatformUrl($platform, $username) {
    switch ($platform) {
        case 'tiktok':
            return "https://www.tiktok.com/@{$username}";
        case 'youtube':
            // Handle both channel IDs and custom URLs
            if (preg_match('/^UC[\w-]{22}$/', $username)) {
                return "https://www.youtube.com/channel/{$username}";
            }
            return "https://www.youtube.com/@{$username}";
        case 'twitch':
            return "https://www.twitch.tv/{$username}";
        default:
            return '#';
    }
}

// Initialize the LiveStatusElement class
LiveStatusElement::init();

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
    width: 100%;
}

.el-livestatus .livestatus-container {
    width: 100%;
}

.el-livestatus a {
    text-decoration: none;
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
