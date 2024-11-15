<?php

namespace YOOtheme\LiveStatus\Element\LiveStatus\Platforms;

class Kick extends Platform
{
    protected function checkLiveStatus(): array
    {
        try {
            // Check cache first
            if ($this->cacheManager) {
                $cachedStatus = $this->cacheManager->get('kick', $this->username);
                if ($cachedStatus !== null) {
                    error_log("Kick: Using cached status for {$this->username}: " . ($cachedStatus['live'] ? 'live' : 'not live'));
                    return $cachedStatus;
                }
            }

            // Check rate limiting
            if ($this->rateLimitManager && !$this->rateLimitManager->checkLimit('kick')) {
                throw new \Exception('Rate limit exceeded for Kick');
            }

            // Simple headers that work
            $headers = [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Accept-Language' => 'en-US,en;q=0.9',
                'Accept' => 'text/html',
                'Accept-Encoding' => 'gzip'
            ];

            $url = "https://kick.com/{$this->username}";
            $response = $this->httpGet($url, $headers);

            error_log("Kick response length for {$this->username}: " . strlen($response));

            // Simple patterns that work reliably
            $patterns = [
                '/livestream-offline-container hidden/',  // Hidden offline container means live
                '/"is_live":true/',                      // JSON live status
                '/livestream-buttons-container/',         // Live buttons container
                '/playback-overlay-container/',           // Playback overlay indicates live
                '/data-channel-is-live="true"/'          // Live channel attribute
            ];

            $isLive = false;
            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $response)) {
                    error_log("Kick live pattern match for {$this->username}: $pattern");
                    $isLive = true;
                    break;
                }
            }

            // Check for profile existence
            if (strpos($response, 'This page is not found') !== false || 
                strpos($response, '404 - Page Not Found') !== false) {
                error_log("Kick profile not found for {$this->username}");
                throw new \Exception("Kick profile not found");
            }

            $result = [
                'live' => $isLive,
                'username' => $this->username,
                'platform' => 'kick'
            ];

            // Cache the result
            if ($this->cacheManager) {
                $cacheDuration = $isLive ? 30 : 120; // 30 seconds if live, 2 minutes if not
                $this->cacheManager->store($result, 'kick', $this->username, $cacheDuration);
            }

            error_log("Kick final status for {$this->username}: " . ($isLive ? 'live' : 'not live'));
            return $result;

        } catch (\Exception $e) {
            error_log("Kick error for {$this->username}: " . $e->getMessage());
            throw $e;
        }
    }
}
