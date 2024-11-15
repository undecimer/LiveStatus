<?php

namespace YOOtheme\LiveStatus\Element\LiveStatus\Platforms;

class YouTube extends Platform
{
    protected function fetchData(string $username): array
    {
        // Handle both channel IDs and custom URLs
        $url = strpos($username, 'UC') === 0 && strlen($username) === 24
            ? "https://www.youtube.com/channel/{$username}"
            : "https://www.youtube.com/@{$username}";

        $response = $this->httpGet($url);

        // Check if channel exists
        if (strpos($response, 'This channel does not exist') !== false) {
            throw new \Exception('YouTube channel not found');
        }

        // Multiple methods to detect live status
        $isLive = false;

        // Method 1: Check for live badge
        if (preg_match('/("label":\s*"LIVE")/', $response)) {
            $isLive = true;
        }

        // Method 2: Check for live stream status
        if (!$isLive && preg_match('/"isLive":\s*true/', $response)) {
            $isLive = true;
        }

        // Method 3: Check for live broadcast content
        if (!$isLive && preg_match('/"liveBroadcastContent":\s*"live"/', $response)) {
            $isLive = true;
        }

        return [
            'live' => $isLive,
            'username' => $username,
            'platform' => 'youtube'
        ];
    }
}
