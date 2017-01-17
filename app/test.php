<?php
require_once "../lib/Lj.Craw.php";
require_once "../lib/Lj.Parse.php";
require_once "../lib/Lj.ZDBTool.php";
//echo phpinfo();
/*class AsyncOperation extends Thread {
    public function __construct($arg){
        $this->arg = $arg;
    }
    public function run(){
        if($this->arg){
            printf("Hello %s\n", $this->arg);
        }
    }
}
$thread = new AsyncOperation("World");
if($thread->start())
    $thread->join();
*/
//$num = Parse::getLine(array('id', 'name'), 1);
$num = Parse::getAreaBuild();
print_r($num);