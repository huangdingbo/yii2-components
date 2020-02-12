<?php


namespace dsj\components\controllers;

use dsj\components\helpers\CheckPermissionsHelper;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;

/**
 * Class WebController
 * @package common\components\controllers
 */
class WebController extends Controller
{
    protected function redirectParent(Array $route){

        $url = Url::to($route,true);

        echo "<script>parent.location.href = '{$url}'</script>";exit;
    }

    public function beforeAction($action)
    {
        if (parent::beforeAction($action)){
            //判断是否登录，没有登录跳转到登录页面
            if (\Yii::$app->user->isGuest){
                return $this->redirect(['/index/site/login'])->send();
            }
            //权限检查
            if (\Yii::$app->user->identity->username == 'root'){
                return true;
            }
            if (!(new CheckPermissionsHelper())->setRoute($this->route)->setUserId(\Yii::$app->user->id)->check()){
                throw new ForbiddenHttpException('对不起，你没有执行该操作的权限');
            }
        }

        return true;
    }

}