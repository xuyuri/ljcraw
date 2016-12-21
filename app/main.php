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
//ini_set('default_socket_timeout', -1);
//echo Helper::getWeekNumber(time());
/*$info = array(
    'buildid' => '1',
    'build_no' => 'a',
    'w1_start' => '20161201',
    'w1_end' => '20161207',
    'w1_low' => '100',
    'w1_high' => '200',
    'w1_ave' => '150',
);

$a = ZDBTool::multiInsert('t_stat_201611_week', array($info));
print_r($a);
die;*/


/*$last_week = date('Ymd', strtotime('-1 week'));
$day_list = Helper::getDateList($last_week, date('Ymd'));
echo implode('`,`', $day_list) ;die;*/

/*ZDBTool::redis()->set('sc:user', 'xyw');
$name = ZDBTool::redis()->get('sc:user');
echo $name;
//ZDBTool::redis()->close();*/
/*Craw::initTable();
die;*/
//echo Craw::crawBuild();
Craw::crawData();
die;
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

