<?php

namespace YOOtheme\LiveStatus\Element\LiveStatus\Platforms;

class YouTube extends Platform
{
    public function fetchData(): array
    {
        // First, get the channel ID if a custom URL is provided
        $channelId = $this->username;
        if (!preg_match('/^UC[\w-]{22}$/', $this->username)) {
            $url = "https://www.youtube.com/@{$this->username}";
            $response = $this->httpGet($url);

            // Check if channel exists
            if (strpos($response, 'This page isn\'t available.') !== false || 
                strpos($response, 'This channel does not exist.') !== false) {
                throw new \Exception('YouTube channel not found');
            }

            // Extract channel ID from meta tags or other sources
            if (preg_match('/"channelId":"(UC[\w-]{22})"/', $response, $matches)) {
                $channelId = $matches[1];
            } else {
                throw new \Exception('Could not determine YouTube channel ID');
            }
        }

        // Now check the live tab of the channel
        $liveUrl = "https://www.youtube.com/channel/{$channelId}/live";
        $response = $this->httpGet($liveUrl);

        $isLive = false;

        // Method 1: Check for specific live stream metadata
        if (preg_match('/"isLiveNow":true/', $response)) {
            $isLive = true;
        }

        // Method 2: Check for live stream microformat
        if (!$isLive && preg_match('/"liveBroadcastDetails":\s*{[^}]*"isLiveNow":\s*true/', $response)) {
            $isLive = true;
        }

        // Method 3: Check for live stream manifest
        if (!$isLive && preg_match('/"hlsManifestUrl":/', $response)) {
            $isLive = true;
        }

        // Method 4: Check for live chat
        if (!$isLive && preg_match('/"allowWatchOnlyMode":true/', $response) && 
            preg_match('/"isLiveChat":true/', $response)) {
            $isLive = true;
        }

        // Method 5: Check if the page redirects to an active stream
        if (!$isLive && preg_match('/"videoId":"([^"]+)"/', $response, $matches)) {
            $videoId = $matches[1];
            $videoUrl = "https://www.youtube.com/watch?v={$videoId}";
            $videoResponse = $this->httpGet($videoUrl);
            
            if (preg_match('/"isLive":true/', $videoResponse)) {
                $isLive = true;
            }
        }

        return [
            'live' => $isLive,
            'username' => $this->username,
            'platform' => 'youtube'
        ];
    }
}
