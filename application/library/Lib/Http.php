<?php
/**
 * http请求类
 * @author wangliuyang
 */
namespace Lib;

class Http
{
    /**
     * Contains the last HTTP status code returned.
     */
    public $httpCode;
    /**
     * Contains the last API call.
     */
    public $url;
    /**
     * Set up the API root URL.
     */
    public $host;
    /**
     * Set timeout default.
     */
    public $timeout = 5;
    /**
     * Set connect timeout.
     */
    public $connectTimeout = 5;
    /**
     * Respons format.
     */
    public $format = 'json';
    /**
     * Decode returned json data.
     */
    public $decodeJson = TRUE;
    /**
     * Contains the last HTTP headers returned.
     */
    public $httpInfo;
    /**
     * print the debug info
     */
    public $debug = FALSE;
    /**
     * Verify SSL Cert.
     */
    public $sslVerifypeer = FALSE;
    /**
     * Set the useragnet.
     */
    public $userAgent = 'uc';
    
    /**
     * boundary of multipart
     * @ignore
     */
    public static $boundary = '';
    
    public function __construct($host = '')
    {
        $this->host = $host;
    }
    
    /**
     * GET wrappwer for request.
     *
     * @return mixed
     */
    function get($url, $parameters = array(), $headers = array()) {
        $response = $this->request($url, 'GET', $parameters, false, $headers);
        if ($this->format === 'json' && $this->decodeJson) {
            return json_decode($response, true);
        }
        return $response;
    }
    
    /**
     * POST wreapper for request.
     *
     * @return mixed
     */
    function post($url, $parameters = array(), $multi = false, $headers = array()) {
        $response = $this->request($url, 'POST', $parameters, $multi , $headers);
        if ($this->format === 'json' && $this->decodeJson) {
            return json_decode($response, true);
        }
        return $response;
    }
    
    /**
     * DELTE wrapper for oAuthReqeust.
     *
     * @return mixed
     */
    function delete($url, $parameters = array()) {
        $response = $this->request($url, 'DELETE', $parameters);
        if ($this->format === 'json' && $this->decodeJson) {
            return json_decode($response, true);
        }
        return $response;
    }
    
    /**
     * Format and sign an OAuth / API request
     *
     * @return string
     * @ignore
     */
    function request($url, $method, $parameters, $multi = false, $headers = array()) {
    
        if (strrpos($url, 'http://') !== 0 && strrpos($url, 'https://') !== 0) {
            $url = "{$this->host}{$url}";
        }
    
        switch ($method) {
            case 'GET':
                $url .= strpos($url, '?') === false ? '?' : '';
                $url .= http_build_query($parameters);
                return $this->http($url, 'GET', null, $headers);
            default:                
                $body = $parameters;
                if($multi)
                {
                    $body = self::build_http_query_multi($parameters);
                    $headers[] = "Content-Type: multipart/form-data; boundary=" . self::$boundary;
                }
                elseif(is_array($parameters) || is_object($parameters))
                {
                    $body = http_build_query($parameters);
                }
                return $this->http($url, $method, $body, $headers);
        }
    }
    
    /**
     * Make an HTTP request
     *
     * @return string API results
     * @ignore
     */
    function http($url, $method, $postfields = NULL, $headers = array()) {
        $this->httpInfo = array();
        $ci = curl_init();
        /* Curl settings */
        curl_setopt($ci, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        curl_setopt($ci, CURLOPT_USERAGENT, $this->userAgent);
        curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, $this->connectTimeout);
        curl_setopt($ci, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ci, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ci, CURLOPT_ENCODING, "");
        curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, $this->sslVerifypeer);
        curl_setopt($ci, CURLOPT_HEADERFUNCTION, array($this, 'getHeader'));
        curl_setopt($ci, CURLOPT_HEADER, FALSE);
    
        switch ($method) {
            case 'POST':
                curl_setopt($ci, CURLOPT_POST, TRUE);
                if (!empty($postfields)) {
                    curl_setopt($ci, CURLOPT_POSTFIELDS, $postfields);
                }
                break;
            case 'GET':
                curl_setopt($ci, CURLOPT_FOLLOWLOCATION, 1);     
                break;
            case 'DELETE':
                curl_setopt($ci, CURLOPT_CUSTOMREQUEST, 'DELETE');
                if (!empty($postfields)) {
                    $url = "{$url}?{$postfields}";
                }
        }
       
        $headers[] = "API-RemoteIP: " . $_SERVER['REMOTE_ADDR'];
        curl_setopt($ci, CURLOPT_URL, $url );
        curl_setopt($ci, CURLOPT_HTTPHEADER, $headers );
        curl_setopt($ci, CURLINFO_HEADER_OUT, TRUE );
    
        $response = curl_exec($ci);
        $this->httpCode = curl_getinfo($ci, CURLINFO_HTTP_CODE);
        $this->httpInfo = array_merge($this->httpInfo, curl_getinfo($ci));
        $this->url = $url;
    
        if ($this->debug) {
            echo "=====error========\r\n";
            var_dump(curl_error($ci));
            echo "=====post data======\r\n";
            var_dump($postfields);
    
            echo '=====info====='."\r\n";
            print_r( curl_getinfo($ci) );
    
            echo '=====$response====='."\r\n";
            print_r( $response );
        }
        curl_close ($ci);
        return $response;
    }
    
    /**
     * Get the header info to store.
     *
     * @return int
     * @ignore
     */
    function getHeader($ch, $header) {
        $i = strpos($header, ':');
        if (!empty($i)) {
            $key = str_replace('-', '_', strtolower(substr($header, 0, $i)));
            $value = trim(substr($header, $i + 2));
         }
        return strlen($header);
    }
    

}