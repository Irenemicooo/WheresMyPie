<?php
class Cache {
    private $cacheDir;
    private $ttl;

    public function __construct($cacheDir = '../private/cache/', $ttl = 3600) {
        $this->cacheDir = $cacheDir;
        $this->ttl = $ttl;
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0777, true);
        }
    }

    public function get($key) {
        $filename = $this->getCacheFile($key);
        if (!file_exists($filename)) {
            return null;
        }

        $data = unserialize(file_get_contents($filename));
        if ($data['expires'] < time()) {
            unlink($filename);
            return null;
        }

        return $data['value'];
    }

    public function set($key, $value, $ttl = null) {
        $data = [
            'expires' => time() + ($ttl ?? $this->ttl),
            'value' => $value
        ];
        
        return file_put_contents(
            $this->getCacheFile($key),
            serialize($data)
        );
    }

    private function getCacheFile($key) {
        return $this->cacheDir . md5($key) . '.cache';
    }
}
