<?php


namespace dsj\components\actions;


use yii\web\Response;

class CaptchaAction extends \yii\captcha\CaptchaAction
{
    public function run()
    {
        $this->setHttpHeaders();
        \Yii::$app->response->format = Response::FORMAT_RAW;
        return $this->renderImage($this->getVerifyCode(true));
    }
}