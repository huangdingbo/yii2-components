<?php


namespace dsj\components\assets;


use yii\web\AssetBundle;
use yii\web\View;

class JsonEditorAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'jsoneditor/dist/jsoneditor.css',
    ];
    public $js = [
        'jsoneditor/dist/jsoneditor.js',
    ];

    public $jsOptions = [
        'position' => View::POS_HEAD
    ];
}