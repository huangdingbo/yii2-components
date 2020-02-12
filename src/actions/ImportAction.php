<?php
/**
 * Created by PhpStorm.
 * User: 黄定波
 * Date: 2019/12/18
 * Time: 12:30
 */

namespace dsj\components\actions;



use ciniran\excel\ReadExcel;
use yii\base\Action;
use yii\base\Exception;

class ImportAction extends Action
{
    //文件后缀
    public $allowedExt = ['*'];

    //允许最大文件大小
    public $maxSize = 2 * 1024; //2M

    //文件类型
    public $type = ['*'];

    //读取excel配置
    public $readConfig = [];

    public function run(){

        if($_FILES){
            \Yii::$app->response->format = 'json';

            if ($_FILES["file"]["error"] > 0)
            {
                return ['code' => 203,'msg' => $_FILES["file"]["error"]];
            }

            $temp = explode(".", $_FILES["file"]["name"]);
            $extension = end($temp);
            if ($this->allowedExt != ['*']){
                if (!in_array($extension,$this->allowedExt)){
                    return ['code' => 201,'msg' => '不支持的文件类型'];
                }
            }
            if ($this->type != ['*']){
                if (!in_array($_FILES["file"]["type"],$this->type)){
                    return ['code' => 201,'msg' => '不支持的文件类型'];
                }
            }
            if (($_FILES["file"]["size"] / 1024) > $this->maxSize){
                return ['code' => 202,'msg' => '文件大于' . ($this->maxSize)/1024 . 'M'];
            }

            $basePath = \Yii::getAlias('@webroot') . '/upload-file/'.$extension . '/';

            if (!is_dir($basePath)){
                mkdir($basePath);
            }
            $datePath = $basePath = \Yii::getAlias('@webroot') . '/upload-file/'.$extension . '/' . date('Ymd') . '/';
            if (!is_dir($datePath)){
                mkdir($datePath);
            }

            $file = $basePath . md5($_FILES["file"]["name"].rand(1,10000)) . '.' . $extension;

            move_uploaded_file($_FILES["file"]["tmp_name"],$file);

            $excel = new ReadExcel(
                array_merge(['path' => $file, 'head' => false, 'headLine' => 1],$this->readConfig)
            );

            $data = isset($this->readConfig['class']) ? $excel->getModels() : $excel->getArray();

            try{
                $map = $this->controller->getExcelKeyForDatabaseKeyMap();

                $mapForData = [];

                foreach ($data as $key => $item){
                    foreach ($item as $k => $val){
                        $mapForData[$key][$map[$k]] = $val;
                    }
                }

                $dealRes = $this->controller->dealExcelData($mapForData);

                if ($dealRes){
                    return ['code' => 200,'msg' => '导入成功'];
                }else{
                    return ['code' => 204,'msg' => '导入失败'];
                }
            }catch (Exception $e){
                return ['code' => 210,'msg' => $e->getMessage()];
            }

        }

        return  $this->controller->render('@vendor/dsj/yii2-components/views/import.php');
    }
}