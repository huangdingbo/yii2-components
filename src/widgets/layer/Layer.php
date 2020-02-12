<?php
namespace dsj\components\widgets\layer;

use yii\base\Widget;

class Layer extends Widget
{
    //基本层类型
    public $type;
    //标题
    public $title;
    //内容
    public $content;
    //样式类名
    public $skin;
    //宽高
    public $area;
    //坐标,默认坐标，即垂直水平居中
    public $offset;
    //图标,信息框和加载层的私有参数
    public $icon;
    //按钮
    public $btn;
    //按钮排列
    public $btnAlign;
    //关闭按钮
    public $closeBtn;
    //遮罩
    public $shade;
    //是否点击遮罩关闭
    public $shadeClose;
    //自动关闭所需毫秒
    public $time;
    //用于控制弹层唯一标识
    public $id;
    // 弹出动画
    public $anim;
    //关闭动画
    public $isOutAnim;
    //最大最小化
    public $maxmin;
    //固定
    public $fixed;
    //是否允许拉伸
    public $resize;
    //监听窗口拉伸动作
    public $resizing;
    //是否允许浏览器出现滚动条
    public $scrollbar;
    //最大宽度
    public $maxWidth;
    //最大高度
    public $maxHeight;
    //层叠顺序
    public $zIndex;
    //触发拖动的元素
    public $move;
    //是否允许拖拽到窗口外
    public $moveOut;
    //拖动完毕后的回调方法
    public $moveEnd;
    // tips方向和颜色
    public $tips;
    //是否允许多个tips
    public $tipsMore;
    //层弹出后的成功回调方法
    public $success;
    //确定按钮回调方法
    public $yes;
    //右上角关闭按钮触发的回调
    public $cancel;
    //层销毁后触发的回调
    public $end;
    //分别代表最大化触发的回调
    public $full;
    //分别代表最小化触发的回调
    public $min;
    //分别代表还原触发的回调
    public $restore;

    public function run()
    {
        $config = $this->getConfig();

        return $this->render('layer',[
            'content' => $config,
        ]);
    }

    protected function getConfig(){
        $obj = new self();
        $reflect = new \ReflectionClass($obj);
        $config = [];
        foreach ($reflect->getProperties() as $item){
            if ($item->isPublic()){
                $name = $item->name;
                if (isset($this->$name)){
                    $config[$name] = $this->$name;
                }
            }
        }
        return $config;
    }
}