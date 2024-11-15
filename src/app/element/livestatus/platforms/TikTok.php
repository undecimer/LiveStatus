<?php

namespace YOOtheme\LiveStatus\Element\LiveStatus\Platforms;

class TikTok extends Platform
{
    protected function fetchData(string $username): array
    {
        $url = "https://www.tiktok.com/@{$username}";
        $response = $this->httpGet($url);

        // Check if user exists
        if (strpos($response, 'user-not-found') !== false) {
            throw new \Exception('TikTok user not found');
        }

        // Multiple methods to detect live status
        $isLive = false;

        // Method 1: Check for LIVE indicator in user info
        if (preg_match('/"isLive"\s*:\s*true/', $response)) {
            $isLive = true;
        }

        // Method 2: Check for live room ID
        if (!$isLive && preg_match('/"roomId"\s*:\s*"[^"]+"/', $response)) {
            $isLive = true;
        }

        // Method 3: Check for live stream status
        if (!$isLive && preg_match('/"liveStatus"\s*:\s*1/', $response)) {
            $isLive = true;
        }

        return [
            'live' => $isLive,
            'username' => $username,
            'platform' => 'tiktok'
        ];
    }
}
