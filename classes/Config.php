<?php

class Config {
    
    private $values = [];
    
    public function __construct() {
        
    }
    
    public static function val($tag) {
        $config = self::getConfig();
        return $config->getVal($tag);
    }
    
    public static function load($config_array) {
        $config = self::single();
        $config->values = $config_array;
        return $config;
    }
    
    private function getVal($tag) {
        return $this->values[$tag] ?? null;
    }
    
    private static function single() {
        if (empty($GLOBALS['pi_config'])) {
            $GLOBALS['pi_config'] = new self();
        }
        return $GLOBALS['pi_config'];
    }
    
}
