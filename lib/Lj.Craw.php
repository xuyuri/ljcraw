<?php
/**
 * Created by PhpStorm.
 * User: Eliot
 * Date: 2016/11/9
 * Time: 22:43
 */
require_once "Lj.Config.php";
require_once "Lj.Helper.php";
//require_once "Lj.crawThread.php";
Class Craw
{
    /**
     * 每日执行数据表检测/初始化
     * @author              yurixu 2016-11-24
     * @example             Craw::initTable();
     */
    public static function initTable()
    {
        $month = date('Ym');
        $day = date('d');
        $date = date('Ymd');

        //每月1号新建日、周、月统计表
        if ($day == 1) {
            $stat_day_sql = "
                CREATE TABLE `t_stat_" . $month . "_day` (
                  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
                  `buildid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '房源ID，对应t_build表ID',
                  `build_no` varchar(50) NOT NULL DEFAULT '' COMMENT '链家房源编码',
                  `" . $date . "` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '" . $date . "日价格',
                  `create_time` datetime NOT NULL COMMENT '创建时间',
                  `operate_time` datetime NOT NULL COMMENT '最后操作时间',
                  PRIMARY KEY (`id`),
                  UNIQUE KEY `build_no` (`build_no`),
                  KEY `buildid` (`buildid`)
                ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='房源价格" . $month . "每日统计表';";
            $stat_week_sql = "
                CREATE TABLE `t_stat_" . $month . "_week` (
                  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
                  `buildid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '房源ID，对应t_build表ID',
                  `build_no` varchar(50) NOT NULL DEFAULT '' COMMENT '链家房源编码',
                  `w1_start` date NOT NULL COMMENT '第一周开始日期',
                  `w1_end` date NOT NULL COMMENT '第一周结束日期',
                  `w1_low` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '第一周最低价格',
                  `w1_high` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '第一周最高价格',
                  `w1_ave` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '第一周均价',
                  `w2_start` date NOT NULL COMMENT '第二周开始日期',
                  `w2_end` date NOT NULL COMMENT '第二周结束日期',
                  `w2_low` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '第二周最低价格',
                  `w2_high` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '第二周最高价格',
                  `w2_ave` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '第二周均价',
                  `w3_start` date NOT NULL COMMENT '第三周开始日期',
                  `w3_end` date NOT NULL COMMENT '第三周结束日期',
                  `w3_low` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '第三周最低价格',
                  `w3_high` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '第三周最高价格',
                  `w3_ave` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '第三周均价',
                  `w4_start` date NOT NULL COMMENT '第四周开始日期',
                  `w4_end` date NOT NULL COMMENT '第四周结束日期',
                  `w4_low` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '第四周最低价格',
                  `w4_high` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '第四周最高价格',
                  `w4_ave` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '第四周均价',
                  `w5_start` date NOT NULL COMMENT '第五周开始日期',
                  `w5_end` date NOT NULL COMMENT '第五周结束日期',
                  `w5_low` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '第五周最低价格',
                  `w5_high` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '第五周最高价格',
                  `w5_ave` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '第五周均价',
                  `create_time` datetime NOT NULL COMMENT '创建时间',
                  `operate_time` datetime NOT NULL COMMENT '最后操作时间',
                  PRIMARY KEY (`id`),
                  UNIQUE KEY `build_no` (`build_no`),
                  KEY `buildid` (`buildid`)
                ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='房源价格" . $month . "每周统计表';";
            $stat_month_sql = "
                CREATE TABLE `t_stat_" . $month . "_month` (
                  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
                  `buildid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '房源ID，对应t_build表ID',
                  `build_no` varchar(50) NOT NULL DEFAULT '' COMMENT '链家房源编码',
                  `low` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '本月最低价格',
                  `low_date` DATE NOT NULL COMMENT '最低价格日期',
                  `high` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '本月最高价格',
                  `high_date` DATE NOT NULL COMMENT '最高价格日期',
                  `average` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '本月均价',
                  `rate` decimal(4,2) NOT NULL DEFAULT '0.00' COMMENT '本月价格升/降率',
                  `create_time` datetime NOT NULL COMMENT '创建时间',
                  `operate_time` datetime NOT NULL COMMENT '最后操作时间',
                  PRIMARY KEY (`id`),
                  UNIQUE KEY `build_no` (`build_no`),
                  KEY `buildid` (`buildid`)
                ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='房源价格" . $month . "每月统计表';";
            ZDBTool::execute($stat_day_sql);
            ZDBTool::execute($stat_week_sql);
            ZDBTool::execute($stat_month_sql);
        }
        //每日向日统计表中添加当天日期的字段
        $stat_day_table = 't_stat_' . $month . '_day';
        $sql = 'SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE table_name = :table_name ;';
        $params = array(':table_name' => $stat_day_table);
        $columns = ZDBTool::queryAll($sql, $params);
        if (!empty($columns)) {
            $column = array_column($columns, 'COLUMN_NAME');
            if (!in_array($date, $column)) {
                $column_sql = "
                ALTER TABLE `" . $stat_day_table . "`
                ADD COLUMN `" . $date . "`  decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '" . $date . "日价格' AFTER `build_no`;";
                ZDBTool::execute($column_sql);
            }
        }
    }

    /**
     * 每月1日执行上月房源价格统计
     * @author              yurixu 2016-11-24
     * @example             Craw::statMonth();
     */
    public static function statMonth()
    {
        $result = 0;
        $day = 1;//date('d');
        if ($day == 1) {

            //上一月
            $month = '201611';//date('Ym', strtotime('-1 month'));
            $pre_day = 't_stat_' . $month . '_day';
            $pre_month = 't_stat_' . $month . '_month';
            $exclude = array('id', 'buildid', 'build_no', 'create_time', 'operate_time');
            $sql = 'SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE table_name = :table_name ;';
            $params = array(':table_name' => $pre_day);
            $columns = ZDBTool::queryAll($sql, $params);
            if (!empty($columns)) {
                $column = array_column($columns, 'COLUMN_NAME');
                $column = array_diff($column, $exclude);
                if (!empty($column)) {
                    $column[] = 'buildid';
                    $column[] = 'build_no';
                    $sql = ' SELECT MAX( id ) AS maxid FROM ' . $pre_day;
                    $total = ZDBTool::queryRow($sql);
                    if (!empty($total)) {
                        $maxid = $total['maxid'];
                        $pernum = 500;  //每次执行的条数
                        $pages = ceil($maxid / $pernum);
                        for ($i = 0; $i < $pages; $i++) {
                            echo "---i--- = $i\n";
                            $condition = ' WHERE id BETWEEN :start AND :end ';
                            $params = array(':start' => $i * 500 + 1, ':end' => ($i + 1) * 500);
                            $data = ZDBTool::getQuery($pre_day, $column, $condition, $params);
                            if (!empty($data)) {
                                foreach ($data as $k => $v) {
                                    $info = array();
                                    $tmp = $v;
                                    unset($tmp['buildid']);
                                    unset($tmp['build_no']);
                                    $tmp = array_filter($tmp, function ($e) {
                                        $e = (float)$e;
                                        return !empty($e);
                                    });
                                    if (!empty($tmp)) {
                                        asort($tmp);
                                        list($low_key, $low) = (reset($tmp) ? each($tmp) : each($tmp));
                                        list($high_key, $high) = (end($tmp) ? each($tmp) : each($tmp));
                                        $count = count($tmp);
                                        $sum = array_sum($tmp);
                                        $average = number_format($sum / $count, 2, '.', '');
                                        $rate = ($high - $low) / $low;
                                        $rate = $low_key <= $high_key ? $rate : '-' . $rate;
                                        $rate = number_format($rate, 2, '.', '');
                                        $info['buildid'] = $v['buildid'];
                                        $info['build_no'] = $v['build_no'];
                                        $info['low'] = $low;
                                        $info['high'] = $high;
                                        $info['low_date'] = $low_key;
                                        $info['high_date'] = $high_key;
                                        $info['average'] = $average;
                                        $info['rate'] = $rate;
                                        print_r($info);
                                        $result += ZDBTool::multiInsert("$pre_month", array($info));
                                        echo "--result--= $result\n";
                                        //die;
                                    }
                                }
                            }
                        }

                    }

                }
            }
        }
        return $result;
    }

    public static function statWeek()
    {
        $result = 0;
        $page_count = 1000;
        $week = date('w');
        //每周日执行
        if ($week == 0) {
            $last_week = date('Ymd', strtotime('-1 week'));
            $month = date('Ym');
            $now_day = 't_stat_' . $month . '_day';
            $now_week = 't_stat_' . $month . '_week';
            $day_list = Helper::getDateList($last_week, date('Ymd'));
            $week_num = Helper::getWeekNumber(time());
            if (!empty($day_list)) {
                $sql = ' SELECT a.buildid, a.build_no, ';
                $sql .= '`' . implode('`,`', $day_list) . '`';
                $sql .= ' FROM ' . $now_day . ' a ';
                if ($month > date('Ym', strtotime($day_list[0]))) {      //周跨月
                    $pre_month = date('Ym', strtotime($day_list[0]));
                    $pre_day = 't_stat_' . $pre_month . '_day';
                    $sql .= ' LEFT JOIN ' . $pre_day . ' b ON a.buildid = b.buildid ';
                }
                $data = ZDBTool::queryAll($sql);
                if (!empty($data)) {
                    $total_page = ceil(count($data) / $page_count);
                    foreach (range(1, $total_page) as $k => $v) {
                        $slice = Helper::arrayPage($data, $page_count, $v);
                        $slice_build = array_column($slice, 'buildid');
                        $slice_flip = array_flip($slice_build);
                        $slice_data = Helper::arrayKeyVal($slice, 'buildid', $day_list);
                        foreach ($slice_data as $sk => $sv) {
                            $info = array();
                            $tmp = array_filter($sv);
                            if (!empty($tmp)) {
                                asort($tmp);
                            }
                            $info['w' . $week_num . '_start'] = reset($sv);
                            $info['w' . $week_num . '_end'] = end($sv);
                            $info['w' . $week_num . '_low'] = empty($tmp) ? '0.00' : reset($tmp);
                            $info['w' . $week_num . '_high'] = empty($tmp) ? '0.00' : end($tmp);
                            $info['w' . $week_num . '_ave'] = empty($tmp) ? '0.00' : number_format(array_sum($tmp) / count($tmp), 2, '.', '');
                            $info['buildid'] = $sk;
                            $info['build_no'] = $slice[$slice_flip[$sk]]['build_no'];
                        }
                        //插入数据表
                    }
                }

                foreach ($day_list as $k => $v) {
                    $pre_day = 't_stat_' . $k . '_day';

                }
            }

        }
    }

    public static function passMonth()
    {

    }

    public static function noPassMonth($dayList)
    {
        if (!empty($dayList)) {
            $month = date('Ym');
            $pre_day = 't_stat_' . $month . '_day';
            $pre_week = 't_stat_' . $month . '_week';

        }
    }


    /**
     * 抓取房源数据
     * @return array        解析后数据集
     * @author              yurixu 2016-11-20
     * @example             Craw::crawBuild();
     */
    public static function crawBuild($distinct, $line)
    {
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '512M');
        $result = 0;

        /*$sql = 'SELECT id, name FROM `t_line` ';
        $line = ZDBTool::queryAll($sql);*/
        /*$sql = ' SELECT id, lj_no, areaPid, areaid, lineid, siteid FROM t_district ';
        $sql .= ' ORDER BY id ';
        $data = ZDBTool::queryAll($sql);*/
        if ($distinct > 0 && !empty($line)) {
            $data = ZDBTool::getInfoByid('t_district', $distinct, array('id', 'lj_no', 'areaPid', 'areaid', 'lineid', 'siteid'));
            if (!empty($data)) {
                echo "--start, id=" . $data['id'] . "\n";
                $url = 'http://' . LjConfig::CITY . LjConfig::DETAIL_BASE_URL . '/c' . $data['lj_no'];
                $content = Helper::getContents($url);
                if (!empty($content)) {
                    $result += self::parseBuild($content, $data['lj_no'], $data['id'], $data['areaPid'], $data['areaid'], $line);
                }
                echo "--end, id=" . $data['id'] . ", result = $result \n";
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
    public static function crawArea()
    {
        $result = 0;
        $sql = ' SELECT id, name, lj_no FROM t_area WHERE parentid = 0 ';

        $area = ZDBTool::queryAll($sql);
        foreach ($area as $k => $v) {
            $url = $url = 'http://' . LjConfig::CITY . LjConfig::DETAIL_BASE_URL . $v['lj_no'];
            echo $url."\n";
            $contents = Helper::getContents($url);
            if (!empty($contents)) {
                $result += self::parseArea($contents, $v['id']);
                sleep(2);
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
    public static function crawLine()
    {
        $result = 0;
        $sql = ' SELECT id, name, lj_no FROM t_line WHERE parentid = 0 ';

        $line = ZDBTool::queryAll($sql);
        foreach ($line as $k => $v) {
            $url = 'http://'.LjConfig::CITY.LjConfig::LINE_BASE_URL . $v['lj_no'];
            echo $url."\n";
            $contents = Helper::getContents($url);
            if (!empty($contents)) {
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
    public static function crawDistrict()
    {
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '512M');
        $result = 0;

        $sql = 'SELECT id, name FROM `t_line` ';
        $line = ZDBTool::queryAll($sql);
        if (!empty($line)) {
            $sql = ' SELECT id, lj_no, parentid FROM t_area WHERE parentid > 0 ';
            $area = ZDBTool::queryAll($sql);
            foreach ($area as $k => $v) {
                echo $v['lj_no'] . "\n";
                $url = 'http://'.LjConfig::CITY.LjConfig::DISTRICT_BASE_URL . $v['lj_no'];
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

    public static function parseBuild($content, $district, $districtid, $areaPid, $areaid, $line)
    {
        //http://bj.lianjia.com/zufang/pg2c2011047640650/
        $result = 0;
        $page_size = 30;
        if (!empty($content)) {
            $head_preg = '~<h2>共有<span>(\d+)</span>套北京在租房源~';
            $head = array();
            preg_match($head_preg, $content, $head);
            if (!empty($head)) {
                $total = $head[1];
                echo "--total--=$total\n";
                if ($total > 0 && $total < 10000) {      //添加小于1万的逻辑，因为http://bj.lianjia.com/zufang/c1112900488982796/，小区不存在，返回的是所有房源
                    $result = self::parseBuildPage($content, $districtid, $areaPid, $areaid, $line);
//                    echo "---num--- = $result\n";
                    if ($result > 0) {
                        $page = ceil($total / $page_size);
                        if ($page > 1) {
                            for ($i = 2; $i <= $page; $i++) {
                                $page_url = 'http://' . LjConfig::CITY . LjConfig::DETAIL_BASE_URL . 'pg' . $i . '/c' . $district;
                                $contents = Helper::getContents($page_url);
                                if (!empty($contents)) {
                                    $num = self::parseBuildPage($contents, $districtid, $areaPid, $areaid, $line);
//                                    echo "---num--- = $num\n";
                                    $result += $num;
                                }
                            }
                        }
                    }
//                    echo "--result=$result\n";
                }
            }
        }
        return $result;
    }

    /**
     * 解析房源页面数据
     * @param string $content 页码数据内容
     * @return array            解析后数据集
     * @author                  yurixu 2016-11-09
     * @example                 Craw::parsePage();
     */
    protected static function parseBuildPage($content, $districtid, $areaPid, $areaid, $line)
    {
        $result = 0;
        if (!empty($content)) {
            //@todo 解析一页数据
            $list_preg = '~<li data-index="\d+" data-id="\S+">[\s\S]*?</li>~';
            $list = array();
            preg_match_all($list_preg, $content, $list);
            if (!empty($list)) {
                $list = $list[0];
                $line_name = array_column($line, 'name');
                $line_flip = array_flip($line_name);

                foreach ($list as $k => $v) {
                    $info = array();
                    preg_match('~data-id="(\S+)"~', $v, $buildid);
                    if (!empty($buildid)) {
                        //房源ID
                        $info['build_no'] = $buildid[1];
                        $info['areaPid'] = $areaPid;
                        $info['areaid'] = $areaid;
                        $info['districtid'] = $districtid;
                        //房源详情页
                        $info['url'] = 'http://' . LjConfig::CITY . LjConfig::DETAIL_BASE_URL . $info['build_no'] . '.html';
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
                        /*//区域ID
                        $info['area'] = $area ? $area[1] : '';
                        //区域名称
                        $info['area_name'] = $area ? $area[2] : '';*/
                        //楼层阶级
                        $level = $area ? $area[3] : '';
                        if (!empty($level)) {
                            switch ($level) {
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
                        preg_match('~<span class="fang-subway-ex"><span>距离(\d+号线)(.*?)站.*?</span>~', $v, $subway);
                        /*//地铁线
                        $info['line'] = $subway ? $subway[1] : 0;
                        //地铁站点
                        $info['station'] = $subway ? $subway[2] : '';*/
                        $info['lineid'] = 0;
                        $info['siteid'] = 0;
                        if (!empty($subway[1])) {
                            if (isset($line_flip[$subway[1]])) {
                                $info['lineid'] = $line[$line_flip[$subway[1]]]['id'];
                            }
                        }
                        if (!empty($subway[2])) {
                            if (isset($line_flip[$subway[2]])) {
                                $info['siteid'] = $line[$line_flip[$subway[2]]]['id'];
                            }
                        }
                        preg_match('~<span class="decoration-ex"><span>(.*?)</span>~', $v, $decoration);
                        //装修类型
                        $info['decoration'] = $decoration ? $decoration[1] : '';
                        preg_match('~<span class="independentBalcony-ex"><span>(.*?)</span>~', $v, $balcony);
                        //阳台类型
                        $info['balcony'] = $balcony ? $balcony[1] : '';
                        preg_match('~<span class="privateBathroom-ex"><span>(.*?)</span>~', $v, $bathroom);
                        //卫生间类型
                        $info['bathroom'] = $bathroom ? $bathroom[1] : '';
                        preg_match('~<span class="heating-ex"><span>(.*?)</span>~', $v, $heating);
                        //供暖类型
                        $info['heating'] = $heating ? $heating[1] : '';
                        preg_match_all('~<span class="num">(\d+)</span>~', $v, $price);
                        //价格
                        $info['price'] = $price ? $price[1][0] : 0;
                        //看房认识
                        $info['visit'] = $price ? $price[1][1] : 0;
                        preg_match('~<div class="price-pre">(.*?)更新</div>~', $v, $update_time);
                        //更新日期
                        $info['update_time'] = $update_time ? $update_time[1] : '';
                        $info['is_rent'] = 0;
//                        print_r($info);
                    }
                    if (!empty($info)) {
                        $sql = ' SELECT id FROM t_build WHERE build_no = :build_no LIMIT 1 ';
                        $params = array(':build_no' => $info['build_no']);
                        $build = ZDBTool::queryRow($sql, $params);
                        if (!empty($build)) {
//                            echo "--update--build--\n";
                            $update = ZDBTool::updateRow('t_build', $build['id'], $info);
                        } else {
//                            echo "--create--build--\n";
                            $update = ZDBTool::multiInsert('t_build', array($info));
                        }
//                        echo "---update-- = $update ";
                        if ($update > 0) {
                            //向day表写入价格数据
                            $day_table = 't_stat_' . date('Ym') . '_day';
                            $day_field = '20161224';//date('Ymd');
                            $sql = ' SELECT id FROM ' . $day_table . ' WHERE build_no = :build_no LIMIT 1 ';
                            $params = array(':build_no' => $info['build_no']);
                            $build_day = ZDBTool::queryRow($sql, $params);
//                            print_r($build_day);
                            if (!empty($build_day)) {
                                $result += ZDBTool::updateRow("$day_table", $build_day['id'], array("$day_field" => $info['price']));
//                                echo "---day-update-- result = $result \n";
                            } else {
//                                echo "---day--create---\n";
                                $fields = array('id');
                                $condition = ' WHERE build_no = :build_no ';
                                $params = array(':build_no' => $info['build_no']);
                                $build_info = ZDBTool::getQuery('t_build', $fields, $condition, $params, 1);
//                                print_r($build_info);
                                if (!empty($build_info)) {
                                    $day_info = array(
                                        'buildid' => $build_info['id'],
                                        'build_no' => $info['build_no'],
                                        "$day_field" => $info['price'],
                                    );
//                                    print_r($day_info);
                                    $result += ZDBTool::multiInsert("$day_table", array($day_info));
//                                    echo "--result--= $result\n";
                                }
                            }
                        }
                    }
                    //print_r($buildid);
                    //die;
                }
            }
        }
        return $result;
    }

    /**
     * 解析区域页面数据
     * @param string $content 页码数据内容
     * @return array            解析后数据集
     * @author                  yurixu 2016-11-16
     * @example                 Craw::parseArea();
     */
    public static function parseArea($content, $parentid)
    {
        $parentid = Helper::CheckPlusInt($parentid);
        $result = 0;
        $area = array();
        $table = 't_area';
        if (!empty($content)) {
            //$head_preg = '~<div class="option-list sub-option-list">([\s\S]*?)</div>~';
            $head_preg = '~<ul data-target="area">([\s\S]*?)</ul>~';
            $head = array();
            preg_match($head_preg, $content, $head);
            if (!empty($head)) {
                preg_match_all('~<a href="/zufang/(\S+?)/">(\S+?)</a>~', $head[1], $list);
                if (!empty($list)) {
                    $area_no = $list[1];
                    $area_name = $list[2];
                    foreach ($area_name as $k => $v) {
                    	if ('不限' == $v) {
                    		continue;
	                    }
	                    echo "lj_no=".$area_no[$k].", name=$v, parentid=$parentid\n";
                        $area[$k]['lj_no'] = $area_no[$k];
                        $area[$k]['name'] = $v;
                        $area[$k]['parentid'] = $parentid;
                    }
	                $area = array_values($area);
                    $result = ZDBTool::multiInsert($table, $area);
                    echo " ---- result = $result \n";
                }
            }
        }
        return $result;
    }

    /**
     * 解析地铁线路页面数据
     * @param string $content 页码数据内容
     * @return array            解析后数据集
     * @author                  yurixu 2016-11-17
     * @example                 Craw::parseLine();
     */
    public static function parseLine($content, $parentid)
    {
        $parentid = Helper::CheckPlusInt($parentid);
        $result = 0;
        $line = array();
        $table = 't_line';
        if (!empty($content)) {
//            $head_preg = '~<div class="option-list sub-option-list">([\s\S]*?)</div>~';
			$head_preg = '~<ul data-target="station">([\s\S]*?)</ul>~';
			$head = array();
            preg_match($head_preg, $content, $head);
            if (!empty($head)) {
                preg_match_all('~<a href="/ditiezufang/(\S+?)/">(\S+?)</a>~', $head[0], $list);
                if (!empty($list)) {
                    $subway_no = $list[1];
                    $subway_name = $list[2];
                    foreach ($subway_name as $k => $v) {
	                    if ('不限' == $v) {
		                    continue;
	                    }
	                    echo "lj_no=".$subway_no[$k].", name=$v, parentid=$parentid\n";
                        $line[$k]['type'] = 2;
                        $line[$k]['lj_no'] = $subway_no[$k];
                        $line[$k]['name'] = $v;
                        $line[$k]['parentid'] = $parentid;
                    }
	                $line = array_values($line);
                    $result = ZDBTool::multiInsert($table, $line);
	                echo " ---- result = $result \n";
                }
            }
        }
        return $result;
    }

    /**
     * 解析小区列表页面数据
     * @param string $content 小区列表数据内容
     * @param array $line 地铁站点数据
     * @param int $areaid 二级区域ID
     * @return int              写入区域表数据记录数
     * @author                  yurixu 2016-11-20
     * @example                 Craw::parseDistrict();
     */
    public static function parseDistrict($content, $line, $areaPid, $areaid, $url)
    {
        $areaPid = Helper::CheckPlusInt($areaPid);
        $areaid = Helper::CheckPlusInt($areaid);
        $result = 0;
        $page_size = 30;
        if (!empty($content) && !empty($line) && !empty($url) && $areaid > 0) {
            $head_preg = '~<h2 class="total fl">共找到<span>\s*(\d+?)\s*</span>个小区</h2>~';
            $head = array();
            preg_match($head_preg, $content, $head);
            if (!empty($head)) {
                $total = $head[1];
                echo "--total--=$total\n";
                if ($total > 0) {
                    $result = self::parseDistrictPage($content, $line, $areaPid, $areaid);
                    echo "---num--- = $result\n";
                    if ($result > 0) {
                        $page = ceil($total / $page_size);
                        if ($page > 1) {
                            for ($i = 2; $i <= $page; $i++) {
                                $page_url = $url . '/pg' . $i;
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
     * @param string $content 小区列表数据内容
     * @param array $line 地铁站点数据
     * @param int $areaid 二级区域ID
     * @return int              写入区域表数据记录数
     * @author                  yurixu 2016-11-20
     * @example                 Craw::parseDistrictPage();
     */
    public static function parseDistrictPage($content, $line, $areaPid, $areaid)
    {
        $areaid = Helper::CheckPlusInt($areaid);
        $areaPid = Helper::CheckPlusInt($areaPid);
        $result = 0;
        $district = array();
        $lineid = 0;
        $siteid = 0;
        $table = 't_district';
        /*$file = '../data/district.txt';
        $content = file_get_contents($file);*/
        if (!empty($content) && !empty($line) && $areaid > 0) {
            $line_name = array_column($line, 'name');
            $line_flip = array_flip($line_name);
            $head_preg = '~<div class="title">\s*<a href="http://bj.lianjia.com/xiaoqu/(\d+)/" target="_blank">\S+</a>\s*</div>[\s\S]*?<div class="tagList">([\s\S]*?)</div>~';
            $list = array();
            preg_match_all($head_preg, $content, $list);
            if (!empty($list)) {
                $district_no = $list[1];
                $district_line = $list[2];
                foreach ($district_no as $k => $v) {
                    if (!empty($district_line[$k])) {
                        preg_match('~近地铁(\S+线)(\S+)站~', $district_line[$k], $site);
                        if (!empty($site)) {
                            $name = $site[1];
                            $site_name = $site[2];
                            if (isset($line_flip[$name])) {
                                $lineid = $line[$line_flip[$name]]['id'];
                            }
                            if (isset($line_flip[$site_name])) {
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

    public static function crawData()
    {
        $sql = 'SELECT id, name FROM `t_line` ';
        $line = ZDBTool::queryAll($sql);
        $sql = ' SELECT id FROM t_district ';
//        $sql .= ' WHERE id > 100 ';
        $sql .= ' ORDER BY id ';
        //$sql .= ' LIMIT 20 ';
        $data = ZDBTool::queryAll($sql);
        if (!empty($line) && !empty($data)) {
            $data = array_column($data, 'id');
            $redis = ZDBTool::redis();
            $redis->delete(LjConfig::REDIS_KEY);
            foreach ($data as $k => $v) {
                $redis->lPush(LjConfig::REDIS_KEY, $v);
            }
//            print_r($redis->lRange(LjConfig::REDIS_KEY, 0, -1));
            while ($redis->lSize(LjConfig::REDIS_KEY) > 0) {
                foreach (range(1, 5) as $k => $v) {
                    echo "--v = $v---\n";
                    $craw = new CrawThread($line);
                    $craw->start();
//                $craw->join();
                }
                sleep(5);
            }

            /*$craw = new CrawThread($line);
            $craw->start();*/
        }
    }
}