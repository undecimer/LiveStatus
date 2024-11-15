<?php

namespace YOOtheme\LiveStatus\Element\LiveStatus\Platforms;

abstract class Platform implements PlatformInterface
{
    /**
     * Check if a channel is currently live
     *
     * @param string $username The channel username
     * @return bool True if live, false otherwise
     */
    public function isLive(string $username): bool
    {
        try {
            $data = $this->fetchData($username);
            return $data['live'] ?? false;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Fetch data from the platform
     *
     * @param string $username The channel username
     * @return array The platform data
     * @throws \Exception If the request fails
     */
    abstract protected function fetchData(string $username): array;

    /**
     * Make an HTTP GET request
     *
     * @param string $url The URL to request
     * @return string The response body
     * @throws \Exception If the request fails
     */
    protected function httpGet(string $url): string
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');

        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new \Exception("HTTP request failed: $error");
        }

        return $response;
    }
}
