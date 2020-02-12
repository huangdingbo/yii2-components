<?php


namespace dsj\components\server;


use dsj\components\helpers\ArrayHelper;
use yii\helpers\Json;

abstract class AbsDemoDataServer
{
    //保存符合规则的url
    protected $url;

    //全局参数
    protected $global_params = [];

    //url规则数据
    protected $url_rule = [];

    //请求参数
    protected $params = [];

    //解析后的参数规则
    protected $params_rule = [];

    //参数缓存数据
    protected $params_cache = [];

    //解析后的变化规则
    protected $change_rule = [];

    //变化规则的缓存数据
    protected $change_cache = [];

    //demo_data 规则列表
    protected $list;

    //url加密后的id
    protected $unique_id;

    //返回的数据
    protected $data = null;

    //获取demo_data 列表
    abstract protected function getList();

    //参数检查
    abstract protected function checker();

    //添加缓存数据
    abstract protected function updateData($unique_id,$data);

    public function setUrl($url){
        $this->url = $url;
        return $this;
    }

    public function setParams($params){
        $this->params = $params;
        return $this;
    }

    public function getData(){
        if (!$this->init()){
            return false;
        }
        //执行逻辑
        if ($this->execute()){
            return $this->data;
        }
        return false;
    }

    protected function init(){
        //检查
        if (!$this->checker()){
            return false;
        }
        //unique_id
        $urlArr = explode('?',$this->url);
        $url = $urlArr[0];
        if (isset($this->params['r'])){
            $url = $url . "?r=" . $this->params['r'];
            unset($this->params['r']);
        }
        $this->url = $url;
        //获取列表
        $this->list = $this->getList();
        if (!$this->list || !$this->list['data_rule']){
            return false;
        }

        //如果存在url规则，则拼接在url后面，一起加密（规定参数中必须含有url_rule中的值）
        if ($this->url_rule){
            foreach ($this->url_rule as $key => $item){
                $this->url .= '&' . $key . '=' . $item;
            }
        }
        $this->unique_id = md5($this->url);
        //解析参数规则
        if ($this->list['params_rule']){
            $this->params_rule = Json::decode($this->list['params_rule']);
            RulesServer::decodeRules($this->params_rule);
        }
        //params_cache
        if ($this->list['params_cache']){
            $this->params_cache = Json::decode($this->list['params_cache']);
        }
        //解析变化规则
        if ($this->list['change_rule']){
            $this->change_rule = Json::decode($this->list['change_rule']);
            RulesServer::decodeRules($this->change_rule);
        }
        //缓存的变化规则
        if ($this->list['change_cache']){
            $this->change_cache = Json::decode($this->list['change_cache']);
        }

        //解析全局参数规则
        if ($this->list['global_params']){
            $this->global_params = Json::decode($this->list['global_params']);
            RulesServer::decodeRules($this->global_params);
        }

        return true;
    }

    protected function execute(){
        //1、如果开启了忽略参数比较
        if ($this->list['is_ignore_params'] == 1){
            //变化规则为空
            if (!$this->change_rule){
                $this->data = Json::decode($this->list['data_rule']);
                RulesServer::decodeRules($this->data,$this->global_params);
                return true;
            }else{ //变化规则不为空
                //如果解析后的变化规则跟缓存的变化规则数据相等
                if (!ArrayHelper::diffArray($this->change_rule,$this->change_cache)){
                    if ($this->list['data_cache']){
                        $this->data = Json::decode($this->list['data_cache']);
                        return true;
                    }else{
                        $this->data = Json::decode($this->list['data_rule']);
                        RulesServer::decodeRules($this->data,$this->global_params);
                        //添加缓存数据
                        if (!$this->updateData($this->unique_id,['data_cache' => Json::encode($this->data)])){
                            return false;
                        };
                        return true;
                    }
                }else{
                    $this->data = Json::decode($this->list['data_rule']);
                    RulesServer::decodeRules($this->data,$this->global_params);
                    //更新缓存数据
                    if (!$this->updateData($this->unique_id,['data_cache' => Json::encode($this->data)])){
                        return false;
                    };
                    //更新缓存参数
                    if (!$this->updateData($this->unique_id,['change_cache' => Json::encode($this->change_rule)])){
                        return false;
                    }
                    return true;
                }
            }
        }
        //2、如果请求参数符合参数规则,开启参数比较的情况
        //如果请求参数跟解析的参数规则数据相同
        if (!ArrayHelper::diffArray($this->params,$this->params_rule) && !ArrayHelper::diffArray($this->params_rule,$this->params)){
            //如果缓存参数跟请求参数一样，如果有缓存数据，直接返回,没有就解析规则
            if (!ArrayHelper::diffArray($this->params_cache,$this->params)){
                if ($this->list['data_cache']){
                    $this->data = Json::decode($this->list['data_cache']);
                    return true;
                }else{
                    $this->data = Json::decode($this->list['data_rule']);
                    RulesServer::decodeRules($this->data,$this->global_params);
                    //添加缓存数据
                    if (!$this->updateData($this->unique_id,['data_cache' => Json::encode($this->data)])){
                        return false;
                    };
                    return true;
                }
            }else{
                $this->data = Json::decode($this->list['data_rule']);
                RulesServer::decodeRules($this->data,$this->global_params);
                //更新缓存数据
                if (!$this->updateData($this->unique_id,['data_cache' => Json::encode($this->data)])){
                    return false;
                };
                //更新缓存参数
                if ($this->params){
                    if (!$this->updateData($this->unique_id,['params_cache' => Json::encode($this->params)])){
                        return false;
                    }
                }
                return true;
            }
        }

        return false;
    }
}