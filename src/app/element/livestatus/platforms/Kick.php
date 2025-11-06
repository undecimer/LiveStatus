<?php

namespace YOOtheme\LiveStatus\Element\LiveStatus\Platforms;

class Kick extends Platform
{
    protected function checkLiveStatus(): array
    {
        try {
            $url = "https://kick.com/{$this->username}";
            $response = $this->httpGet($url, [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/123.0.0.0 Safari/537.36',
                'Accept-Language' => 'en-US,en;q=0.9',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
                'Referer' => 'https://kick.com/'
            ], [
                'timeout' => 20,
                'throw_on_http_error' => false,
                'follow_location' => true
            ]);

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

            error_log("Kick final status for {$this->username}: " . ($isLive ? 'live' : 'not live'));
            return [
                'live' => $isLive,
                'username' => $this->username,
                'platform' => 'kick'
            ];

        } catch (\Exception $e) {
            error_log("Kick error for {$this->username}: " . $e->getMessage());
            throw $e;
        }
    }
}
