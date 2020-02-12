<?php


namespace dsj\components\server;


use yii\web\ForbiddenHttpException;

class ToolsServer
{

    public static function getDiffArrayByPk($arr1,$arr2,$pk = 'id'){
        try{
            $res=[];
            foreach($arr2 as $item) $tmpArr[$item[$pk]] = $item;
            foreach($arr1 as $v) if(! isset($tmpArr[$v[$pk]])) $res[] = $v;
            return $res;
        }catch (\Exception $exception){
            throw new ForbiddenHttpException(json_encode($exception->getMessage()));
        }
    }
}