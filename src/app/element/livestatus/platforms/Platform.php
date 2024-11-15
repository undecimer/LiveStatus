<?php

namespace YOOtheme\LiveStatus\Element\LiveStatus\Platforms;

use Joomla\CMS\Factory;
use YOOtheme\LiveStatus\Cache\CacheManager;
use YOOtheme\LiveStatus\RateLimit\RateLimitManager;

abstract class Platform implements PlatformInterface {
    protected $username;
    protected static $cacheGroup = 'plg_system_livestatus';
    protected $cacheManager;
    protected $rateLimitManager;

    public function __construct(string $username) {
        $this->username = $username;
        try {
            $this->cacheManager = CacheManager::getInstance();
            $this->rateLimitManager = RateLimitManager::getInstance();
        } catch (\Exception $e) {
            error_log("Platform initialization error: " . $e->getMessage());
            // Continue without cache - we'll fetch fresh data each time
        }
    }

    /**
     * Check if a channel is currently live
     *
     * @return bool True if live, false otherwise
     */
    public function isLive(): bool {
        try {
            // First check cache if available
            if ($this->cacheManager) {
                $cachedStatus = $this->cacheManager->get($this->getPlatformName(), $this->username);
                if ($cachedStatus !== null) {
                    error_log("Using cached status for {$this->getPlatformName()}_{$this->username}: " . ($cachedStatus ? 'live' : 'not live'));
                    return $cachedStatus;
                }
            }

            // If not in cache or no cache available, fetch fresh data
            $data = $this->fetchData();
            error_log("Platform isLive() data for {$this->username}: " . json_encode($data));
            
            // Ensure we have a proper boolean value
            $isLive = isset($data['live']) && $data['live'] === true;
            error_log("Platform isLive() final value for {$this->username}: " . ($isLive ? 'live' : 'not live'));
            
            return $isLive;
        } catch (\Exception $e) {
            error_log("Platform isLive() error for {$this->username}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Fetch data from the platform
     *
     * @return array The platform data
     * @throws \Exception If the request fails
     */
    public function fetchData(): array {
        try {
            // Check rate limiting first if available
            if ($this->rateLimitManager && !$this->rateLimitManager->checkLimit($this->getPlatformName())) {
                throw new \Exception("Rate limit exceeded for {$this->getPlatformName()}");
            }

            // Get fresh status
            $data = $this->checkLiveStatus();
            error_log("Platform fetchData() raw result for {$this->username}: " . json_encode($data));
            
            // Ensure we have all required fields with proper types
            if (!isset($data['live'])) {
                $data['live'] = false;
            }
            
            // Force boolean type and ensure it's not null
            $data['live'] = ($data['live'] === true);
            
            if (!isset($data['username'])) {
                $data['username'] = $this->username;
            }
            
            if (!isset($data['platform'])) {
                $data['platform'] = $this->getPlatformName();
            }
            
            // Store in cache if available
            if ($this->cacheManager) {
                $cacheDuration = $data['live'] ? 30 : 120; // 30 seconds if live, 2 minutes if not
                $this->cacheManager->store($data['live'], $this->getPlatformName(), $this->username, $cacheDuration);
            }
            
            error_log("Platform fetchData() processed result for {$this->username}: " . json_encode($data));
            return $data;
        } catch (\Exception $e) {
            error_log("Platform fetchData() error for {$this->username}: " . $e->getMessage());
            return [
                'live' => false,
                'username' => $this->username,
                'platform' => $this->getPlatformName(),
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get platform name from class name
     */
    protected function getPlatformName(): string {
        $className = get_class($this);
        $parts = explode('\\', $className);
        return strtolower(end($parts));
    }

    /**
     * Make an HTTP GET request
     */
    protected function httpGet(string $url): string {
        $ch = curl_init();
        
        // Set modern browser headers
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/119.0.0.0 Safari/537.36',
            CURLOPT_HTTPHEADER => [
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                'Accept-Language: en-US,en;q=0.5',
                'Connection: keep-alive',
                'Upgrade-Insecure-Requests: 1',
                'Cache-Control: max-age=0'
            ],
            CURLOPT_TIMEOUT => 10
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new \Exception("HTTP request failed: " . $error);
        }
        
        curl_close($ch);
        
        if ($httpCode >= 400) {
            throw new \Exception("HTTP request failed with status code: " . $httpCode);
        }
        
        return $response;
    }

    /**
     * Clear the cache for this platform
     */
    protected function clearCache(): void {
        try {
            if ($this->cacheManager) {
                $this->cacheManager->clear($this->getPlatformName(), $this->username);
                error_log("Cache cleared for platform {$this->username}");
            }
        } catch (\Exception $e) {
            error_log("Failed to clear cache: " . $e->getMessage());
        }
    }

    /**
     * Abstract method to check live status
     * Must be implemented by each platform
     */
    abstract protected function checkLiveStatus(): array;
}
