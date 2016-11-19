<?php
/**
 * Created by PhpStorm.
 * User: Eliot
 * Date: 2016/11/9
 * Time: 22:45
 */
require_once "../lib/Lj.Craw.php";
require_once "../lib/Lj.ZDBTool.php";

$result = Craw::crawPage(101);
print_r($result);
//echo Craw::crawArea();
//echo Craw::crawSubWay();