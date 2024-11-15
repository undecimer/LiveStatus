<?php

namespace YOOtheme\LiveStatus\Element\LiveStatus\Platforms;

class Twitch extends Platform
{
    public function fetchData(): array
    {
        $url = "https://www.twitch.tv/{$this->username}";
        $response = $this->httpGet($url);

        // Check if user exists
        if (strpos($response, 'Sorry. Unless you\'ve got a time machine') !== false) {
            throw new \Exception('Twitch user not found');
        }

        // Multiple methods to detect live status
        $isLive = false;

        // Method 1: Check for isLiveBroadcast schema
        if (preg_match('/"isLiveBroadcast":\s*true/', $response)) {
            $isLive = true;
        }

        // Method 2: Check for live channel status
        if (!$isLive && preg_match('/"isLive":\s*true/', $response)) {
            $isLive = true;
        }

        // Method 3: Check for live text in page
        if (!$isLive && preg_match('/\b' . preg_quote($this->username, '/') . '\s+is\s+(now\s+)?live\b/i', $response)) {
            $isLive = true;
        }

        // Method 4: Check for stream schema
        if (!$isLive && preg_match('/"broadcastType":\s*"live"/', $response)) {
            $isLive = true;
        }

        return [
            'live' => $isLive,
            'username' => $this->username,
            'platform' => 'twitch'
        ];
    }
}
