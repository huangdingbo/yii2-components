<?php


use dsj\components\assets\LayuiAsset;

LayuiAsset::register($this);
/**
 * @var  $type
 * @var  $content
 * @var  $config
 */
$config = \yii\helpers\Json::encode($content);
$type = isset($content['type']) ? $content['type'] : 5;
$contentText = $content['content'];
$icon = isset($content['icon']) ? $content['icon'] : -1;
$time = isset($content['time']) ? $content['time'] : 1000;
$webPath = Yii::getAlias('@web');
$js = <<<JS
 layui.config({
			base: '$webPath/layui/src/layuiadmin/'
		}).use('layer', function(){
  var layer = layui.layer;
  var type = "$type";
  if (type == 5){
      layer.msg("$contentText",{'icon': $icon,'time': $time});
  }else {
      layer.open($config);
  }
  
});              
JS;
$this->registerJs($js);
?>



