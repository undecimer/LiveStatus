<?php

namespace YOOtheme\LiveStatus;

abstract class Platform
{
    protected $username;
    protected $cache_time = 30; // Cache time in seconds
    protected static $instances = [];

    protected function __construct(string $username)
    {
        $this->username = $username;
    }

    public static function create(string $platform, string $username): self
    {
        $class = __NAMESPACE__ . '\\Platforms\\' . ucfirst($platform);
        
        $key = $platform . '_' . $username;
        if (!isset(static::$instances[$key])) {
            if (!class_exists($class)) {
                throw new \RuntimeException("Platform '$platform' not found");
            }
            static::$instances[$key] = new $class($username);
        }
        
        return static::$instances[$key];
    }

    public function getData(): array
    {
        try {
            $cache = $this->getCache();
            if ($cache !== false) {
                return $cache;
            }

            $data = $this->fetchData();
            $this->setCache($data);
            return $data;
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    abstract protected function fetchData(): array;

    protected function getCache(): mixed
    {
        $cache_id = $this->getCacheId();
        $cache_file = $this->getCachePath($cache_id);

        if (file_exists($cache_file)) {
            $cache_time = filemtime($cache_file);
            if (time() - $cache_time < $this->cache_time) {
                return json_decode(file_get_contents($cache_file), true);
            }
        }

        return false;
    }

    protected function setCache(array $data): void
    {
        $cache_id = $this->getCacheId();
        $cache_file = $this->getCachePath($cache_id);
        
        // Ensure cache directory exists
        $cache_dir = dirname($cache_file);
        if (!is_dir($cache_dir)) {
            mkdir($cache_dir, 0777, true);
        }

        file_put_contents($cache_file, json_encode($data));
    }

    protected function getCacheId(): string
    {
        return strtolower(basename(str_replace('\\', '/', get_class($this)))) . '_' . $this->username;
    }

    protected function getCachePath(string $cache_id): string
    {
        return JPATH_CACHE . '/plg_system_livestatus/' . $cache_id . '.json';
    }

    protected function httpGet(string $url, array $headers = []): string
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');
        
        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new \RuntimeException("HTTP request failed: $error");
        }

        return $response;
    }

    public static function getPlatformIcon(string $platform): string
    {
        return match($platform) {
            'tiktok' => 'tiktok',
            'youtube' => 'youtube',
            'twitch' => 'twitch',
            default => throw new \RuntimeException("Unknown platform: $platform")
        };
    }
}
