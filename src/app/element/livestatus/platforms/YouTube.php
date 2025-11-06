<?php

namespace YOOtheme\LiveStatus\Element\LiveStatus\Platforms;

class YouTube extends Platform
{
    protected function checkLiveStatus(): array
    {
        try {
            $url = "https://www.youtube.com/@{$this->username}/live";
            $response = $this->httpGet($url, [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/123.0.0.0 Safari/537.36',
                'Accept-Language' => 'en-US,en;q=0.9',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
                'Referer' => 'https://www.youtube.com/'
            ], [
                'timeout' => 20,
                'http_version' => defined('CURL_HTTP_VERSION_2_0') ? CURL_HTTP_VERSION_2_0 : null,
                'throw_on_http_error' => false
            ]);
            
            error_log("YouTube response length for {$this->username}: " . strlen($response));
            
            // Multiple patterns to detect live status
            $patterns = [
                '/"isLive":true/',                     // Standard JSON pattern
                '/\bisLive\s*:\s*true\b/',             // JavaScript object pattern
                '/"status":"LIVE"/',                   // Live status
                '/\blive-now\b/',                      // Live now badge
                '/\bdata-is-live="true"\b/',           // Live attribute
                '/"videoLiveStatus":"live"/',          // Live video status
                '/\blive-badge\b/',                    // Live badge class
                '/"broadcastIsLive":true/'             // Broadcast status
            ];
            
            $isLive = false;
            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $response)) {
                    error_log("YouTube live pattern match for {$this->username}: $pattern");
                    $isLive = true;
                    break;
                }
            }
            
            // Check for offline indicators
            $offlinePatterns = [
                '/"isLive":false/',
                '/\bisLive\s*:\s*false\b/',
                '/"status":"ENDED"/',
                '/"videoLiveStatus":"ended"/',
                '/"broadcastIsLive":false/',
                '/This channel does not exist/'
            ];
            
            foreach ($offlinePatterns as $pattern) {
                if (preg_match($pattern, $response)) {
                    error_log("YouTube offline pattern match for {$this->username}: $pattern");
                    $isLive = false;
                    break;
                }
            }
            
            // Additional validation for channel existence
            if (strpos($response, 'This channel does not exist') !== false || 
                strpos($response, 'Channel not found') !== false ||
                strpos($response, '404 Not Found') !== false) {
                error_log("YouTube channel not found for {$this->username}");
                throw new \Exception("YouTube channel not found");
            }

            return [
                'live' => $isLive,
                'username' => $this->username,
                'platform' => 'youtube'
            ];
            
        } catch (\Exception $e) {
            error_log("YouTube error for {$this->username}: " . $e->getMessage());
            throw $e;
        }
    }
}
