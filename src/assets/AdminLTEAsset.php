<?php


namespace dsj\components\assets;


use yii\web\AssetBundle;

class AdminLTEAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/site.css',
        'css/bootstrap.min.css',
        'css/font-awesome.min.css',
        'css/ionicons.min.css',
        'css/AdminLTE.min.css',
        'css/all-skins.min.css',
    ];
    public $js = [
        'js/html5shiv.min.js',
        'js/respond.min.js',
        'js/jquery-2.2.3.min.js',
        'js/bootstrap.min.js',
        'js/jquery.slimscroll.min.js',
        'js/fastclick.js',
        'js/app.js',
        'js/demo.js',
        'js/app_iframe.js',
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];
}