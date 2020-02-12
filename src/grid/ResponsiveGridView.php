<?php


namespace dsj\components\grid;

use dsj\components\assets\LayuiAsset;
use yii\grid\GridView;
use yii\helpers\Json;

class ResponsiveGridView extends GridView
{
    /**
     * @var array
     * 弹窗配置
     * 'layerOption' =>
     * [
     *       'view' => [
     *           'type' => 2,
     *       ],
     *       'update' => [
     *          'type' => 1,
     *      ],
     *   ],
     */
    public $layerOption = [];

    public function run()
    {
        //初始化配置
        $this->initLayerOption();
        //根据配置加载js
        $this->registerJs();
        parent::run();
    }

    protected function initLayerOption(){
        $baseConfig = [
            'view' => [
                'type' => 2,
                'title' => '查看',
                'area' => ['900px','600px'],
                'shadeClose' => true,
            ],
            'update' => [
                'type' => 2,
                'title' => '修改',
                'area' => ['900px','600px'],
                'shadeClose' => true,
            ],
            'create' => [
                'type' => 2,
                'title' => '创建',
                'area' => ['900px','600px'],
                'shadeClose' => true,
            ],
            'import' => [
                'type' => 2,
                'title' => '导入',
                'area' => ['600px','420px'],
                'shadeClose' => true,
            ],
            'delete' => [
                'is_confirm' => true,
                'content' => '你确定要删除吗?',
                'method' => 'POST',
            ],
        ];
        foreach ($baseConfig as $key => $item){
            if (isset($this->layerOption[$key])){
                $this->layerOption[$key] = array_merge($item,$this->layerOption[$key]);
            }
        }
        $this->layerOption = array_merge($baseConfig,$this->layerOption);
    }

    protected function registerJs(){

        $view = $this->getView();
        LayuiAsset::register($view);
        $webPath = \Yii::getAlias('@web');
        foreach ($this->layerOption as $key => $item){
            $config = Json::encode($item);
            $className = 'data-' . $key;
            if (isset($item['is_confirm']) && $item['is_confirm'] == true){
                $content = isset($item['content']) ? $item['content'] : '你确定要执行此操作吗？';
                $method = isset($item['method']) ? $item['method'] : 'POST';
                $js = <<<JS
$('.$className').on('click', function () {
    let url = $(this).attr('url');
    layui.config({
        base:'$webPath/layui/src/layuiadmin/' //静态资源所在路径
    }).use(['layer'],function() {
        var layer = layui.layer;
        layer.confirm("$content", {icon: 7, title:'提示',skin:'layui-layer-lan'}, function(index){
               $.ajax({
                    type: "$method",
                    url: url,
                    dataType: "json",
			    });
              layer.close(index);
        });
});
});
JS;
            }else{
                $js = <<<JS
    $('.$className').on('click', function () {
        let config = $config;
        config.content = $(this).attr('url');
          layui.config({
                base: '$webPath/layui/src/layuiadmin/'
            }).use('layer', function(){
                var layer = layui.layer;
                layer.open(config);
            });
    });
JS;
            }
            $view->registerJs($js);
        }
    }
}