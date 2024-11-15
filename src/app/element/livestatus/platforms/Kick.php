<?php

namespace YOOtheme\LiveStatus\Element\LiveStatus\Platforms;

use YOOtheme\LiveStatus\Cache\CacheManager;
use YOOtheme\LiveStatus\RateLimit\RateLimitManager;

class Kick implements PlatformInterface
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
        if (!$this->rateLimitManager->checkLimit('kick')) {
            return false;
        }

        // Check cache first
        $cached = $this->cacheManager->get('kick', $username);
        if ($cached !== false) {
            return $cached;
        }

        // Try API first, then fallback to scraping
        $isLive = $this->checkViaApi($username);
        if ($isLive === null) {
            $isLive = $this->checkViaScraping($username);
        }

        $this->cacheManager->store($isLive, 'kick', $username);
        return $isLive;
    }

    private function checkViaApi(string $username): ?bool
    {
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://kick.com/api/v1/channels/{$username}");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERAGENT, 'LiveStatus/1.0');
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode !== 200 || $response === false) {
                return null;
            }

            $data = json_decode($response, true);
            return isset($data['livestream']) && $data['livestream'] !== null;

        } catch (\Exception $e) {
            return null;
        }
    }

    private function checkViaScraping(string $username): bool
    {
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://kick.com/{$username}");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode !== 200 || $response === false) {
                return false;
            }

            // Look for indicators that the stream is live
            return (
                stripos($response, '"isLive":true') !== false ||
                stripos($response, 'livestream-offline-container') === false ||
                stripos($response, 'Live on Kick') !== false
            );

        } catch (\Exception $e) {
            return false;
        }
    }
}
