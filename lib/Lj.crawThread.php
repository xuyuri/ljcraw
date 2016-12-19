<?php
/**
 * Created by PhpStorm.
 * User: Eliot
 * Date: 2016/12/15
 * Time: 22:36
 */
class CrawThread extends Thread {
    protected $line;


    public function __construct($line) {
        $this->line = $line;
    }

    public function run() {
        $num = 0;
        $size = ZDBTool::redis()->lSize(LjConfig::REDIS_KEY);
        echo "---size = $size \n";
        if($size > 0) {
            $data = ZDBTool::redis()->rPop(LjConfig::REDIS_KEY);
            if (!empty($data)) {
                $num++;
                echo "threadid=" . $this->getCurrentThreadId() . ", parentThread = ".$this->getCreatorId(). ", distinct=$data, num=$num \n";
                //Craw::crawBuild($distinct, $this->line);
                /*if ($num % 5 == 0) {
                    sleep(3);
                }*/
            }
        }
    }
}