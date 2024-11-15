<?php

namespace YOOtheme\LiveStatus\Element\LiveStatus\Platforms;

class FacebookLive extends Platform
{
    protected function checkLiveStatus(): array
    {
        try {
            // Check cache first
            if ($this->cacheManager) {
                $cachedStatus = $this->cacheManager->get('facebook', $this->username);
                if ($cachedStatus !== null) {
                    error_log("Facebook: Using cached status for {$this->username}: " . ($cachedStatus['live'] ? 'live' : 'not live'));
                    return $cachedStatus;
                }
            }

            // Check rate limiting
            if ($this->rateLimitManager && !$this->rateLimitManager->checkLimit('facebook')) {
                throw new \Exception('Rate limit exceeded for Facebook');
            }

            // Simple headers that work
            $headers = [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Accept-Language' => 'en-US,en;q=0.9',
                'Accept' => 'text/html',
                'Accept-Encoding' => 'gzip'
            ];

            // Try both normal and live URLs
            $urls = [
                "https://www.facebook.com/{$this->username}",
                "https://www.facebook.com/{$this->username}/live"
            ];

            $isLive = false;
            foreach ($urls as $url) {
                $response = $this->httpGet($url, $headers);
                error_log("Facebook response length for {$this->username} at {$url}: " . strlen($response));

                // Simple patterns that work reliably
                $patterns = [
                    '/\\"is_live_streaming\\":true/',     // Live streaming status
                    '/\\"is_live\\":true/',               // Basic live status
                    '/isLiveVideo":true/',                // Live video indicator
                    '/watchVideoComponent.*?isLive":true/',// Live video component
                    '/LiveVideoIndicator/',               // Live indicator element
                    '/live_video_badge/',                 // Live badge class
                    '/live-video-badge/',                 // Alternative live badge
                    '/currently_live":true/'              // Currently live status
                ];

                foreach ($patterns as $pattern) {
                    if (preg_match($pattern, $response)) {
                        error_log("Facebook live pattern match for {$this->username}: $pattern");
                        $isLive = true;
                        break 2; // Break both loops if we find a match
                    }
                }
            }

            // Check for profile existence
            if (strpos($response, 'The link you followed may be broken') !== false || 
                strpos($response, 'This page isn\'t available') !== false ||
                strpos($response, 'This content isn\'t available right now') !== false) {
                error_log("Facebook profile not found for {$this->username}");
                throw new \Exception("Facebook profile not found");
            }

            $result = [
                'live' => $isLive,
                'username' => $this->username,
                'platform' => 'facebook'
            ];

            // Cache the result
            if ($this->cacheManager) {
                $cacheDuration = $isLive ? 30 : 120; // 30 seconds if live, 2 minutes if not
                $this->cacheManager->store($result, 'facebook', $this->username, $cacheDuration);
            }

            error_log("Facebook final status for {$this->username}: " . ($isLive ? 'live' : 'not live'));
            return $result;

        } catch (\Exception $e) {
            error_log("Facebook error for {$this->username}: " . $e->getMessage());
            throw $e;
        }
    }
}
