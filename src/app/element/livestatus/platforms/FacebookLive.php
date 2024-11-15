<?php

namespace YOOtheme\LiveStatus\Element\LiveStatus\Platforms;

use YOOtheme\LiveStatus\Cache\CacheManager;
use YOOtheme\LiveStatus\RateLimit\RateLimitManager;

class FacebookLive implements PlatformInterface
{
    private $cacheManager;
    private $rateLimitManager;

    public function __construct()
    {
        $this->cacheManager = CacheManager::getInstance();
        $this->rateLimitManager = RateLimitManager::getInstance();
    }

    public function isLive(string $username): bool
    {
        // Check rate limit
        if (!$this->rateLimitManager->checkLimit('facebook')) {
            return false;
        }

        // Check cache first
        $cached = $this->cacheManager->get('facebook', $username);
        if ($cached !== false) {
            return $cached;
        }

        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://www.facebook.com/{$username}/live");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode !== 200 || $response === false) {
                $this->cacheManager->store(false, 'facebook', $username);
                return false;
            }

            // Look for indicators that the stream is live
            $isLive = (
                stripos($response, 'is live now') !== false ||
                stripos($response, 'LIVE_STREAMING') !== false ||
                stripos($response, 'isLive":true') !== false
            );
            
            $this->cacheManager->store($isLive, 'facebook', $username);
            return $isLive;

        } catch (\Exception $e) {
            $this->cacheManager->store(false, 'facebook', $username);
            return false;
        }
    }
}
