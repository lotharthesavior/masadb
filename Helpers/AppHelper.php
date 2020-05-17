<?php

namespace Helpers;

class AppHelper 
{
    
    /**
     * Split the string by lines
     * 
     * @param string $string
     * @return Array
     */
    public static function splitByLine( string $string )
    {
        $vector = preg_split('/$\R?^/m', $string);

        return $vector;
    }

    /**
     * Reference: https://stackoverflow.com/questions/14587514/php-fire-and-forget-post-request
     * 
     * @todo put this in a helper
     * 
     * @param string $url
     * @param array $params
     * @param array $header 
     */
    public static function curlPostAsync(string $url, $params = array(), $header = array())
    {
        global $config;

        // create POST string   
        $post_params = array();
        foreach ($params as $key => &$val) {
            $post_params[] = $key . '=' . urlencode($val);
        }
        $post_string = implode('&', $post_params);

        // get URL segments
        $parts = parse_url($url);

        // workout port and open socket
        $port = isset($parts['port']) ? $parts['port'] : 80;
        $fp = fsockopen($parts['host'] ?? $config['settings']['domain-fallback'], $port, $errno, $errstr, 30);

        // create output string
        $output  = "POST " . $parts['path'] . " HTTP/1.1\r\n";
        $output .= "Host: " . $parts['host'] . "\r\n";
        
        if( isset($header['ClientId']) )
            $output .= "ClientId: " . $header['ClientId'] . "\r\n";
        
        if( isset($header['Authorization']) )
            $output .= "Authorization: " . $header['Authorization'] . "\r\n";
        
        if( isset($header['Content-Type']) )
            $output .= "Content-Type: " . $header['Content-Type'] . "\r\n";
        else
            $output .= "Content-Type: application/x-www-form-urlencoded\r\n";

        $output .= "Content-Length: " . strlen($post_string) . "\r\n";
        $output .= "Connection: Close\r\n\r\n";
        $output .= isset($post_string) ? $post_string : '';

        // send output to $url handle
        fwrite($fp, $output);

        // while (!feof($fp)) {
        //     echo fgets($fp, 128);
        // }
        fclose($fp);
        // exit();
    }

    /**
     * Return the memory used
     * 
     * Reference: http://php.net/manual/en/function.memory-get-usage.php#96280
     * 
     * @param string $size (when it comes not empty, 
     *        it will just convert Bytes to MB)
     */
    public static function getCurrentMemoryUsage( string $size = '' )
    {
        if( empty($size) )
            $size = memory_get_usage(true);
        $unit = array('b','kb','mb','gb','tb','pb');
        return @round( $size / pow( 1024, ($i = floor(log($size,1024))) ), 2 ) . ' ' . $unit[$i];
    }

}