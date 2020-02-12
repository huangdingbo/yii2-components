<?php


namespace dsj\components\helpers;


use yii\base\Exception;
use yii\web\ForbiddenHttpException;

class BaseCommandHelper
{
    //命令
    protected $command;
    //输出
    protected $output;
    //执行结果
    protected $exec_result;
    //进程pid
    protected $pid;
    //进程名
    protected $program;
    //项目绝对路径
    protected $projectPth = null;

    public function setProjectPath($projectPath){
        $this->projectPth = $projectPath;
        return $this;
    }

    public function setCommand($command){
        $this->command = $command;
        return $this;
    }

    public function setPid($pid){
        $this->pid = $pid;
        return $this;
    }

    public function setProgram($program){
        $this->program = $program;
        return $this;
    }

    public function getOutput(){
        return $this->output;
    }

    public function getExecResult(){
        return $this->exec_result;
    }

    public function getPid(){
        return $this->pid;
    }

    public function status(){
        if ($this->pid){
            exec('ps -p '.$this->pid,$output,$exec_result);
            if (isset($output[1]) && $exec_result == 0){
                return true;
            }
        }
        return false;
    }
    public function exec(){
        $this->check();
        exec($this->command,$output,$exec_result);
        $this->output = $output;
        $this->exec_result = $exec_result;
        if ($this->exec_result == 0){
            return true;
        }
        return false;
    }

    public function execAsBackground(){
        $this->check();
        exec('nohup '.$this->command.' > /dev/null 2>&1 & echo $!',$pidArr,$exec_result);
        $this->pid = $pidArr[0];
        $this->exec_result = $exec_result;
        if ($this->exec_result == 0){
            return true;
        }
        return false;
    }

    public function kill(){

        if ($this->pid){
            if (is_array($this->pid)){
                foreach ($this->pid as $item){
                    exec('kill -9 '. $item,$output,$exec_result);
                }
            }else{
                exec('kill -9 '. $this->pid,$output,$exec_result);
            }
        }
        return true;
    }

    public function searchPidByProgram(){
        exec("ps -ef |grep -v grep|grep -v 'sh -c' | grep '$this->program' |awk '{print $2}'",$output,$exec_result);
        if (count($output) == 1){
            $this->pid = $output[0];
        }else{
            $this->pid = $output;
        }

        return $this;
    }
    protected function check(){
        if ($this->projectPth === null){
            throw new Exception('请配置项目绝对路径');
        }
    }




}