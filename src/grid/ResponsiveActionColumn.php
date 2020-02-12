<?php
namespace dsj\components\grid;

use Yii;
use yii\grid\ActionColumn;
use yii\helpers\Html;

class ResponsiveActionColumn extends ActionColumn
{
    protected function initDefaultButtons()
    {
        $this->initDefaultButton('view', 'eye-open');
        $this->initDefaultButton('update', 'pencil');
        $this->initDefaultButton('delete', 'trash');
    }

    protected function initDefaultButton($name, $iconName, $additionalOptions = [])
    {
        if (!isset($this->buttons[$name]) && strpos($this->template, '{' . $name . '}') !== false) {
            $this->buttons[$name] = function ($url, $model, $key) use ($name, $iconName, $additionalOptions) {

                switch ($name) {
                    case 'view':
                        $title = Yii::t('yii', '查看');
                        $className = "btn btn-primary btn-sm data-$name";
                        break;
                    case 'update':
                        $title = Yii::t('yii', '修改');
                        $className = "btn btn-warning btn-sm data-$name";
                        break;
                    case 'delete':
                        $title = Yii::t('yii', '删除');
                        $className = "btn btn-danger btn-sm data-$name";
                        break;
                    default:
                        $title = ucfirst($name);
                }
                $options = array_merge([
                    'title' => $title,
                    'aria-label' => $title,
                    'data-pjax' => '0',
                    'class' => $className,
                    'url' => $url,
                ], $additionalOptions, $this->buttonOptions);

                return Html::button($title,$options);
            };
        }
    }
}