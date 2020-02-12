<?php


namespace dsj\components\helpers\processes;


class MultipleProcessesHelper
{
    /**
     * @var int
     * 进程数量
     */
    private $processNum = 0;

    /**
     * @var array
     * pid
     */
    private $ids = [];

    /**
     * @var
     * todo something
     */
    private $callback;

    /**
     * @var
     * handler 类名
     */
    private $className;

    private $process;

    private $params;

    /**
     * @var
     * 进程标记号
     */
    private $index;

    private $pidMap;

    private $beforeExecute;

    private $afterExecute;

    public function setBeforeExecute($callback){
        $this->beforeExecute = $callback;
    }

    public function setAfterExecute($callback){
        $this->afterExecute = $callback;
    }

    public function getPidMap(){
        return $this->pidMap;
    }

    public function setProcessNum($processNum){
        $this->processNum = $processNum;
    }

    public function getProcessNum(){
        return $this->processNum;
    }

    public function getIds(){
        return $this->ids;
    }

   public function setIndex($index){
        $this->index = $index;
   }

   public function getIndex(){
        return $this->index;
   }

    public function setCallback($callback){
        $this->callback = $callback;
    }

    public function setHandler($className,MultipleProcessesHelper $process,array $params = []){
        $this->className = $className;
        $this->process = $process;
        $this->params = $params;
    }

    public function execute(){

        foreach (range(0, $this->processNum - 1) as $index) {

            $this->index = $index;

            $this->ids[] = $pid = pcntl_fork();

            if ($pid === 0) {
                $cid = posix_getpid();

                $this->pidMap[$index] = $cid;

                $beforeExecute = $this->beforeExecute;

                if (is_callable($beforeExecute)){
                    $beforeExecute();
                }

                $callback = $this->callback;

                if(is_callable($callback)){
                    $callback();
                    $afterExecute = $this->afterExecute;
                    if (is_callable($afterExecute)){
                        $afterExecute();
                    }
                    exit;
                }

                if ($this->className){
                    $obj = MakeObjectHelper::make($this->className,['process' => $this->process,'params' => $this->params]);
                    $obj->execute();
                    $afterExecute = $this->afterExecute;
                    if (is_callable($afterExecute)){
                        $afterExecute();
                    }
                    exit;
                }

                exit;
            }
        }

        foreach ($this->ids as $i => $pid) {
            if ($pid) {
                pcntl_waitpid($pid, $status);
            }
        }
    }


}