<?php

include('craw.php');
function main() {
    $url = 'http://bj.lianjia.com/zufang/pg1/';
    $craw = new Craw;    
    $data = $craw->crawl($url);
    print_r($data);
}

main();