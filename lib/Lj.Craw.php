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
    private $tool = null;

    public function init() {
        $this->tool = new ZDBTool();
    }

    /**
     * 抓取页码数据
     * @param int $page     小区编码
     * @return array        解析后数据集
     * @author              yurixu 2016-11-20
     * @example             Craw::crawBuild();
     */
    public static function crawBuild($district, $areaid, $line) {
        $district = Helper::EscapeString($district);
        $areaid = Helper::CheckPlusInt($areaid);
        $line = is_array($line) && !empty($line) ? $line : array();
        $result = array();

        if(!empty($district) && $areaid > 0 && !empty($line)) {
            $url = LjConfig::DETAIL_BASE_URL.'/c'.$district;
            $content = Helper::getContents($url);
            if(!empty($content)) {
                $result = self::parseBuild($content, $district, $areaid, $line);
            }
        }
        return $result;
    }

    /**
     * 抓取区域数据（仅运行一次）
     * @return array        插入数据表t_area记录数
     * @author              yurixu 2016-11-17
     * @example             Craw::crawArea();
     */
    public static function crawArea() {
        $result = 0;
        $sql = ' SELECT id, name, lj_no FROM t_area WHERE parentid = 0  ';
        $tool = new ZDBTool();
        $area = $tool->queryAll($sql);
        foreach($area as $k => $v) {
            $url = $url = LjConfig::DETAIL_BASE_URL.$v['lj_no'];
            $contents = Helper::getContents($url);
            if(!empty($contents)) {
                $result += self::parseArea($contents, $v['id']);
            }
        }
        return $result;
    }

    /**
     * 抓取地铁数据（仅运行一次）
     * @return array        插入数据表t_line记录数
     * @author              yurixu 2016-11-17
     * @example             Craw::crawLine();
     */
    public static function crawLine() {
        $result = 0;
        $sql = ' SELECT id, name, lj_no FROM t_line WHERE parentid = 0 ';
        $tool = new ZDBTool();
        $line = $tool->queryAll($sql);
        foreach($line as $k => $v) {
            $url = $url = LjConfig::LINE_BASE_URL.$v['lj_no'];
            $contents = Helper::getContents($url);
            if(!empty($contents)) {
                $result += self::parseLine($contents, $v['id']);
            }
        }
        return $result;
    }

    /**
     * 抓取小区数据（仅运行一次）
     * @return array        插入小区数据表t_district记录数
     * @author              yurixu 2016-11-20
     * @example             Craw::crawDistrict();
     */
    public static function crawDistrict() {
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '512M');
        $result = 0;
        $tool = new ZDBTool();
        $sql = 'SELECT id, name FROM `t_line` ';
        $line = $tool->queryAll($sql);
        if(!empty($line)) {
            $sql = ' SELECT id, lj_no, parentid FROM t_area WHERE parentid > 0 ';
            $area = $tool->queryAll($sql);
            foreach ($area as $k => $v) {
                echo $v['lj_no']."\n";
                $url = LjConfig::DISTRICT_BASE_URL . $v['lj_no'];
                $contents = Helper::getContents($url);
                if (!empty($contents)) {
                    $result += self::parseDistrict($contents, $line, $v['parentid'], $v['id'], $url);
                    echo "---result--- = $result ---\n";
                }
                sleep(3);
            }
        }
        return $result;
    }

    public static function parseBuild($content, $district, $areaid, $line) {
        //http://bj.lianjia.com/zufang/pg2c2011047640650/
        $result = 0;
        $page_size = 30;
        if(!empty($content) && !empty($district) && !empty($line) && $areaid > 0) {
            $head_preg = '~<h2>共有<span>(\d+)</span>套北京在租房源~';
            $head = array();
            preg_match($head_preg, $content, $head);
            if (!empty($head)) {
                $total = $head[1];
                echo "--total--=$total\n";
                if($total > 0) {
                    $result = self::parseBuildPage($content, $areaid, $line);
                    echo "---num--- = $result\n";
                    if($result > 0) {
                        $page = ceil($total / $page_size);
                        if($page > 1) {
                            for($i=2; $i<=$page; $i++) {
                                $page_url = LjConfig::DETAIL_BASE_URL.'pg'.$i.'/c'.$district;
                                $contents = Helper::getContents($page_url);
                                if (!empty($contents)) {
                                    $num = self::parseBuildPage($contents, $areaid, $line);
                                    echo "---num--- = $num\n";
                                    $result += $num;
                                }
                            }
                        }
                    }
                    echo "--result=$result\n";
                }
            }
        }
    }
    /**
     * 解析房源页面数据
     * @param string $content   页码数据内容
     * @return array            解析后数据集
     * @author                  yurixu 2016-11-09
     * @example                 Craw::parsePage();
     */
    protected static function parseBuildPage($content, $areaid, $line) {
        $result = 0;
        if(!empty($content) && $areaid > 0 && !empty($line)) {
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
                    preg_match('~data-id="(\S+)"~', $v, $buildid);
                    if(!empty($buildid)) {
                        //房源ID
                        $info['build_no'] = $buildid[1];
                        //房源详情页
                        $info['url'] = LjConfig::DETAIL_BASE_URL.$info['build_no'].'.html';
                        preg_match('~data-img="(.*?)"~', $v, $cover);
                        //房源封面图
                        $info['cover'] = $cover ? $cover[1] : '';
                        preg_match('~<a target="_blank" href="\S+" title="[\s\S]+?">([\s\S]+?)</a>~', $v, $title);
                        //房源标题
                        $info['title'] = $title ? preg_replace('~\s+~', ' ', $title[1]) : '';

                        /*preg_match('~http://bj.lianjia.com/xiaoqu/(\d+)/~', $v, $regionid);
                        //小区ID
                        $info['district'] = $regionid ? $regionid[1] : 0;
                        preg_match('~<span class="region">([\s\S]*?)</span>~', $v, $region);
                        //小区名称
                        $info['district_name'] = $region ? str_replace('&nbsp;', '', $region[1]) : '';*/



                        preg_match('~<span class="zone">\s*<span>([\s\S]*?)</span>~', $v, $zone);
                        //房屋户型
                        $info['zone'] = $zone ? str_replace('&nbsp;', '', $zone[1]) : '';
                        preg_match('~<span class="meters">(\d+).*?</span>\s*<span>([\s\S]*?)</span>~', $v, $meters);
                        //面积
                        $info['meters'] = $meters ? str_replace('&nbsp;', '', $meters[1]) : '';
                        //朝向
                        $info['direction'] = $meters ? str_replace('&nbsp;', '', $meters[2]) : '';
                        preg_match('~<div class="con"><a href="http://bj.lianjia.com/zufang/(.*?)/">(.*?)租房</a><span>/</span>(.*?)楼层\(共(\d+)层\)<span>/</span>(\d+)年建(.*?)</div>~', $v, $area);
                        //区域ID
                        $info['area'] = $area ? $area[1] : '';
                        //区域名称
                        $info['area_name'] = $area ? $area[2] : '';
                        //楼层阶级
                        $level = $area ? $area[3] : '';
                        if(!empty($level)) {
                            switch($level) {
                                case '高':
                                    $info['locate'] = 3;
                                    break;
                                case '中':
                                    $info['locate'] = 2;
                                    break;
                                case '低':
                                    $info['locate'] = 1;
                                    break;
                            }
                        }
                        //楼层
                        $info['floor'] = $area ? $area[4] : 0;
                        //建造时间
                        $info['build_year'] = $area ? $area[5] : '0';
                        //板楼类型
                        $info['build_type'] = $area ? $area[6] : '';
                        preg_match('~<span class="fang-subway-ex"><span>距离(\d+号线)(.*?)站.*?</span>~',$v, $subway);
                        //地铁线
                        $info['line'] = $subway ? $subway[1] : 0;
                        //地铁站点
                        $info['station'] = $subway ? $subway[2] : '';
                        preg_match('~<span class="decoration-ex"><span>(.*?)</span>~',$v, $decoration);
                        //装修类型
                        $info['decoration'] = $decoration ? $decoration[1] : '';
                        preg_match('~<span class="independentBalcony-ex"><span>(.*?)</span>~',$v, $balcony);
                        //阳台类型
                        $info['balcony'] = $balcony ? $balcony[1] : '';
                        preg_match('~<span class="privateBathroom-ex"><span>(.*?)</span>~',$v, $bathroom);
                        //卫生间类型
                        $info['bathroom'] = $bathroom ? $bathroom[1] : '';
                        preg_match('~<span class="heating-ex"><span>(.*?)</span>~',$v, $heating);
                        //供暖类型
                        $info['heating'] = $heating ? $heating[1] : '';
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

    /**
     * 解析区域页面数据
     * @param string $content   页码数据内容
     * @return array            解析后数据集
     * @author                  yurixu 2016-11-16
     * @example                 Craw::parseArea();
     */
    public static function parseArea($content, $parentid) {
        $parentid = Helper::CheckPlusInt($parentid);
        $result = 0;
        $area = array();
        $table = 't_area';
        if(!empty($content)) {
            $head_preg = '~<div class="option-list sub-option-list">([\s\S]*?)</div>~';
            $head = array();
            preg_match($head_preg, $content, $head);
            if (!empty($head)) {
                preg_match_all('~<a href="/zufang/(\S+?)/">(\S+?)</a>~', $head[0], $list);
                if(!empty($list)) {
                    $area_no = $list[1];
                    $area_name = $list[2];
                    foreach($area_name as $k => $v) {
                        $area[$k]['lj_no'] = $area_no[$k];
                        $area[$k]['name'] = $v;
                        $area[$k]['parentid'] = $parentid;
                    }
                    $result = ZDBTool::multiInsert($table, $area);
                }
            }
        }
        return $result;
    }

    /**
     * 解析地铁线路页面数据
     * @param string $content   页码数据内容
     * @return array            解析后数据集
     * @author                  yurixu 2016-11-17
     * @example                 Craw::parseLine();
     */
    public static function parseLine($content, $parentid) {
        $parentid = Helper::CheckPlusInt($parentid);
        $result = 0;
        $line = array();
        $table = 't_line';
        if(!empty($content)) {
            $head_preg = '~<div class="option-list sub-option-list">([\s\S]*?)</div>~';
            $head = array();
            preg_match($head_preg, $content, $head);
            if (!empty($head)) {
                preg_match_all('~<a href="/ditiezufang/(\S+?)/">(\S+?)</a>~', $head[0], $list);
                if(!empty($list)) {
                    $subway_no = $list[1];
                    $subway_name = $list[2];
                    foreach($subway_name as $k => $v) {
                        $line[$k]['type'] = 2;
                        $line[$k]['lj_no'] = $subway_no[$k];
                        $line[$k]['name'] = $v;
                        $line[$k]['parentid'] = $parentid;
                    }
                    $result = ZDBTool::multiInsert($table, $line);
                }
            }
        }
        return $result;
    }

    /**
     * 解析小区列表页面数据
     * @param string  $content  小区列表数据内容
     * @param array $line       地铁站点数据
     * @param int $areaid       二级区域ID
     * @return int              写入区域表数据记录数
     * @author                  yurixu 2016-11-20
     * @example                 Craw::parseDistrict();
     */
    public static function parseDistrict($content, $line, $areaPid, $areaid, $url) {
        $areaPid = Helper::CheckPlusInt($areaPid);
        $areaid = Helper::CheckPlusInt($areaid);
        $result = 0;
        $page_size = 30;
        if(!empty($content) && !empty($line) && !empty($url) && $areaid > 0) {
            $head_preg = '~<h2 class="total fl">共找到<span>\s*(\d+?)\s*</span>个小区</h2>~';
            $head = array();
            preg_match($head_preg, $content, $head);
            if (!empty($head)) {
                $total = $head[1];
                echo "--total--=$total\n";
                if($total > 0) {
                    $result = self::parseDistrictPage($content, $line, $areaPid, $areaid);
                    echo "---num--- = $result\n";
                    if($result > 0) {
                        $page = ceil($total / $page_size);
                        if($page > 1) {
                            for($i=2; $i<=$page; $i++) {
                                $page_url = $url.'/pg'.$i;
                                $contents = Helper::getContents($page_url);
                                if (!empty($contents)) {
                                    $num = self::parseDistrictPage($contents, $line, $areaPid, $areaid);
                                    echo "---num--- = $num\n";
                                    $result += $num;
                                }
                            }
                        }
                    }
                    echo "--result=$result\n";
                }
            }
        }
        return $result;
    }

    /**
     * 解析小区列表页面数据-子方法
     * @param string  $content  小区列表数据内容
     * @param array $line       地铁站点数据
     * @param int $areaid       二级区域ID
     * @return int              写入区域表数据记录数
     * @author                  yurixu 2016-11-20
     * @example                 Craw::parseDistrictPage();
     */
    public static function parseDistrictPage($content, $line, $areaPid, $areaid) {
        $areaid = Helper::CheckPlusInt($areaid);
        $areaPid = Helper::CheckPlusInt($areaPid);
        $result = 0;
        $district = array();
        $lineid = 0;
        $siteid = 0;
        $table = 't_district';
        /*$file = '../data/district.txt';
        $content = file_get_contents($file);*/
        if(!empty($content) && !empty($line) && $areaid > 0) {
            $line_name = array_column($line, 'name');
            $line_flip = array_flip($line_name);
            $head_preg = '~<div class="title">\s*<a href="http://bj.lianjia.com/xiaoqu/(\d+)/" target="_blank">\S+</a>\s*</div>[\s\S]*?<div class="tagList">([\s\S]*?)</div>~';
            $list = array();
            preg_match_all($head_preg, $content, $list);
            if (!empty($list)) {
                $district_no = $list[1];
                $district_line = $list[2];
                foreach($district_no as $k => $v) {
                    if(!empty($district_line[$k])) {
                        preg_match('~近地铁(\S+线)(\S+)站~', $district_line[$k], $site);
                        if(!empty($site)) {
                            $name = $site[1];
                            $site_name = $site[2];
                            if(isset($line_flip[$name])) {
                                $lineid = $line[$line_flip[$name]]['id'];
                            }
                            if(isset($line_flip[$site_name])) {
                                $siteid = $line[$line_flip[$site_name]]['id'];
                            }
                        }
                    }
                    $district[$k]['lj_no'] = $v;
                    $district[$k]['areaPid'] = $areaPid;
                    $district[$k]['areaid'] = $areaid;
                    $district[$k]['lineid'] = $lineid;
                    $district[$k]['siteid'] = $siteid;
                }
                $result = ZDBTool::multiInsert($table, $district);
            }
        }
        return $result;
    }
}