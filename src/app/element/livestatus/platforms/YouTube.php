<?php

namespace YOOtheme\LiveStatus\Element\LiveStatus\Platforms;

use YOOtheme\LiveStatus\Cache\CacheManager;
use YOOtheme\LiveStatus\RateLimit\RateLimitManager;

class YouTube extends Platform
{
    protected function checkLiveStatus(): array
    {
        try {
            // Check cache first
            if ($this->cacheManager) {
                $cachedStatus = $this->cacheManager->get('youtube', $this->username);
                if ($cachedStatus !== null) {
                    error_log("YouTube: Using cached status for {$this->username}: " . ($cachedStatus ? 'live' : 'not live'));
                    return [
                        'live' => $cachedStatus,
                        'username' => $this->username,
                        'platform' => 'youtube'
                    ];
                }
            }

            // Check rate limiting
            if ($this->rateLimitManager && !$this->rateLimitManager->checkLimit('youtube')) {
                throw new \Exception('Rate limit exceeded for YouTube');
            }

            $url = "https://www.youtube.com/@{$this->username}/live";
            $response = $this->httpGet($url);
            
            error_log("YouTube response length for {$this->username}: " . strlen($response));
            
            // Multiple patterns to detect live status
            $patterns = [
                '/"isLive":true/',                     // Standard JSON pattern
                '/\bisLive\s*:\s*true\b/',             // JavaScript object pattern
                '/"status":"LIVE"/',                   // Live status
                '/\blive-now\b/',                      // Live now badge
                '/\bdata-is-live="true"\b/',           // Live attribute
                '/"videoLiveStatus":"live"/',          // Live video status
                '/\blive-badge\b/',                    // Live badge class
                '/"broadcastIsLive":true/'             // Broadcast status
            ];
            
            $isLive = false;
            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $response)) {
                    error_log("YouTube live pattern match for {$this->username}: $pattern");
                    $isLive = true;
                    break;
                }
            }
            
            // Check for offline indicators
            $offlinePatterns = [
                '/"isLive":false/',
                '/\bisLive\s*:\s*false\b/',
                '/"status":"ENDED"/',
                '/"videoLiveStatus":"ended"/',
                '/"broadcastIsLive":false/',
                '/This channel does not exist/'
            ];
            
            foreach ($offlinePatterns as $pattern) {
                if (preg_match($pattern, $response)) {
                    error_log("YouTube offline pattern match for {$this->username}: $pattern");
                    $isLive = false;
                    break;
                }
            }
            
            // Additional validation for channel existence
            if (strpos($response, 'This channel does not exist') !== false || 
                strpos($response, 'Channel not found') !== false ||
                strpos($response, '404 Not Found') !== false) {
                error_log("YouTube channel not found for {$this->username}");
                throw new \Exception("YouTube channel not found");
            }

            $result = [
                'live' => $isLive,
                'username' => $this->username,
                'platform' => 'youtube'
            ];
            
            // Store in cache (shorter time if live)
            if ($this->cacheManager) {
                $cacheDuration = $isLive ? 30 : 120; // 30 seconds if live, 2 minutes if not
                $this->cacheManager->store($result, 'youtube', $this->username, $cacheDuration);
            }
            
            error_log("YouTube final status for {$this->username}: " . ($isLive ? 'true' : 'false'));
            return $result;
            
        } catch (\Exception $e) {
            error_log("YouTube error for {$this->username}: " . $e->getMessage());
            throw $e;
        }
    }
}
