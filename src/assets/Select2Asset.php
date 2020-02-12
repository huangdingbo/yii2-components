<?php


namespace dsj\components\assets;


use yii\web\AssetBundle;
use yii\web\View;

class Select2Asset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/select2.css',
    ];
    public $js = [
        'js/select2.full.js',
    ];
    public $depends = [
    ];

    public $jsOptions = [
        'position' => View::POS_HEAD
    ];
}