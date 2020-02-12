<?php


namespace dsj\components\server\rules;


class TimeRule extends AbsRules
{
    /**
     * @param $rule
     * @return false|int|string
     * @param $rule
     * 规则： time|-1 month|Y-m-d H:i:s
     *
     * 使用：time是关键词，第一个管道做运算，第二个管道格式化
     * 1、返回当前时间戳： time
     * 2、返回当前时间并格式化成Y-m-d： time||Y-m-d
     *3、返回当前时间减去一天的时间戳： time|-1 day
     */
    public function decodeRule($rule)
    {
        $arr = explode('|',$rule);

        if ($arr[0] != 'time'){
            throw new \Exception('不支持的规则:' . $arr[0]);
        }

        $result = time();

        if (isset($arr[1])){
            $result = date('Y-m-d H:i:s',$result);

            $result = date('Y-m-d H:i:s',strtotime("$result $arr[1]"));
        }

        if (isset($arr[2])){
            if (is_numeric($result)){
                $result = date($arr[2],$result);
            }else{
                $result = date($arr[2],strtotime($result));
            }
        }else{
            if (!is_numeric($result)){
                $result = strtotime($result);
            }
        }

        return $result;
    }
}