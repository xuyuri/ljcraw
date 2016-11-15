<?php
/**
 * Created by PhpStorm.
 * User: Eliot
 * Date: 2016/11/9
 * Time: 22:45
 */
require_once "../lib/Lj.Craw.php";
require_once "../lib/Lj.ZDBTool.php";

//Craw::crawPage(1);

$sql = 'delete  FROM `t_area` where id = 2';
$tool = new ZDBTool();
$data = $tool->execute($sql);
print_r($data);