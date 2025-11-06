<?php

namespace YOOtheme\LiveStatus\Element\LiveStatus\Platforms;

class TikTok extends Platform
{
    protected $platform = 'tiktok';
    protected $colors = ['#25F4EE', '#FE2C55', '#000000'];
    protected $icon = 'tiktok';
    protected $url_pattern = 'https://www.tiktok.com/@{username}';

    protected function checkLiveStatus(): array
    {
        try {
            $url = "https://www.tiktok.com/@{$this->username}";
            $response = $this->httpGet($url, [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/123.0.0.0 Safari/537.36',
                'Accept-Language' => 'en-US,en;q=0.9',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8'
            ], [
                'ssl_verify_peer' => false,
                'timeout' => 20
            ]);

            if ($response === '') {
                throw new \Exception('Empty response from TikTok');
            }

            $parsedStatus = $this->extractLiveStatusFromSigState($response);

            // Check if profile exists
            if (strpos($response, 'userInfo') === false &&
                strpos($response, 'profile') === false &&
                strpos($response, 'tiktok-avatar') === false &&
                strpos($response, 'SIGI_STATE') === false) {
                error_log("TikTok profile not found for {$this->username}");
                throw new \Exception("TikTok profile not found");
            }

            // Enhanced live detection using multiple reliable methods
            $isLive = $parsedStatus ?? false;

            // Method 1: Check for live room metadata
            if ($isLive === false && preg_match('/"roomId":"[0-9]+"/i', $response)) {
                error_log("TikTok: Live detected via roomId for user {$this->username}");
                $isLive = true;
            }

            // Method 2: Check for live stream status
            if ($isLive === false && preg_match('/"isLive":true/i', $response)) {
                error_log("TikTok: Live detected via isLive flag for user {$this->username}");
                $isLive = true;
            }

            // Method 3: Check for live indicator in user data
            if ($isLive === false && preg_match('/"user":[^}]+?"live":true/i', $response)) {
                error_log("TikTok: Live detected via user.live flag for user {$this->username}");
                $isLive = true;
            }

            return [
                'live' => $isLive,
                'username' => $this->username,
                'platform' => 'tiktok'
            ];

        } catch (\Exception $e) {
            error_log("TikTok error for {$this->username}: " . $e->getMessage());
            throw $e;
        }
    }

    private function extractLiveStatusFromSigState(string $html): ?bool
    {
        if (!preg_match('/<script id="SIGI_STATE" type="application\/(?:ld\+)?json">(.+?)<\/script>/is', $html, $matches)) {
            return null;
        }

        $json = html_entity_decode($matches[1], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $data = json_decode($json, true);

        if (!is_array($data)) {
            return null;
        }

        $liveRoom = $data['LiveRoom']['liveRoomUserInfo']['liveRoom'] ?? null;
        if (!is_array($liveRoom)) {
            return null;
        }

        $status = $liveRoom['status'] ?? null;
        if ($status === 2) {
            return true;
        }

        if (in_array($status, [3, 4, 6], true)) {
            return false;
        }

        if (!empty($liveRoom['streamData']) || !empty($liveRoom['hevcStreamData'])) {
            return true;
        }

        return null;
    }
}
