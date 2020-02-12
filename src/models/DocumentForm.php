<?php


namespace dsj\components\models;


use yii\base\Model;

class DocumentForm extends Model
{
    public $id;
    public $content;
    public $savePath;

    public function rules()
    {
        return [
            [['content','id','savePath'],'required'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'content' => '内容'
        ];
    }

    public function save(){
        if (!is_dir($this->savePath)){
            mkdir($this->savePath,0777,true);
            chmod($this->savePath,0777);
        }
        if (file_put_contents($this->savePath.$this->id,$this->content)){
            return true;
        }
       return false;
    }

    public function getContent(){
        if ($this->id && $this->savePath){
            return file_get_contents($this->savePath.$this->id);
        }
        return false;
    }
}