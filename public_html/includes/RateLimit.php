<?php
class RateLimit {
    private $redis;
    private $prefix = 'rate_limit:';
    private $window = 3600; // 1 hour window
    
    public function __construct() {
        $this->redis = new Redis();
        $this->redis->connect('127.0.0.1', 6379);
    }
    
    public function check($key, $limit) {
        $current = $this->redis->get($this->prefix . $key);
        if (!$current) {
            $this->redis->setex($this->prefix . $key, $this->window, 1);
            return true;
        }
        
        if ($current >= $limit) {
            return false;
        }
        
        $this->redis->incr($this->prefix . $key);
        return true;
    }
}
