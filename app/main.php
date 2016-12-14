<?php
/**
 * Created by PhpStorm.
 * User: Eliot
 * Date: 2016/11/9
 * Time: 22:45
 */
require_once "../lib/Lj.Craw.php";
require_once "../lib/Lj.ZDBTool.php";

//Craw::crawBuild('2011047640650');

ini_set('max_execution_time', 0);
ini_set('memory_limit', '512M');

//Craw::initTable();

//echo Craw::crawBuild();

//Craw::statMonth();

//print_r(Helper::getDateList('20161028', '20161105'));
//echo Craw::crawArea();
//echo Craw::crawLine();
/*$result = Craw::crawDistrict();
echo "----END---total-- = $result <br>";*/
/*$info = array(
    'buildid' => 1,
    '20161122' => '100',
);
echo ZDBTool::updateRow('t_stat_201611_day', 1, $info);*/

/*class AsyncOperation extends Thread {
    public function __construct($arg){
        $this->arg = $arg;
    }
    public function run(){
        if($this->arg){
            printf("Hello %s\n", $this->arg);
        }
    }
}
$thread = new AsyncOperation("World");
if($thread->start())
    $thread->join();*/


class WebRequest extends Thread {
    public $url;
    public $data;

    public function __construct($url){
        $this->url = $url;
    }

    public function run(){
        if(($url = $this->url)){
            /*
            * If a large amount of data is being requested, you might want to
            * fsockopen and read using usleep in between reads
            */
            $this->data = file_get_contents($url);
        } else printf("Thread #%lu was not provided a URL\n", $this->getThreadId());
    }
}

$t = microtime(true);
$g = new WebRequest(sprintf("http://bj.lianjia.com/zufang/pg%s", rand(1, 10)));
/* starting synchronized */
if($g->start()){
    printf("Request took %f seconds to start ", microtime(true)-$t);
    while($g->isRunning()){
        echo ".";
        $g->synchronized(function() use($g) {
            $g->wait(100);
        });
    }
    if ($g->join()){
        printf(" and %f seconds to finish receiving %d bytes\n", microtime(true)-$t, strlen($g->data));
    } else printf(" and %f seconds to finish, request failed\n", microtime(true)-$t);

}