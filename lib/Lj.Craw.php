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
            $url = LjConfig::PAGE_BASE_URL.$page;
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
            $list_preg = '~<li data-index="\d+" data-id="\S+">[\s\S]*?</li>~';
            $list = array();
            preg_match_all($list_preg, $content, $list);
            //print_r($list);die;
            if(!empty($list)) {
                $list = $list[0];
                $cell_preg = '';
                foreach($list as $k => $v) {
                    //var_dump($v);die;
                    $info = array();
                    preg_match('~data-id="(\d+)"~', $v, $buildid);
                    if(!empty($buildid)) {
                        //房源ID
                        $info['buildid'] = $buildid[1];
                        //房源详情页
                        $info['detail'] = LjConfig::DETAIL_BASE_URL.$info['buildid'].'.html';
                        preg_match('~data-img="(.*?)"\s+alt="(.*?)"~', $v, $title);
                        //房源封面图
                        $info['cover'] = $title ? $title[1] : '';
                        //房源标题
                        $info['title'] = $title ? preg_replace('~\s+~', ' ', $title[2]) : '';
                        preg_match('~http://bj.lianjia.com/xiaoqu/(\d+)/~', $v, $regionid);
                        //小区ID
                        $info['regionid'] = $regionid ? $regionid[1] : 0;
                        preg_match('~<span class="region">([\s\S]*?)</span>~', $v, $region);
                        //小区名称
                        $info['region'] = $region ? str_replace('&nbsp;', '', $region[1]) : '';
                        preg_match('~<span class="zone">\s*<span>([\s\S]*?)</span>~', $v, $zone);
                        //房屋户型
                        $info['zone'] = $zone ? str_replace('&nbsp;', '', $zone[1]) : '';
                        preg_match('~<span class="meters">([\s\S]*?)</span>\s*<span>([\s\S]*?)</span>~', $v, $meters);
                        //面积
                        $info['meters'] = $meters ? str_replace('&nbsp;', '', $meters[1]) : '';
                        //朝向
                        $info['direction'] = $meters ? str_replace('&nbsp;', '', $meters[2]) : '';
                        print_r($info);


                    }
                    //print_r($buildid);
                }
            }
        }
        return $result;
    }
}