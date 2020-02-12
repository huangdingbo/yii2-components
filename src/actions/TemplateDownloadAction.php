<?php
/**
 * Created by PhpStorm.
 * User: 黄定波
 * Date: 2019/12/20
 * Time: 11:24
 */

namespace dsj\components\actions;


use dsj\components\server\DownloadFile;
use Yii;
use yii\base\Action;

/**
 * Class TemplateDownloadAction
 * @package dsj\components\actions
 * 模板下载
 */
class TemplateDownloadAction extends Action
{
    public $file;

    public function run(){
        if (!file_exists($this->file)){
            Yii::$app->session->setFlash('warning','文件不存在');
            return $this->controller->redirect(array('index'));
        }else{
            (new DownloadFile())->setFilePath($this->file)->run();
        }
    }
}