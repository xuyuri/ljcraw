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
                        preg_match('~<div class="con"><a href="http://bj.lianjia.com/zufang/(.*?)/">(.*?)</a><span>/</span>(.*?)楼层\(共(\d+)层\)<span>/</span>(.*?)</div>~', $v, $area);
                        //区域ID
                        $info['areaid'] = $area ? $area[1] : '';
                        //区域名称
                        $info['area'] = $area ? $area[2] : '';
                        //楼层阶级
                        $level = $area ? $area[3] : '';
                        if(!empty($level)) {
                            switch($level) {
                                case '高':
                                    $info['floor'] = 3;
                                    break;
                                case '中':
                                    $info['floor'] = 2;
                                    break;
                                case '低':
                                    $info['floor'] = 1;
                                    break;
                            }
                        }
                        //楼层
                        $info['floor'] = $area ? $area[4] : 0;
                        //板楼
                        $info['banlou'] = $area ? $area[5] : '';
                        preg_match('~<span class="fang-subway-ex"><span>距离(\d+)号线(.*?)站.*?</span>~',$v, $subway);
                        //地铁线
                        $info['line'] = $subway ? $subway[1] : 0;
                        //地铁站点
                        $info['station'] = $subway ? $subway[2] : '';
                        preg_match('~<span class="decoration-ex"><span>(.*?)</span>~',$v, $decoration);
                        //装修类型
                        $info['decoration'] = $decoration ? $decoration[1] : 0;
                        preg_match('~<span class="heating-ex"><span>(.*?)</span>~',$v, $heating);
                        //供暖类型
                        $info['heating'] = $heating ? $heating[1] : 0;
                        preg_match_all('~<span class="num">(\d+)</span>~', $v, $price);
                        //价格
                        $info['price'] = $price ? $price[1][0] : 0;
                        //看房认识
                        $info['visit'] = $price ? $price[1][1] : 0;
                        preg_match('~<div class="price-pre">(.*?)更新</div>~', $v, $update);
                        //更新日期
                        $info['update'] = $update ? $update[1] : '';
                        print_r($info);


                    }
                    //print_r($buildid);
                }
            }
        }
        return $result;
    }
}