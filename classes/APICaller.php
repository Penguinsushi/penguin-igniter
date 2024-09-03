<?php

// =============================================================================================================
// USAGE EXAMPLE
// =============================================================================================================

// =============================================================================================================


class APICaller 
{
    
    // PROPERTIES

    public $url = '';
    public $headers = [];
    public $post_fields = null;
    public $post = false;
    public $get_fields = [];
    public $log_name = null;
    public $use_cache = false;
    public $ssl_verification = true;
    public $parse_response = null;
    
    protected $log = '';
    protected $start_time = 0;
    protected $end_time = 0;
    protected $data_id = '';
    protected $call_id = '';
    
    public $request_headers = '';
    public $request_data = '';
    
    public $response_headers = '';
    public $response_data = '';
    public $response_time = 0;
    public $response_error = false;
    public $response_raw = '';
    public $response_parsed = '';
    public $http_status_code = 0;    
    
    public function __construct($data = []) 
    {
        foreach($data as $prop => $value) {
            $this->$prop = $value;
        }
    }
    
    public static function call($props = array())
    {
        $caller = new self($props);
        $caller->init();
        if (!empty($caller->log_name) && !empty($GLOBALS['log'])) {
            $caller->log = $GLOBALS['log'];
        }  
        $caller->startTime();
        $caller->logRequest();
        $caller->curlRequest();        
        $caller->stopTime();
        $caller->getResponseCode();
        $caller->logResponse();
        $caller->parseResponse();
        return $caller->response_parsed;
    }
    
    // PRIVATE
    
    private function init()
    {        
        $this->request_headers = '';
        $this->request_data = '';
        $this->response_time = '';
        $this->response_header = '';
        $this->response_data = '';
        $this->response_raw = '';
        $this->response_parsed = '';
        $this->http_status_code = 0;
        $this->setIds();
    }
    
    private function startTime() {
        clearstatcache();
        $this->time_start = microtime(true);
    }
    
    private function stopTime() {
        clearstatcache();
        $this->end_time = microtime(true);
        $this->response_time = $this->end_time - $this->time_start;
    }
    
    private function setIds()
    {
        $this->data_id = md5($this->url . serialize($this->get_fields) . serialize($this->post_fields));
        $this->call_id = md5($this->data_id . microtime(true));
    }
    
    private function logRequest()
    {
        if (empty($this->log)) {
            return;
        }
        $this->log->writeEntry(' API CALL REQUEST ' . $this->call_id . ' :: ' . print_r($this, true) );        
    }
    
    private function logResponse()
    {
        if (empty($this->log)) {
            return;
        }
        $this->log->writeEntry(' API CALL RESPONSE ' . $this->call_id . ' :: ' . print_r($this, true) );  
    }
    
    private function getResponseCode()
    {
        if (!empty($this->response_headers)) {
            $hc = explode(' ', $this->response_headers);
            $this->http_status_code = $hc[2];
        } else {
            $this->http_status_code = 200;
        }
    }
    
    private function curlRequest()
    {
        if (!empty($this->get_fields)) {
            $q = [];
            foreach ($this->get_fields as $field => $value) {
                $q[] = $field . '=' . urlencode($value);
            }
            $this->url .= '?' . implode('&', $q);
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if (!$this->ssl_verification) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }
        if ($this->post || !empty($this->post_fields)) {
            curl_setopt($ch, CURLOPT_POST, 1);
        }
        if (!empty($this->post_fields)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $this->post_fields);
        }
        if (!empty($this->headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $this->compileCurlHeaders());
        }
        $rdata = curl_exec($ch);
        if (isset($this->get_fields)) {
            $this->request_data .= ' GET :: ' . print_r($this->get_fields, true) . ' ';
        }
        if (isset($this->post_fields)) {
            $this->request_data .= ' POST :: ' . print_r($this->post_fields, true) . ' ';
        }
        $this->request_headers = curl_getinfo($ch, CURLINFO_HEADER_OUT);
        $raw = print_r($rdata, true);
        $this->response_raw = $raw;
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $this->response_headers = trim(substr($rdata, 0, $header_size));
        $this->response_data = trim(substr($rdata, $header_size));
        if (!empty(curl_errno($ch))) {
            $this->response_error = true;
            $this->response_data = curl_strerror(curl_errno($ch));
        }
        curl_close($ch);
    }

    private function compileCurlHeaders()
    {
        $headers = [];
        foreach ($this->headers as $header => $val) {
            $headers[] = "$header: $val";
        }
        return $headers;
    }
    
    private function parseResponse()
    {
        $parse = strtolower($this->parse_response);
        if ($parse == 'json') {
            $this->response_parsed = json_decode($this->response_data);
        } elseif ($parse == 'serialized') {
            $this->response_parsed = unserialize($this->response_data);
        } else {
            $this->response_parsed = $this->response_data;
        }
    }
    
}
