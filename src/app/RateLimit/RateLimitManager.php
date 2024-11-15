<?php

namespace YOOtheme\LiveStatus\RateLimit;

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;

class RateLimitManager
{
    private static $instance = null;
    private $cache;
    private $window;
    private $limits;
    private $group = 'plg_system_livestatus_ratelimit';

    private function __construct()
    {
        $plugin = PluginHelper::getPlugin('system', 'livestatus');
        $params = json_decode($plugin->params);

        // Get configuration from plugin params
        $this->window = isset($params->rate_limit_window) ? (int)$params->rate_limit_window : 60;
        $this->limits = [
            'tiktok' => isset($params->rate_limit_tiktok) ? (int)$params->rate_limit_tiktok : 60,
            'youtube' => isset($params->rate_limit_youtube) ? (int)$params->rate_limit_youtube : 60,
            'twitch' => isset($params->rate_limit_twitch) ? (int)$params->rate_limit_twitch : 60,
            'facebook' => isset($params->rate_limit_facebook) ? (int)$params->rate_limit_facebook : 60,
            'instagram' => isset($params->rate_limit_instagram) ? (int)$params->rate_limit_instagram : 60,
            'kick' => isset($params->rate_limit_kick) ? (int)$params->rate_limit_kick : 60
        ];

        // Initialize cache
        $this->cache = Factory::getCache($this->group, 'output');
        $this->cache->setCaching(true);
        $this->cache->setLifeTime($this->window);
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function checkLimit(string $platform): bool
    {
        $platform = strtolower($platform);
        if (!isset($this->limits[$platform])) {
            return false;
        }

        $key = $this->getRateLimitKey($platform);
        $data = $this->cache->get($key) ?: ['requests' => [], 'window_start' => time()];

        // Clean old requests
        $data['requests'] = array_filter($data['requests'], function($timestamp) {
            return time() - $timestamp < $this->window;
        });

        // Reset window if needed
        if (time() - $data['window_start'] >= $this->window) {
            $data = ['requests' => [], 'window_start' => time()];
        }

        // Check if limit exceeded
        if (count($data['requests']) >= $this->limits[$platform]) {
            return false;
        }

        // Add new request
        $data['requests'][] = time();
        $this->cache->store($data, $key);

        return true;
    }

    public function getRemainingRequests(string $platform): int
    {
        $platform = strtolower($platform);
        if (!isset($this->limits[$platform])) {
            return 0;
        }

        $key = $this->getRateLimitKey($platform);
        $data = $this->cache->get($key) ?: ['requests' => [], 'window_start' => time()];

        // Clean old requests
        $data['requests'] = array_filter($data['requests'], function($timestamp) {
            return time() - $timestamp < $this->window;
        });

        return max(0, $this->limits[$platform] - count($data['requests']));
    }

    private function getRateLimitKey(string $platform): string
    {
        return md5('rate_limit_' . $platform);
    }
}
