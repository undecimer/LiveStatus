<?php

namespace YOOtheme\LiveStatus\Element\LiveStatus\Platforms;

class Twitch extends Platform
{
    protected function checkLiveStatus(): array
    {
        try {
            // Check cache first
            if ($this->cacheManager) {
                $cachedStatus = $this->cacheManager->get('twitch', $this->username);
                if ($cachedStatus !== null) {
                    error_log("Twitch: Using cached status for {$this->username}: " . ($cachedStatus['live'] ? 'live' : 'not live'));
                    return $cachedStatus;
                }
            }

            // Check rate limiting
            if ($this->rateLimitManager && !$this->rateLimitManager->checkLimit('twitch')) {
                throw new \Exception('Rate limit exceeded for Twitch');
            }

            // Simple headers that work
            $headers = [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Accept-Language' => 'en-US,en;q=0.9',
                'Accept' => 'text/html',
                'Accept-Encoding' => 'gzip'
            ];

            $url = "https://www.twitch.tv/{$this->username}";
            $response = $this->httpGet($url, $headers);

            error_log("Twitch response length for {$this->username}: " . strlen($response));

            // Simple patterns that work reliably
            $patterns = [
                '/isLiveBroadcast":true/',           // Live broadcast indicator
                '/channelStatus":"live"/',           // Channel status
                '/channel-status-restriction--live/', // Live restriction class
                '/"isLive":true/',                   // Basic live status
                '/\bdata-a-target="live-indicator"\b/' // Live indicator element
            ];

            $isLive = false;
            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $response)) {
                    error_log("Twitch live pattern match for {$this->username}: $pattern");
                    $isLive = true;
                    break;
                }
            }

            // Check for profile existence using reliable patterns
            if (strpos($response, 'Sorry. Unless you\'ve got a time machine') !== false || 
                strpos($response, '404 Page Not Found') !== false) {
                error_log("Twitch profile not found for {$this->username}");
                throw new \Exception("Twitch profile not found");
            }

            $result = [
                'live' => $isLive,
                'username' => $this->username,
                'platform' => 'twitch'
            ];

            // Cache the result
            if ($this->cacheManager) {
                $cacheDuration = $isLive ? 30 : 120; // 30 seconds if live, 2 minutes if not
                $this->cacheManager->store($result, 'twitch', $this->username, $cacheDuration);
            }

            error_log("Twitch final status for {$this->username}: " . ($isLive ? 'live' : 'not live'));
            return $result;

        } catch (\Exception $e) {
            error_log("Twitch error for {$this->username}: " . $e->getMessage());
            throw $e;
        }
    }
}
