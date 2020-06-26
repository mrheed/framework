<?php

namespace Ez;

class Request {

    public static function server($var, $default = ''){

        return isset($_SERVER[$var]) ? $_SERVER[$var] : $default;
    }
    
    private static function getBody(){


        if (in_array(self::method(), ['POST', 'PUT', 'PATCH'])){
           
            return file_get_contents('php://input');
        }

        return false;
    }

    private static function parseQuery($url){
        
        $params = [];

        $args = parse_url($url);

        if (isset($args['query'])){
            
            parse_str($args['query'], $params);
        }

        return $params;
    }

    public static function base()
    {

        return str_replace(['\\',' '], ['/','%20'],
            dirname(self::server('SCRIPT_NAME')));
    }

    public static function url()
    {

        $url =  str_replace('@', '%40', self::server('REQUEST_URI', '/'));

        if (strpos($url, '?')){
            
            $url = explode('?', $url)[0];      
        }

        if (self::base() != '/' and
            strlen(self::base()) > 0 and
            strpos($url, self::base()) === 0){
            
            return substr($url, strlen(self::base()));
        }

        if (empty($url)) return '/';

        return $url;
    }

    public static function method(){

        $method = self::server('REQUEST_METHOD', 'GET');

        if (isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'])){
            $method = $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'];
        }
        elseif (isset($_REQUEST['_method'])){
            $method = $_REQUEST['_method'];
        }

        return strtoupper($method);
    }

    public static function isAjax()
    {

        return self::server('HTTP_X_REQUESTED_WITH') == 'XMLHttpRequest';
    }

    public static function query($key = null)
    {

        if (!empty(self::url())){

            $_GET += self::parseQuery(self::url());   
        }

        if ($key){
            
            return isset($_GET[$key]) ? $_GET[$key] : null;
        }

        return new Collection($_GET);
    }

    public static function data($key = null)
    {

        if (strpos(self::server('CONTENT_TYPE'), 'application/json') === 0){
            
            $body = self::getBody();

            if ($body != ''){
                
                $data = json_decode($body, true);
                
                if ($data != null) return new Collection($data);
            }
        }

        if ($key){
    
            return isset($_POST[$key]) ? $_POST[$key] : null;
        }

        return new Collection($_POST);
    }

    public static function cookies()
    {

        return new Collection($_COOKIE);
    }

    public static function file($key = null)
    {

        if ($key){
    
            return isset($_FILES[$key]) ? new Collection($_FILES[$key]) : null;
        }

        return new Collection($_FILES);
    }
}