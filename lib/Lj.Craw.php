<?php
/**
 * Created by PhpStorm.
 * User: Eliot
 * Date: 2016/11/9
 * Time: 22:43
 */
require_once "Lj.Config.php";
require_once "Lj.Helper.php";
Class Craw {

    /**
     * 抓取页码数据
     * @param int $page     页码
     * @return array        解析后数据集
     * @author              yurixu 2016-11-09
     * @example             Craw::crawPage();
     */
    public static function crawPage($page=1) {
        $page = Helper::CheckPlusInt($page);
        $result = array();

        if($page > 0) {
            $url = LjConfig::BASE_URL.$page;
            $content = file_get_contents($url);
            if(!empty($content)) {
                $result = self::parseData($content);
            }
        }
        return $result;
    }

    /**
     * 解析页面数据
     * @param string $content   页码数据内容
     * @return array            解析后数据集
     * @author                  yurixu 2016-11-09
     * @example                 Craw::parseData();
     */
    protected static function parseData($content) {
        $result = array();
        if(!empty($content)) {
            //@todo 解析一页数据
            $list_preg = '~<li data-index="\d+" data-id="\d+">[\s\S]*?</li>~';
            $list = array();
            preg_match_all($list_preg, $content, $list);
            print_r($list);
            if(!empty($list)) {
                $cell_preg = '';
                foreach($list as $k => $v) {

                }
            }
        }
        return $result;
    }
}