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

//Craw::crawArea();
//Craw::crawLine();
getSiteArea();

//获取地铁站点对应的行政区
function getSiteArea() {
    $file = 'site_area_'.LjConfig::CITY.'.txt';
    echo "------- START --------\n";
    $fields = array('name', 'lj_no');
    $condition = ' WHERE type = 2 ';
    $data = ZDBTool::getQuery('t_line', $fields, $condition);
    if (empty($data)) {
        echo "无站点数据\n";
        return;
    }
    $num = 0;
    $result = array();
    foreach($data as $k => $v) {
        $num ++;
        $parse_data = array();
        $url = 'https://'.LjConfig::CITY.'.lianjia.com/ditiezufang/'.$v['lj_no'];
        $content = Helper::getContents($url);
        if (!empty($content)) {
            $parse_data = parseBuild($content, $v['lj_no']);
        }
        if ($num % 50 == 0) {
            sleep(5);
        } elseif ($parse_data) {
            sleep(3);
        }
        echo "----- NUM: $num [".$v['name']."] total = ".count($parse_data)."-----\n\n";
        $result[$v['name']] = array_values($parse_data);
    }

    $content = json_encode($result);
    file_put_contents($file, $content, FILE_APPEND);
    echo "------- END --------";
}

function parseBuild($content, $lj_no) {
    $result = array();
    $page_size = 30;
    if (empty($content)) {
        echo "[parseBuild] content is empty\n";
        return $result;
    }
    //$head_preg = '~<h2>共有<span>(\d+)</span>套上海在租房源~';
    $head_preg = '~已为您找到 <span class="content__title--hl">(\d+)</span> 套~';
    //已为您找到 <span class="content__title--hl">11</span> 套
    $head = array();
    preg_match($head_preg, $content, $head);
    if (empty($head)) {
        echo "[parseBuild] HEAD is empty \n";
        return $result;
    }
    $total = $head[1];
    echo "[parseBuild-$lj_no] total = $total\n";
    if($total > 0 && $total < 10000) {      //添加小于1万的逻辑，因为http://bj.lianjia.com/zufang/c1112900488982796/，小区不存在，返回的是所有房源
        $result = parseBuildPage($content);
        if (empty($result)) {
            echo "[parseBuild] parseBuildPage is empty \n";
            return $result;
        }
        $page = ceil($total / $page_size);
//        echo "[parseBuild] total page = $page \n";
        if($page > 1) {
            for($i=2; $i<=$page; $i++) {
                $page_url = "https://".LjConfig::CITY.".lianjia.com/ditiezufang/$lj_no/pg$i/";
                $contents = Helper::getContents($page_url);
                if (!empty($contents)) {
                    $page_data = parseBuildPage($contents);
                    if (!empty($page_data)) {
                        $result = array_merge($result, $page_data);
                    }
                }
            }
        }
    } else {
        echo "[parseBuild] total is 0 \n";
        return $result;
    }
    $result = array_unique($result);
    return $result;
}

function parseBuildPageV1($content) {
    $result = array();
    if (empty($content)) {
        echo "[parseBuildPage] content is empty\n";
        return $result;
    }
    //@todo 解析一页数据
    $list_preg = '~<li data-index="\d+" data-id="\S+">[\s\S]*?</li>~';
    $list = array();
    preg_match_all($list_preg, $content, $list);
    if (empty($list)) {
        echo "[parseBuildPage] list is empty\n";
        return $result;
    }
    $list = $list[0];

    foreach($list as $k => $v) {
        preg_match('~data-id="(\S+)"~', $v, $buildid);
//        echo "--- buildid --- = ".json_encode($buildid);
        if(!empty($buildid)) {
            preg_match('~<div class="con"><a href="https://'.LjConfig::CITY.'.lianjia.com/zufang/.*?/">(.*?)租房</a>~', $v, $area);
            $result[] = $area ? $area[1] : '';
        }
    }
    $result = array_unique($result);
    return $result;
}

function parseBuildPage($content) {
	$result = array();
	if (empty($content)) {
		echo "[parseBuildPage] content is empty\n";
		return $result;
	}
	//@todo 解析一页数据
	//$list_preg = '~<li data-index="\d+" data-id="\S+">[\s\S]*?</li>~';
	$list_preg = '~<p class="content__list--item--des">[\s\S]*?</p>~';
	$list = array();
	preg_match_all($list_preg, $content, $list);
	if (empty($list)) {
		echo "[parseBuildPage] list is empty\n";
		return $result;
	}
	$list = $list[0];
	//print_r($list);

	foreach($list as $k => $v) {
		preg_match_all('~href="/zufang/(\S+)/"~', $v, $buildids);
        //echo "--- buildid --- = ".json_encode($buildids);
//        print_r($buildids[1]);
		if(!empty($buildids) && !empty($buildids[1])) {
			$result = array_merge($result, $buildids[1]);
		}
	}
	$result = array_unique($result);
//	print_r($result);
	return $result;
}



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
//Craw::crawData();
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

