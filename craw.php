<?php

/**
 * 
 */
class Craw
{
        
    public static function crawl($url) {        
        $result = '';
        if(!empty($url)) { 
            $page = file_get_contents($url);
            $result = $page;
        }
        return $result;
    }
}
