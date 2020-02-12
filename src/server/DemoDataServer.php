<?php


namespace dsj\components\server;

use dsj\demoData\models\DemoData;
use yii\helpers\Json;
use yii\web\ForbiddenHttpException;

class DemoDataServer extends AbsDemoDataServer
{
    protected function getList()
    {

        $data = DemoData::find()->where(['url' => $this->url,'is_open' => '1'])->asArray()->all();
        $num = count($data);
        $list = [];
        if ($num == 1){
            if (!$data[0]['url_rule']){
                $list = $data[0];
            }else{
                $this->url_rule = Json::decode($data[0]['url_rule']);
                RulesServer::decodeRules($this->url_rule);
                foreach ($this->url_rule as $key => $value){
                    if ($this->params[$key] == $value){
                        $list = $data[0];
                        break;
                    }
                }
            }
        }elseif ($num > 1){
            foreach ($data as $item){
                if (!$item['url_rule']){
                    if (!$list){
                        $list = $item;
                    }
                    continue;
                }
                $url_rule = Json::decode($item['url_rule']);
                RulesServer::decodeRules($url_rule);
                foreach ($url_rule as $key => $value){
                    if (isset($this->params[$key]) && $this->params[$key] == $value){
                        //参数更精确的覆盖不精确的
                        $list = $item;
                        $this->url_rule = $url_rule;
                        break;
                    }
                }
            }
        }
        return $list ? $list : false;
    }

    protected function updateData($unique_id, $data)
    {
        if (\Yii::$app->db->createCommand()->update('t_demo_data',$data,['unique_id' => $unique_id])->execute()){
            return true;
        }

        return false;
    }

    protected function checker()
    {
        if (!$this->url){
            throw new ForbiddenHttpException('请先设置url');
        }
        return true;
    }

}