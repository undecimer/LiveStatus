<?php

namespace YOOtheme\LiveStatus\Element\LiveStatus\Platforms;

class Twitch extends Platform
{
    protected function checkLiveStatus(): array
    {
        try {
            $headers = [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/123.0.0.0 Safari/537.36',
                'Accept-Language' => 'en-US,en;q=0.9',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8',
                'Referer' => 'https://www.twitch.tv/'
            ];

            $url = "https://www.twitch.tv/{$this->username}";
            $response = $this->httpGet($url, $headers, [
                'timeout' => 20,
                'throw_on_http_error' => false,
                'follow_location' => true
            ]);

            error_log("Twitch response length for {$this->username}: " . strlen($response));

            // Enhanced patterns for live status detection
            $patterns = [
                '/"isLiveBroadcast":true/',                  // JSON data
                '/"broadcastSettings":{"isLive":true}/',     // Broadcast settings
                '/"stream":{"type":"live"}/',                // Stream type
                '/"isLive":true/',                           // Basic live status
                '/\bdata-a-target="live-indicator"\b/',       // Live indicator element
                '/\bdata-test-selector="stream-status-live"\b/', // Stream status
                '/\blive-indicator--live\b/',                // Live indicator class
                '/\bchannel-status-restriction--live\b/',    // Live restriction class
                '/\bstream-status--live\b/'                  // Stream status class
            ];

            $isLive = false;
            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $response)) {
                    error_log("Twitch live pattern match for {$this->username}: $pattern");
                    $isLive = true;
                    break;
                }
            }

            return [
                'live' => $isLive,
                'username' => $this->username,
                'platform' => 'twitch'
            ];

        } catch (\Exception $e) {
            error_log("Twitch error for {$this->username}: " . $e->getMessage());
            throw $e;
        }
    }

    protected function getPlatformName(): string
    {
        return 'twitch';
    }
}