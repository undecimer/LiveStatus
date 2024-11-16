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
            // Check cache first
            if ($this->cacheManager) {
                $cachedStatus = $this->cacheManager->get('tiktok', $this->username);
                if ($cachedStatus !== null) {
                    error_log("TikTok: Using cached status for {$this->username}: " . ($cachedStatus['live'] ? 'live' : 'not live'));
                    return $cachedStatus;
                }
            }

            // Check rate limiting
            if ($this->rateLimitManager && !$this->rateLimitManager->checkLimit('tiktok')) {
                throw new \Exception('Rate limit exceeded for TikTok');
            }

            $url = "https://www.tiktok.com/@{$this->username}";
            $ch = curl_init();
            
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_ENCODING => '',
                CURLOPT_HTTPHEADER => [
                    'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                    'Accept-Language: en-US,en;q=0.5',
                    'Connection: keep-alive',
                    'Upgrade-Insecure-Requests: 1'
                ]
            ]);

            $response = curl_exec($ch);
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpcode !== 200) {
                error_log("TikTok: HTTP error {$httpcode} for user {$this->username}");
                throw new \Exception("TikTok access error: HTTP {$httpcode}");
            }

            // Check if profile exists
            if (strpos($response, 'userInfo') === false && 
                strpos($response, 'profile') === false && 
                strpos($response, 'tiktok-avatar') === false) {
                error_log("TikTok profile not found for {$this->username}");
                throw new \Exception("TikTok profile not found");
            }

            // Enhanced live detection using multiple reliable methods
            $isLive = false;
            
            // Method 1: Check for live room metadata
            if (preg_match('/"roomId":"[0-9]+"/i', $response)) {
                error_log("TikTok: Live detected via roomId for user {$this->username}");
                $isLive = true;
            }
            
            // Method 2: Check for live stream status
            if (!$isLive && preg_match('/"isLive":true/i', $response)) {
                error_log("TikTok: Live detected via isLive flag for user {$this->username}");
                $isLive = true;
            }
            
            // Method 3: Check for live indicator in user data
            if (!$isLive && preg_match('/"user":[^}]+?"live":true/i', $response)) {
                error_log("TikTok: Live detected via user.live flag for user {$this->username}");
                $isLive = true;
            }

            $result = [
                'live' => $isLive,
                'username' => $this->username,
                'platform' => 'tiktok'
            ];

            // Cache the result
            if ($this->cacheManager) {
                $cacheDuration = $isLive ? 30 : 120; // 30 seconds if live, 2 minutes if not
                $this->cacheManager->store($result, 'tiktok', $this->username, $cacheDuration);
            }

            return $result;

        } catch (\Exception $e) {
            error_log("TikTok error for {$this->username}: " . $e->getMessage());
            throw $e;
        }
    }
}
