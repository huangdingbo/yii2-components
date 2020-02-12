<?php


namespace dsj\components\helpers;

use yii\base\Exception;

/**
 * Class CommandHelper
 * @package common\helpers
 * linux 命令执行类
 * 说明：最终执行方法结束返回的都是bool，必须设置项目的绝对路径（重写 $projectPth 属性实现）
 *      必须在控制台运行，web访问www用户的权限不足，可以赋予www用户root权限解决
 * 1、通过进程名查询pid用法
 * $res = CommandHelper::getInstance()->setProgram('test/index')->searchPidByProgram();
 * $pid = CommandHelper::getInstance()->getPid();
 * 2、通过进程名查询进程状态用法
 * $res = CommandHelper::getInstance()->setProgram('test/index')->searchPidByProgram()->status();
 * 返回bool，true表示正在运行，false表示没有运行
 * 3、通过pid杀进程用法
 *  $res = CommandHelper::getInstance()->setPid('xxx')->kill();
 * 4、通过进程名杀进程用法
 * $res = CommandHelper::getInstance()->setProgram('test/index')->searchPidByProgram()->kill();
 * 5、运行普通命令用法
 * $res = CommandHelper::getInstance()->setCommand('ls -l')->exec();
 * 获取命令输出：CommandHelper::getInstance()->getOutput();
 * 获取命令执行结果状态码：CommandHelper::getInstance()->getExecResult();
 * 6、后台运行命令用法
 * $res = CommandHelper::getInstance()->setCommand('ls -l')->execAsBackground();
 * 获取pid: $pid = CommandHelper::getInstance()->getPid();
 * 7、运行yii控制台命令用法
 * $res = CommandHelper::getInstance()->setCommand('php yii test/index')->execYiiAsBack();
 * 获取pid: $pid = CommandHelper::getInstance()->getPid();
 */
class CommandHelper extends BaseCommandHelper
{
    protected $projectPth;

    private static $instance = null;

    private function __construct(){
        $this->projectPth = isset(\Yii::$app->params['projectPath']) ? \Yii::$app->params['projectPath'] : null;
    }

    private function __clone(){}

    public static function getInstance(){
        if (self::$instance === null){
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function execYiiAsBack(){
        $this->check();
        exec('cd '.\Yii::$app->params['projectPath'] . ' & nohup '.$this->command.' > /dev/null 2>&1 & echo $!',$pidArr,$exec_result);
        $this->pid = $pidArr[0];
        $this->exec_result = $exec_result;
        if ($this->exec_result == 0){
            return true;
        }
        return false;
    }

    public function checkMainIsRun(){
        exec("ps -ef |grep -v grep|grep -v 'sh -c' | grep main/index",$op);

        return count($op)>1;
    }
}