<?php


namespace dsj\components\helpers;


use yii\web\ForbiddenHttpException;

class LogHelper
{
    protected $data = '';
    protected $path = null;
    protected $fileName = null;
    protected $route = null;
    protected $path_is_absolute = false;
    protected $type = FILE_APPEND;
    protected $is_format = true;

    /**
     * @param $data
     * @return $this
     * 日志数据
     */
    public function setData($data){
        $this->data = $data;

        return $this;
    }

    /**
     * @param $path
     * @return $this
     * 日志路径
     * 1、不设置默认实在console/runtime/当前日期/
     * 2、使用别名设置路径，只需传入分组别名，如\Yii::getAlias('@test')，日志将放在：@console/runtime/当前日期/
     * 3、使用绝对路径，path_is_absolute参数需设置成true,路径使用设置的绝对路径
     */
    public function setPath($path){
        $this->path = $path;

        return $this;
    }

    /**
     * @param $fileName
     * @return $this
     * 设置文件名
     */
    public function setFileName($fileName){
        $this->fileName = $fileName;

        return $this;
    }

    /**
     * @param $type
     * 设置日志写入类型，默认追加形式
     */
    public function setType($type){
        $this->type = $type;

        return $this;
    }

    /**
     * @param $route
     * @return $this
     * 设置控制器路由，该参数是必须的，用于区分是那个控制器那个方法产生的日志
     */
    public function setRoute($route){
        $this->route = $route;

        return $this;
    }

    public function setIsFormat($is_format = true){
        $this->is_format = $is_format;

        return $this;
    }
    /**
     * @return $this
     * 设置日志路径是否绝对路径，默认是false
     */
    public function setPathIsAbsolute(){
        $this->path_is_absolute = true;

        return $this;
    }

    /**
     * @throws ForbiddenHttpException
     * 写日志前的初始化操作
     */
    protected function init(){
        if (!$this->route){
            throw new ForbiddenHttpException('请设置控制器唯一键！！！');
        }
        if (is_string($this->route)){
            $this->route = str_replace('/','_',$this->route);
        }

        if ($this->path && !$this->path_is_absolute){
            $this->path = $this->path . '/runtime/' .date('Ymd') . '/';
            if (!is_dir($this->path)){
                mkdir($this->path,0777,true);
                chmod($this->path,0777);
            }
        }elseif ($this->path && $this->path_is_absolute){
            if (!is_dir($this->path)){
                mkdir($this->path,0777,true);
                chmod($this->path,0777);
            }
        }else{
            $this->path = \Yii::getAlias('@console') . '/runtime/' .date('Ymd') . '/';
            if (!is_dir($this->path)){
                mkdir($this->path,0777,true);
                chmod($this->path,0777);
            }
        }
        if (!$this->fileName){
            $this->fileName = $this->route . '.txt';
        }
        if ($this->is_format){
            $this->formatData();
        }
    }

    /**
     *格式化日志数据
     */
    protected function formatData(){
        $header = '>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>'.PHP_EOL;
        $content = date('Y-m-d H:i:s').PHP_EOL;
        $data = "<$this->route>" . PHP_EOL . $this->data . PHP_EOL;
        $footer = '----------------------------------------------------------------------------------------------------------'.PHP_EOL;
        $this->data = $header . $content . $data . $footer;
    }

    /**
     * @throws ForbiddenHttpException
     * 写日志
     */
    public function write(){
        $this->init();
        file_put_contents($this->path.$this->fileName,$this->data,$this->type);
        chmod($this->path.$this->fileName,0777);
    }

    /**
     * @throws ForbiddenHttpException
     * 获取日志的初始化操作
     */
    protected function getLogInit(){
        if ($this->path && !$this->path_is_absolute){
            $this->path = $this->path . '/runtime/' .date('Ymd') . '/';
            if (!is_dir($this->path)){
                throw new ForbiddenHttpException('日志路径不存在');
            }
        }elseif ($this->path && $this->path_is_absolute){
            if (!is_dir($this->path)){
                throw new ForbiddenHttpException('日志路径不存在');
            }
        }else{
            $this->path = \Yii::getAlias('@console') . '/runtime/' .date('Ymd') . '/';
            if (!is_dir($this->path)){
                throw new ForbiddenHttpException('日志路径不存在');
            }
        }
        if (!$this->fileName){
            throw new ForbiddenHttpException('请先设置要获取的日志文件名');
        }
    }

    /**
     * @return false|string
     * @throws ForbiddenHttpException
     * 获取日志
     */
    public function getLog(){
        $this->getLogInit();
        return file_get_contents($this->path . $this->fileName);
    }



}