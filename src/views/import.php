<?php
/* @var $this yii\web\View */

use dsj\components\assets\LayuiAsset;
use yii\helpers\Url;

$this->title = false;
LayuiAsset::register($this);

$css = <<<CSS
.layui-card{
    width:580px;
    height:330px;
}
.layui-upload-drag{
   width:550px;
   height: 150px;
   margin-bottom: 5px;
}
CSS;
$this->registerCss($css);
?>
<div class="layui-col-md12" id="main">
    <div class="layui-card">
        <div class="layui-card-body">
            <div class="layui-upload-drag" id="test-upload-drag">
                <i class="layui-icon"></i>
                <p>点击上传，或将文件拖拽到此处</p>
            </div>
            <div class="layui-progress layui-progress-big" lay-filter="demo" lay-showPercent="yes">
                <div class="layui-progress-bar" lay-percent="0%"></div>
            </div>
        </div>
    </div>
</div>

<?php
$webPath = Yii::getAlias('@web');
$url = Url::to(['import']);
$js = <<<JS
layui.config({
       base:'$webPath/layui/src/layuiadmin/' //静态资源所在路径
    }).use([ 'upload','element'],function() {
         var $ = layui.jquery
        ,upload = layui.upload
        ,element = layui.element;
         
         element.init();
         
         //开始
         var percent = 0;
         var timeInterval = '';
         function start(){
              timeInterval = setInterval(function(){
                 if (percent < 90){
                     percent = percent + Math.ceil(Math.random()*10);
                 }
                  element.progress('demo', percent + '%');
　　         },100);
         }
         
         //拖拽上传
        upload.render({
          elem: '#test-upload-drag',
          url: "{$url}",
          accept: 'file',
          size : 500 * 1024,
          choose: function(obj){
               element.progress('demo', 0 + '%');
          },
          before: function(obj){ //obj参数包含的信息，跟 choose回调完全一致，可参见上文。
                start();
                },
          progress: function(obj) {
                      
					},
          done:function(res) {
              if (res.code == 200){
                  clearInterval(timeInterval)
                   element.progress('demo', 100 + '%');
                   layer.msg(res.msg);
              }else {
                  clearInterval(timeInterval)
                   element.progress('demo', 0 + '%');
                   layer.msg(res.msg);
              }
          },
        });
       
    })
JS;
$this->registerJs($js);
?>
