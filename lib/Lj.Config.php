<?php
/**
 * Created by PhpStorm.
 * User: Eliot
 * Date: 2016/11/9
 * Time: 22:43
 */
Class LjConfig {
    //房源列表
    const PAGE_BASE_URL = 'http://bj.lianjia.com/zufang/pg';
    //房源详情
    const DETAIL_BASE_URL = 'http://bj.lianjia.com/zufang/';
    //小区
    const DISTRICT_BASE_URL = 'http://bj.lianjia.com/xiaoqu/';
    //地铁
    const LINE_BASE_URL = 'http://bj.lianjia.com/ditiezufang/';
    //数据库用户名
    const DB_USER_NAME = 'root';
    //数据库密码
    const DB_PASSWORD = 'xyw';
    //数据库连接
    const BD_CONNECTION = 'mysql:host=localhost;dbname=ljCraw';
    //Redis host
    const REDIS_HOST = '127.0.0.1';
    //Redis端口号
    const REDIS_PORT = '6379';
    //小区Redis
    const REDIS_KEY = 'lj:district';

    /**
     * 这里设置代理机器，只有需要代理的时候才设置，不需要代理，请设置为0.0.0.0和0
     * 默认CURL_PROXY_HOST=0.0.0.0和CURL_PROXY_PORT=0，此时不开启代理（如有需要才设置）
     */
    /*const CURL_PROXY_HOST = "0.0.0.0";
    const CURL_PROXY_PORT = 0;*/

    const CURL_PROXY_HOST = "dGNwOi8vcHJveHkudGVuY2VudC5jb20=";
    const CURL_PROXY_PORT = 8080;
}