<?php


namespace dsj\components\controllers;

use dsj\bgtask\models\BgTask;
use dsj\bgtask\models\BgTaskForConsole;
use dsj\components\helpers\LogHelper;
use yii\console\Controller;

/**
 * Class BgController
 * @package common\components\controllers
 * 继承该控制台控制器类需注意：
 * 1、必须先在后台添加后台任务
 * 2、必须将逻辑代码放在回调函数中
 */
class BgController extends Controller
{
    protected $model = null;
    public function beforeAction($action)
    {
        try{
            if (parent::beforeAction($action)){
                $this->model = (new BgTaskForConsole())->findOneByProgram($action->uniqueId);
                if (!$this->model){
                    throw new \Exception('请先在后台设置中添加程序：' . $action->uniqueId);
                }
                $this->model->status = BgTask::STATUS_ON;
                $this->model->start_time = time();
                $this->model->memory = memory_get_usage();
                $this->model->save();
            }
        }catch (\Exception $e){
            (new LogHelper())->setRoute($this->route)->setData($e->getMessage())->write();exit;
        }
        //创建日志文件
        (new LogHelper())->setRoute($this->route)->setData('任务开始执行')->write();
        return true;
    }

    public function afterAction($action, $result)
    {
        if (parent::afterAction($action, $result)){
            try{
                $result();
                $this->model->status = BgTask::STATUS_SUCCESS;
                $this->model->end_time = time();
                $this->model->run_time = $this->model->end_time - $this->model->start_time;
                $this->model->save();
                (new LogHelper())->setRoute($this->route)->setData('任务执行结束，未发现异常')->write();
            }catch (\Exception $e){
                $this->model->status = BgTask::STATUS_OFF;
                $this->model->end_time = time();
                $this->model->run_time = $this->model->end_time - $this->model->start_time;
                $this->model->save();
                (new LogHelper())->setRoute($this->route)->setData($e->getMessage())->write();
            }
        }

        return true;
    }
}