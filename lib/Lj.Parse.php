<?php
/**
 * Created by PhpStorm.
 * User: Eliot
 * Date: 2016/11/9
 * Time: 22:44
 */
require_once "Lj.Config.php";
require_once "Lj.Helper.php";
require_once "Lj.ZDBTool.php";
Class Parse {

    /**
 * 一级区域-房源数量分布（TOP 20）
 * @param int $pid          区域ID（0：所有一级区域房源分布；>0：某一级区域下相应区域房源分布）
 * @return array            房源数量分布数组
 * @author                  yurixu 2017-01-17
 * @example                 Parse::getAreaTopBuild();
 */
    public static function getAreaTopBuild($pid=0) {
        $pid = Helper::CheckPlusInt($pid);
        $result = array();
        $params = array();

        if($pid > 0) {
            $sql = 'SELECT areaid AS id, count(*) AS num FROM t_build
                    WHERE is_rent = 0
                    AND areaPid = :areaPid GROUP BY areaid ORDER BY num  DESC LIMIT 20 ';
            $params = array(':areaPid' => $pid);
        } else {
            $sql = 'SELECT areaPid AS id, count(*) AS num FROM t_build
                    WHERE is_rent = 0
                    GROUP BY areaPid ORDER BY num  DESC LIMIT 20 ';
        }
        $data = ZDBTool::queryAll($sql, $params);
        if(!empty($data)) {
            $area = Parse::getArea(array('id', 'name'));
            if(!empty($area)) {
                $name = Helper::arrayKeyVal($area, 'id', 'name');
                foreach($data as $k => $v) {
                    $data[$k]['name'] = $name[$v['id']];
                }
            }
            $data = array_reverse($data);
            $result['data'] = $data;
            $result['max'] = end($data)['num'];
        }
        return $result;
    }

    /**
     * 二级区域-房源数量分布（TOP 10）
     * @return array            房源数量分布数组
     * @author                  yurixu 2017-01-17
     * @example                 Parse::getAreaBuild();
     */
    public static function getAreaBuild() {
        $result = array();

        $sql = 'SELECT areaid AS id, count(*) AS num FROM t_build
                WHERE is_rent = 0
                GROUP BY areaid ORDER BY num  DESC LIMIT 10 ';
        $data = ZDBTool::queryAll($sql);
        if(!empty($data)) {
            $area = Parse::getArea(array('id', 'name'));
            if(!empty($area)) {
                $name = Helper::arrayKeyVal($area, 'id', 'name');
                foreach($data as $k => $v) {
                    $data[$k]['name'] = $name[$v['id']];
                    unset($data[$k]['id']);
                }
            }
            $data = array_reverse($data);
            $result['data'] = $data;
            $result['max'] = end($data)['num'];
        }
        return $result;
    }

    /**
     * 户型-租房源数量分布（TOP 10）
     * @param int $pid          区域ID（0：所有一级区域房源分布；>0：某一级区域下相应区域房源分布）
     * @return array            房源数量分布数组
     * @author                  yurixu 2017-01-17
     * @example                 Parse::getZoneBuild();
     */
    public static function getZoneBuild($pid=0) {
        $pid = Helper::CheckPlusInt($pid);
        $result = array();
        $params = array();

        $sql = 'SELECT zone, count(*) AS num FROM t_build
                WHERE is_rent = 0 ';
        if($pid > 0) {
            $sql .= ' AND areaPid = :areaPid ';
            $params = array(':areaPid' => $pid);
        }
        $sql .= ' GROUP BY zone ORDER BY num DESC LIMIT 10 ';
        $data = ZDBTool::queryAll($sql, $params);
        if(!empty($data)) {
            $data = array_reverse($data);
            $result['data'] = $data;
            $result['max'] = end($data)['num'];
        }
        return $result;
    }

    /**
     * 面积-租房源数量分布
     * @param int $pid          区域ID（0：所有一级区域房源分布；>0：某一级区域下相应区域房源分布）
     * @return array            房源数量分布数组
     * @author                  yurixu 2017-01-17
     * @example                 Parse::getMetersBuild();
     */
    public static function getMetersBuild($pid=0) {
        $pid = Helper::CheckPlusInt($pid);
        $params = array();
        $result = array();
        $data = array(
            '0-50' => 0,
            '50-100' => 0,
            '100-150' => 0,
            '150-200' => 0,
            '>200' => 0,
        );

        $sql = 'SELECT meters FROM t_build
                WHERE is_rent = 0 ';
        if($pid > 0) {
            $sql .= ' AND areaPid = :areaPid ';
            $params = array(':areaPid' => $pid);
        }
        $nret = ZDBTool::queryAll($sql, $params);
        if(!empty($nret)) {
            foreach($nret as $k => $v) {
                if((int)$v['meters'] <= 50) {
                    $data['0-50'] ++;
                } elseif ((int)$v['meters'] > 50 && (int)$v['meters'] <= 100) {
                    $data['50-100'] ++;
                } elseif ((int)$v['meters'] > 100 && (int)$v['meters'] <= 150) {
                    $data['100-150'] ++;
                } elseif ((int)$v['meters'] > 150 && (int)$v['meters'] <= 200) {
                    $data['150-200'] ++;
                } elseif ((int)$v['meters'] > 200) {
                    $data['>200'] ++;
                }
            }

            $result['data'] = $data;
            $result['max'] = max($data);
        }
        return $result;
    }

    /**
     * 朝向-租房源数量分布（TOP 10）
     * @param int $pid          区域ID（0：所有一级区域房源分布；>0：某一级区域下相应区域房源分布）
     * @return array            房源数量分布数组
     * @author                  yurixu 2017-01-17
     * @example                 Parse::getDirectBuild();
     */
    public static function getDirectBuild($pid=0) {
        $pid = Helper::CheckPlusInt($pid);
        $result = array();
        $params = array();

        $sql = 'SELECT direction, count(*) AS num FROM t_build
                WHERE is_rent = 0 ';
        if($pid > 0) {
            $sql .= ' AND areaPid = :areaPid ';
            $params = array(':areaPid' => $pid);
        }
        $sql .= ' GROUP BY direction ORDER BY num DESC LIMIT 10 ';
        $data = ZDBTool::queryAll($sql, $params);
        if(!empty($data)) {
            $data = array_map(function($e) {
                $e['direction'] = str_replace(' ', '', $e['direction']);
                return $e;
            }, $data);
            $data = array_reverse($data);
            $result['data'] = $data;
            $result['max'] = end($data)['num'];
        }
        return $result;
    }

    /**
     * 楼层-租房源数量分布
     * @param int $pid          区域ID（0：所有一级区域房源分布；>0：某一级区域下相应区域房源分布）
     * @return array            房源数量分布数组
     * @author                  yurixu 2017-01-17
     * @example                 Parse::getLocateBuild();
     */
    public static function getLocateBuild($pid=0) {
        $pid = Helper::CheckPlusInt($pid);
        $result = array();
        $params = array();

        $sql = "SELECT CASE locate
                WHEN 1 THEN '低楼层'
                WHEN 2 THEN '中楼层'
                WHEN 3 THEN '高楼层'
                END AS `locate`,
                COUNT(*) AS num FROM t_build
                WHERE is_rent = 0 AND locate > 0 ";
        if($pid > 0) {
            $sql .= ' AND areaPid = :areaPid ';
            $params = array(':areaPid' => $pid);
        }
        $sql .= ' GROUP BY locate ';
        $data = ZDBTool::queryAll($sql, $params);
        if(!empty($data)) {
            $result['data'] = $data;
            $result['max'] = max(array_column($data, 'num'));
        }
        return $result;
    }

    /**
     * 地铁沿线-租房源数量分布（TOP 20）
     * @param int $lineid       地铁线路ID（0：整体线路房源分布；>0：相应地铁线路房源分布）
     * @return array            房源数量分布数组
     * @author                  yurixu 2017-01-17
     * @example                 Parse::getLineBuild();
     */
    public static function getLineBuild($lineid=0) {
        $lineid = Helper::CheckPlusInt($lineid);
        $result = array();
        $params = array();

        if($lineid > 0) {
            $sql = 'SELECT siteid AS id, COUNT(*) AS num FROM t_build
                    WHERE is_rent = 0 AND lineid = :lineid AND siteid > 0
                    GROUP BY siteid
                    ORDER BY num DESC
                    LIMIT 20 ';
            $params = array(':lineid' => $lineid);
        } else {
            $sql = 'SELECT lineid AS id, COUNT(*) AS num FROM t_build
                    WHERE is_rent = 0 AND lineid > 0
                    GROUP BY lineid
                    ORDER BY num DESC
                    LIMIT 20 ';
        }
        $data = ZDBTool::queryAll($sql, $params);
        if(!empty($data)) {
            $line = Parse::getLine(array('id', 'name'));
            if(!empty($line)) {
                $name = Helper::arrayKeyVal($line, 'id', 'name');
                foreach($data as $k => $v) {
                    $data[$k]['name'] = $name[$v['id']];
                }
            }
            $data = array_reverse($data);
            $result['data'] = $data;
            $result['max'] = end($data)['num'];
        }
        return $result;
    }

    /**
     * 地铁站点-租房源数量分布（TOP 20）
     * @return array            房源数量分布数组
     * @author                  yurixu 2017-01-17
     * @example                 Parse::getSiteBuild();
     */
    public static function getSiteBuild() {
        $result = array();

        $sql = 'SELECT siteid AS id, lineid, COUNT(*) AS num FROM t_build
                WHERE is_rent = 0 AND siteid > 0
                GROUP BY siteid
                ORDER BY num DESC
                LIMIT 20 ';
        $data = ZDBTool::queryAll($sql);
        if(!empty($data)) {
            $line = Parse::getLine(array('id', 'name'));
            if(!empty($line)) {
                $name = Helper::arrayKeyVal($line, 'id', 'name');
                foreach($data as $k => $v) {
                    $data[$k]['name'] = $name[$v['id']].'('.$name[$v['lineid']].')';
                    unset($data[$k]['lineid']);
                    unset($data[$k]['id']);
                }
            }
            $data = array_reverse($data);
            $result['data'] = $data;
            $result['max'] = end($data)['num'];
        }
        return $result;
    }

    /**
     * 小区-租房源数量分布（TOP 10）
     * @return array            房源数量分布数组
     * @author                  yurixu 2017-01-17
     * @example                 Parse::getDistrictBuild();
     */
    public static function getDistrictBuild() {
        $result = array();

        $sql = 'SELECT districtid AS id, COUNT(*) AS num FROM t_build
                WHERE is_rent = 0 AND districtid > 0
                GROUP BY districtid
                ORDER BY num DESC
                LIMIT 10 ';
        $data = ZDBTool::queryAll($sql);
        if(!empty($data)) {
            $distinct = Parse::getDistinct(array('id', 'name'));
            if(!empty($distinct)) {
                $name = Helper::arrayKeyVal($distinct, 'id', 'name');
                foreach($data as $k => $v) {
                    $data[$k]['name'] = $name[$v['id']];
                    unset($data[$k]['id']);
                }
            }
            $data = array_reverse($data);
            $result['data'] = $data;
            $result['max'] = end($data)['num'];
        }
        return $result;
    }

    /**
     * 建筑年代-租房源数量分布
     * @param int $pid          区域ID（0：所有一级区域房源分布；>0：某一级区域下相应区域房源分布）
     * @return array            房源数量分布数组
     * @author                  yurixu 2017-01-17
     * @example                 Parse::getYearBuild();
     */
    public static function getYearBuild($pid=0) {
        $pid = Helper::CheckPlusInt($pid);
        $params = array();
        $result = array();
        $data = array(
            '<1990' => 0,
            '90-95' => 0,
            '95-00' => 0,
            '00-05' => 0,
            '05-10' => 0,
            '10-15' => 0,
            '>2015' => 0,
        );

        $sql = 'SELECT build_year FROM t_build
                WHERE is_rent = 0 ';
        if($pid > 0) {
            $sql .= ' AND areaPid = :areaPid ';
            $params = array(':areaPid' => $pid);
        }
        $nret = ZDBTool::queryAll($sql, $params);
        if(!empty($nret)) {
            foreach($nret as $k => $v) {
                if((int)$v['build_year'] <= 1990) {
                    $data['<1990'] ++;
                } elseif ((int)$v['build_year'] > 1990 && (int)$v['build_year'] <= 1995) {
                    $data['90-95'] ++;
                } elseif ((int)$v['build_year'] > 1995 && (int)$v['build_year'] <= 2000) {
                    $data['95-00'] ++;
                } elseif ((int)$v['build_year'] > 2000 && (int)$v['build_year'] <= 2005) {
                    $data['00-05'] ++;
                } elseif ((int)$v['build_year'] > 2005 && (int)$v['build_year'] <= 2010) {
                    $data['05-10'] ++;
                } elseif ((int)$v['build_year'] > 2010 && (int)$v['build_year'] <= 2015) {
                    $data['10-15'] ++;
                } elseif ((int)$v['build_year'] > 2015) {
                    $data['>2015'] ++;
                }
            }

            $result['data'] = $data;
            $result['max'] = max($data);
        }
        return $result;
    }

    /**
     * 价格-租房源数量分布
     * @param int $pid          区域ID（0：所有一级区域房源分布；>0：某一级区域下相应区域房源分布）
     * @return array            房源数量分布数组
     * @author                  yurixu 2017-01-17
     * @example                 Parse::getPriceBuild();
     */
    public static function getPriceBuild($pid=0) {
        $pid = Helper::CheckPlusInt($pid);
        $params = array();
        $result = array();
        $data = array(
            '<1000' => 0,
            '1k-2k' => 0,
            '2k-3k' => 0,
            '3k-4k' => 0,
            '4k-5k' => 0,
            '5k-10k' => 0,
            '>10k' => 0,
        );

        $sql = 'SELECT price FROM t_build
                WHERE is_rent = 0 ';
        if($pid > 0) {
            $sql .= ' AND areaPid = :areaPid ';
            $params = array(':areaPid' => $pid);
        }
        $nret = ZDBTool::queryAll($sql, $params);
        if(!empty($nret)) {
            foreach($nret as $k => $v) {
                if((int)$v['price'] <= 1000) {
                    $data['<1000'] ++;
                } elseif ((int)$v['price'] > 1000 && (int)$v['price'] <= 2000) {
                    $data['1k-2k'] ++;
                } elseif ((int)$v['price'] > 2000 && (int)$v['price'] <= 3000) {
                    $data['2k-3k'] ++;
                } elseif ((int)$v['price'] > 3000 && (int)$v['price'] <= 4000) {
                    $data['3k-4k'] ++;
                } elseif ((int)$v['price'] > 4000 && (int)$v['price'] <= 5000) {
                    $data['4k-5k'] ++;
                } elseif ((int)$v['price'] > 5000 && (int)$v['price'] <= 10000) {
                    $data['5k-10k'] ++;
                } elseif ((int)$v['price'] > 10000) {
                    $data['>10k'] ++;
                }
            }

            $result['data'] = $data;
            $result['max'] = max($data);
        }
        return $result;
    }

    /**
     * 看房次数-房源排行（TOP 20）
     * @param int $pid          区域ID（0：所有一级区域房源分布；>0：某一级区域下相应区域房源分布）
     * @return array            房源数量分布数组
     * @author                  yurixu 2017-01-17
     * @example                 Parse::getVisitBuild();
     */
    public static function getVisitBuild($pid=0) {
        $pid = Helper::CheckPlusInt($pid);
        $result = array();
        $params = array();

        $sql = "SELECT title, url, visit FROM `t_build`
                WHERE is_rent = 0";
        if($pid > 0) {
            $sql .= ' AND areaPid = :areaPid ';
            $params = array(':areaPid' => $pid);
        }
        $sql .= ' ORDER BY `visit` DESC LIMIT 20 ';
        $result = ZDBTool::queryAll($sql, $params);
        return $result;
    }

    /**
     * 获取区域数据
     * @param int $parent       父级ID
     * @param array $fields     查询字段
     * @return array            结果集
     * @author                  yurixu 2017-01-17
     * @example                 Parse::getArea();
     */
    public static function getArea($fields=array('name'), $parent='-1') {
        $parent = Helper::CheckInt($parent);
        $fields = is_array($fields) && !empty($fields) ? $fields : array('name');
        $result = array();
        $condition = '';
        $params = array();

        if(!empty($fields)) {
            if($parent != '-1') {
                $condition = ' WHERE parentid = :parentid ';
                $params = array(':parentid' => $parent);
            }
            $result = ZDBTool::getQuery('t_area', $fields, $condition, $params);
        }
        return $result;
    }

    /**
     * 获取地铁线路数据
     * @param int $parent       父级ID
     * @param array $fields     查询字段
     * @return array            结果集
     * @author                  yurixu 2017-01-17
     * @example                 Parse::getLine();
     */
    public static function getLine($fields=array('name'), $parent='-1') {
        $parent = Helper::CheckInt($parent);
        $fields = is_array($fields) && !empty($fields) ? $fields : array('name');
        $result = array();
        $condition = '';
        $params = array();

        if(!empty($fields)) {
            if($parent != '-1') {
                $condition = ' WHERE parentid = :parentid ';
                $params = array(':parentid' => $parent);
            }
            $result = ZDBTool::getQuery('t_line', $fields, $condition, $params);
        }
        return $result;
    }

    /**
     * 获取小区数据
     * @param array $fields     查询字段
     * @return array            结果集
     * @author                  yurixu 2017-01-17
     * @example                 Parse::getDistinct();
     */
    public static function getDistinct($fields=array('name')) {
        $fields = is_array($fields) && !empty($fields) ? $fields : array('name');
        $result = array();

        if(!empty($fields)) {
            $result = ZDBTool::getQuery('t_district', $fields);
        }
        return $result;
    }

    /**
     * 获取未出租/已出租的房源数
     * @param int $state        0:未出租 1:已出租
     * @return int              数目
     * @author                  yurixu 2017-01-17
     * @example                 Parse::getBuildNum();
     */
    public static function getBuildNum($state=0) {
        $state = Helper::CheckPlusInt($state);
        $result = 0;

        if($state == 0 || $state == 1) {
            $result = ZDBTool::getQueryCount('t_build', ' WHERE is_rent = :state ', array(':state' => $state));
        }
        return $result;
    }


    /**
     * 更新房屋租赁状态
     * 执行完房源抓取程序后再执行本方法，默认房源抓取程序是在当天执行完成
     * @return int              前一天已经出租出去的房源数
     * @author                  yurixu 2017-01-17
     * @example                 Parse::updateRentState();
     */
    public static function updateRentState() {
        $result = 0;
        $time = date('Y-m-d 00:00:00');
        $sql = ' UPDATE t_build SET is_rent = 1 WHERE operate_time < :operate_time ';
        $params = array(':operate_time' => $time);
        $result = ZDBTool::execute($sql, $params);
        echo "--- updateRentState success: $result ---\n";
        return $result;
    }
}
