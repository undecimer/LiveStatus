<?php

namespace YOOtheme\LiveStatus\Element\LiveStatus\Platforms;

class InstagramLive extends Platform
{
    protected function checkLiveStatus(): array
    {
        try {
            // Check cache first
            if ($this->cacheManager) {
                $cachedStatus = $this->cacheManager->get('instagram', $this->username);
                if ($cachedStatus !== null) {
                    error_log("Instagram: Using cached status for {$this->username}: " . ($cachedStatus['live'] ? 'live' : 'not live'));
                    return $cachedStatus;
                }
            }

            // Check rate limiting
            if ($this->rateLimitManager && !$this->rateLimitManager->checkLimit('instagram')) {
                throw new \Exception('Rate limit exceeded for Instagram');
            }

            // Simple headers that work
            $headers = [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Accept-Language' => 'en-US,en;q=0.9',
                'Accept' => 'text/html',
                'Accept-Encoding' => 'gzip'
            ];

            $url = "https://www.instagram.com/{$this->username}";
            $response = $this->httpGet($url, $headers);

            error_log("Instagram response length for {$this->username}: " . strlen($response));

            // Simple patterns that work reliably
            $patterns = [
                '/\\"is_live\\":true/',                // Live status in JSON
                '/live_broadcast_id/',                 // Live broadcast ID present
                '/live_broadcast_status.*?LIVE/',      // Live broadcast status
                '/live-video-badge/',                  // Live badge element
                '/live-broadcast-view-count/',         // Live view count element
                '/live_streaming_enabled.*?true/',     // Live streaming enabled
                '/live-video-indicator/',              // Live indicator element
                '/live_streaming_now":true/'           // Currently streaming
            ];

            $isLive = false;
            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $response)) {
                    error_log("Instagram live pattern match for {$this->username}: $pattern");
                    $isLive = true;
                    break;
                }
            }

            // Check for profile existence
            if (strpos($response, 'Sorry, this page isn\'t available.') !== false || 
                strpos($response, 'Page Not Found') !== false ||
                strpos($response, 'The link you followed may be broken') !== false) {
                error_log("Instagram profile not found for {$this->username}");
                throw new \Exception("Instagram profile not found");
            }

            $result = [
                'live' => $isLive,
                'username' => $this->username,
                'platform' => 'instagram'
            ];

            // Cache the result
            if ($this->cacheManager) {
                $cacheDuration = $isLive ? 30 : 120; // 30 seconds if live, 2 minutes if not
                $this->cacheManager->store($result, 'instagram', $this->username, $cacheDuration);
            }

            error_log("Instagram final status for {$this->username}: " . ($isLive ? 'live' : 'not live'));
            return $result;

        } catch (\Exception $e) {
            error_log("Instagram error for {$this->username}: " . $e->getMessage());
            throw $e;
        }
    }
}
