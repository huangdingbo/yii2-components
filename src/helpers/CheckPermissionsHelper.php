<?php


namespace dsj\components\helpers;

use backend\models\RbacAssignment;
use backend\models\RbacItemChild;
use yii\web\ForbiddenHttpException;

/**
 * Class CheckPermissionsHelper
 * @package common\helpers
 * rbac检查权限助手
 */
class CheckPermissionsHelper
{
    private $user_id;

    private $route;

    private $ownPermissionMap = [];

    public function setUserId($user_id){
        $this->user_id = $user_id;
        return $this;
    }

    public function setRoute($route){
        $this->route = $route;
        return $this;
    }

    /**
     *获取用护拥有的所有权限路由
     */
    protected function getAllPermissionsByUser(){
        $list = [];
        $rolesList = RbacAssignment::find()->where(['user_id' => $this->user_id])->select('item_name')->asArray()->all();
        foreach ($rolesList as $item){
            $permissionList = RbacItemChild::find()->where(['parent' => $item['item_name']])->select('child')->asArray()->all();
            $permissionMap = \yii\helpers\ArrayHelper::map($permissionList,'child','child');
            $list = array_merge($list,$permissionMap);
        }

        $this->ownPermissionMap = $list;
    }

    public function check(){
        if (!$this->user_id || !$this->route){
            throw new ForbiddenHttpException('参数错误');
        }

        if (\Yii::$app->user->identity->username == 'root'){
            return true;
        }

        //获取拥有的权限列表
        $this->getAllPermissionsByUser();
        //通配符检查
        if ($this->wildcardChecker()){
            return true;
        }

        return in_array($this->route,$this->ownPermissionMap);
    }

    /**
     * @return bool
     * 通配符检查
     * eg： demo-data/* 表示demo-data控制器下所有权限
     * eg： demo-data/* /* 表示demo-data分组下所有权限
     */
    protected function wildcardChecker(){
        $routeArr = explode('/',$this->route);
        $name = $routeArr[0];
        foreach ($routeArr as $key => $item){
            if ($key == 0){
                continue;
            }
            $name .= '/' . '*';
            if (isset($this->ownPermissionMap[$name])){
                return true;
            }
        }

        return false;
    }
}