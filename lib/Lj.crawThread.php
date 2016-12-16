<?php
/**
 * Created by PhpStorm.
 * User: Eliot
 * Date: 2016/12/15
 * Time: 22:36
 */
class CrawThread extends Thread {
    private $que;
    private $line;


    public function __construct(&$que, $line) {
//        $this->que = $que;
        $this->line = $line;
    }

    public function run() {
        $num = 0;
        while(true) {
            if(!empty($que)) {
                echo "---que---".json_encode($que);
                $num ++;
                $distinct = array_shift($que);
                echo "threadid=".$this->getCurrentThreadId().", distinct=$distinct, num=$num \n";
                Craw::crawBuild($distinct, $this->line);
                if($num % 5 == 0) {
                    sleep(3);
                }
            } else {
                break;
            }
        }
    }
}