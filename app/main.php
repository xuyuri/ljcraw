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
Craw::crawBuild('1111027382209', 1, array(1));
//echo Craw::crawArea();
//echo Craw::crawLine();
/*$result = Craw::crawDistrict();
echo "----END---total-- = $result <br>";*/