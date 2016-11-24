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

echo Craw::crawBuild();

//Craw::initTable();

//echo Craw::crawArea();
//echo Craw::crawLine();
/*$result = Craw::crawDistrict();
echo "----END---total-- = $result <br>";*/
/*$info = array(
    'buildid' => 1,
    '20161122' => '100',
);
echo ZDBTool::updateRow('t_stat_201611_day', 1, $info);*/