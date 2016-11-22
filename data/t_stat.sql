DROP TABLE IF EXISTS `t_build`;
CREATE TABLE IF NOT EXISTS `t_build` (
    `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
    `build_no` varchar(50) NOT NULL DEFAULT '' COMMENT '链家房源编码',
    `areaPid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '一级区域ID，对应t_area表ID',
    `areaid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '二级区域ID，对应t_area表ID',
    `districtid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '小区ID，对应t_district表ID',
    `url` varchar(300) NOT NULL DEFAULT '' COMMENT '房源详情URL',
    `cover` varchar(300) NOT NULL DEFAULT '' COMMENT '房源封面图',
    `title` varchar(100) NOT NULL DEFAULT '' COMMENT '房源名称',
    `zone` varchar(50) NOT NULL DEFAULT '' COMMENT '户型',
    `meters` smallint(4) unsigned NOT NULL DEFAULT '0' COMMENT '面积',
    `direction` varchar(10) NOT NULL DEFAULT '' COMMENT '朝向',
    `locate` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '所在楼层',
    `floor` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '楼层总数',
    `build_year` year NOT NULL COMMENT '建筑年',
    `build_type` varchar(100) NOT NULL DEFAULT '' COMMENT '楼板类型',
    `lineid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '地铁线路ID，对应t_line表ID',
    `siteid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '地铁站点ID，对应t_line表ID',
    `price` decimal(10,2) NOT NULL DEFAULT '0.0' COMMENT '价格',
    `decoration` varchar(30) NOT NULL DEFAULT '' COMMENT '装修类型',
    `balcony` varchar(30) NOT NULL DEFAULT '' COMMENT '阳台类型',
    `bathroom` varchar(30) NOT NULL DEFAULT '' COMMENT '卫生间类型',
    `heating` varchar(30) NOT NULL DEFAULT '' COMMENT '供暖类型',
    `visit` smallint(4) unsigned NOT NULL DEFAULT '0' COMMENT '看房人数',
    `is_rent` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否已出租',
    `update_time` date NOT NULL COMMENT '房源更新日期',
    `create_time` datetime NOT NULL COMMENT '创建时间',    
    `operate_time` datetime NOT NULL COMMENT '最后操作时间',        
     PRIMARY KEY (`id`)
) ENGINE = MyISAM DEFAULT CHARSET=utf8 COMMENT='房源信息表' ;

DROP TABLE IF EXISTS `t_stat_201611_day`;
CREATE TABLE IF NOT EXISTS `t_stat_201611_day` (
    `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
    `buildid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '房源ID，对应t_build表ID',
    `20161122` decimal(10,2) NOT NULL DEFAULT '0.0' COMMENT '20161122日价格',
    `create_time` datetime NOT NULL COMMENT '创建时间',    
    `operate_time` datetime NOT NULL COMMENT '最后操作时间',        
     PRIMARY KEY (`id`),
     KEY `buildid` (`buildid`)
) ENGINE = MyISAM DEFAULT CHARSET=utf8 COMMENT='房源价格日统计表' ;

DROP TABLE IF EXISTS `t_stat_201611_week`;
CREATE TABLE IF NOT EXISTS `t_stat_201611_week` (
    `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
    `buildid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '房源ID，对应t_build表ID',
    `w1_start` date NOT NULL COMMENT '第一周开始日期',
    `w1_end` date NOT NULL COMMENT '第一周结束日期',
    `w1_low` decimal(10,2) NOT NULL DEFAULT '0.0' COMMENT '第一周最低价格',
    `w1_high` decimal(10,2) NOT NULL DEFAULT '0.0' COMMENT '第一周最高价格',
    `w1_ave` decimal(10,2) NOT NULL DEFAULT '0.0' COMMENT '第一周均价',
    `w2_start` date NOT NULL COMMENT '第二周开始日期',
    `w2_end` date NOT NULL COMMENT '第二周结束日期',
    `w2_low` decimal(10,2) NOT NULL DEFAULT '0.0' COMMENT '第二周最低价格',
    `w2_high` decimal(10,2) NOT NULL DEFAULT '0.0' COMMENT '第二周最高价格',
    `w2_ave` decimal(10,2) NOT NULL DEFAULT '0.0' COMMENT '第二周均价',
    `w3_start` date NOT NULL COMMENT '第三周开始日期',
    `w3_end` date NOT NULL COMMENT '第三周结束日期',
    `w3_low` decimal(10,2) NOT NULL DEFAULT '0.0' COMMENT '第三周最低价格',
    `w3_high` decimal(10,2) NOT NULL DEFAULT '0.0' COMMENT '第三周最高价格',
    `w3_ave` decimal(10,2) NOT NULL DEFAULT '0.0' COMMENT '第三周均价',
    `w4_start` date NOT NULL COMMENT '第四周开始日期',
    `w4_end` date NOT NULL COMMENT '第四周结束日期',
    `w4_low` decimal(10,2) NOT NULL DEFAULT '0.0' COMMENT '第四周最低价格',
    `w4_high` decimal(10,2) NOT NULL DEFAULT '0.0' COMMENT '第四周最高价格',
    `w4_ave` decimal(10,2) NOT NULL DEFAULT '0.0' COMMENT '第四周均价',
    `create_time` datetime NOT NULL COMMENT '创建时间',    
    `operate_time` datetime NOT NULL COMMENT '最后操作时间',        
     PRIMARY KEY (`id`),
     KEY `buildid` (`buildid`)
) ENGINE = MyISAM DEFAULT CHARSET=utf8 COMMENT='房源价格周统计表' ;

DROP TABLE IF EXISTS `t_stat_201611_month`;
CREATE TABLE IF NOT EXISTS `t_stat_201611_month` (
    `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
    `buildid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '房源ID，对应t_build表ID',
    `low` decimal(10,2) NOT NULL DEFAULT '0.0' COMMENT '本月最低价格',
    `high` decimal(10,2) NOT NULL DEFAULT '0.0' COMMENT '本月最高价格',
    `average` decimal(10,2) NOT NULL DEFAULT '0.0' COMMENT '本月均价',
    `rate` decimal(4,2) NOT NULL DEFAULT '0.0' COMMENT '本月价格升/降率',
    `create_time` datetime NOT NULL COMMENT '创建时间',    
    `operate_time` datetime NOT NULL COMMENT '最后操作时间',        
     PRIMARY KEY (`id`),
     KEY `buildid` (`buildid`)
) ENGINE = MyISAM DEFAULT CHARSET=utf8 COMMENT='房源价格月统计表' ;

