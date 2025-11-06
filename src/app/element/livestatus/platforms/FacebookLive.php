<?php

namespace YOOtheme\LiveStatus\Element\LiveStatus\Platforms;

class FacebookLive extends Platform
{
    protected function checkLiveStatus(): array
    {
        try {
            $headers = [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/123.0.0.0 Safari/537.36',
                'Accept-Language' => 'en-US,en;q=0.9',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
                'Referer' => 'https://www.facebook.com/'
            ];

            $urls = [
                "https://www.facebook.com/{$this->username}",
                "https://www.facebook.com/{$this->username}/live"
            ];

            $isLive = false;
            $lastResponse = '';
            foreach ($urls as $url) {
                $response = $this->httpGet($url, $headers, [
                    'timeout' => 20,
                    'throw_on_http_error' => false,
                    'follow_location' => true,
                    'ssl_verify_peer' => false
                ]);
                $lastResponse = $response;
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
            if (strpos($lastResponse, 'The link you followed may be broken') !== false || 
                strpos($lastResponse, 'This page isn\'t available') !== false ||
                strpos($lastResponse, 'This content isn\'t available right now') !== false) {
                error_log("Facebook profile not found for {$this->username}");
                throw new \Exception("Facebook profile not found");
            }

            error_log("Facebook final status for {$this->username}: " . ($isLive ? 'live' : 'not live'));
            return [
                'live' => $isLive,
                'username' => $this->username,
                'platform' => 'facebook'
            ];

        } catch (\Exception $e) {
            error_log("Facebook error for {$this->username}: " . $e->getMessage());
            throw $e;
        }
    }
}
