<?php

namespace YOOtheme\LiveStatus\Element\LiveStatus\Platforms;

class Twitch extends Platform
{
    protected function fetchData(string $username): array
    {
        $url = "https://www.twitch.tv/{$username}";
        $response = $this->httpGet($url);

        // Check if channel exists
        if (strpos($response, 'Sorry. Unless you\'ve got a time machine') !== false) {
            throw new \Exception('Twitch channel not found');
        }

        // Multiple methods to detect live status
        $isLive = false;

        // Method 1: Check for live indicator in page data
        if (preg_match('/"isLiveBroadcast":\s*true/', $response)) {
            $isLive = true;
        }

        // Method 2: Check for live stream properties
        if (!$isLive && preg_match('/"broadcastSettings":\s*{\s*"isMature"/', $response)) {
            $isLive = true;
        }

        // Method 3: Check for live badge
        if (!$isLive && preg_match('/data-a-target="live-indicator"/', $response)) {
            $isLive = true;
        }

        return [
            'live' => $isLive,
            'username' => $username,
            'platform' => 'twitch'
        ];
    }
}
