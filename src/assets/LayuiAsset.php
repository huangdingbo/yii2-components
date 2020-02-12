<?php


namespace dsj\components\assets;


use yii\web\AssetBundle;

class LayuiAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'layui/src/layuiadmin/layui/css/layui.css',
        'layui/src/layuiadmin/style/admin.css',
        'layui/src/layuiadmin/style/login.css',
        'css/site.css',
        'css/font-awesome.css',
        'css/iconfont.css',


    ];
    public $js = [
        'layui/src/layuiadmin/layui/layui.js',
    ];
    public $depends = [

    ];

}