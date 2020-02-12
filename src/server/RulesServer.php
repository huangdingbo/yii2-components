<?php


namespace dsj\components\server;


use dsj\components\helpers\CalculateHelper;

class RulesServer
{
    /**
     * @param $data
     * 解析为json结构的规则
     */
    public static function encodeRules(&$data){
        foreach ($data as $key => &$item){
            if (is_array($item)){
                if (self::is_assoc($item)){
                    self::encodeRules($item);
                }else{
                    $num = count($item);
                    $item = [
                        'rule' => 'list',
                        'num' => $num,
                        'child' => $item[0],
                    ];
                    self::encodeRules($item['child']);
                }
            }else{
                $item = 'fixed:' . $item . ':' . $key;
            }
        }
    }

    /**
     * @param $rules
     * @param array $global_params
     * 解析json结构规则-数据
     */
    public static function decodeRules(&$rules,$global_params = []){
        foreach ($rules as &$item){
            if (is_array($item)){
                if (isset($item['num']) && isset($item['rule']) && $item['rule'] == 'list'){
                    $temp = [];
                    for ($i=0;$i<$item['num'];$i++){
                        $temp[$i] = $item['child'];
                    }
                    self::decodeList($temp,$global_params);
                    $item = $temp;
                }
                self::decodeRules($item,$global_params);
            }else{
                $arr = explode(':',$item);
                $rule = $arr[0];
                $value = isset($arr[1]) ? $arr[1] : '';
                if ($rule == 'date'){
                    $value = str_replace('*',':',$value);
                }
                if ($rule == 'fixed'){
                    $num = count($arr);
                    if ($num >3){
                        for ($i=0;$i<$num-2;$i++){
                            if ($i == 0){
                                $value = $arr[$i+1];
                            }else{
                                $value .= ':' . $arr[$i+1];
                            }
                        }
                    }
                }


                $item = self::getValueByRule($rule,$value,$global_params);
            }
        }
    }

    /**
     * @param $rules
     * 解析json结构规则-文档
     */
    public static function decodeRulesDoc(&$rules){

        foreach ($rules as $key => &$item){
            if (is_array($item)){
                if (isset($item['num']) && isset($item['rule']) && $item['rule'] == 'list'){
                    $temp = [];
                    $temp[0] = $item['child'];
                    $item = $temp;
                }
                self::decodeRulesDoc($item);
            }else{
                $arr = explode(':',$item);
                $doc = isset($arr[2]) ? $arr[2] : $key;
                $item = $doc;
            }
        }
    }

    /**
     * @param $arr
     * @return bool true=>关联数组，false=>数字数组
     * 判断数组是关联数组还是数字数组
     */
    public static function is_assoc($arr){
        return array_keys($arr) !== range(0, count($arr) - 1);
    }

    /**
     * @param $rule
     * @param $value
     * @param array $global_params
     * @return false|int|mixed|string
     * 解析规则
     */
    protected static function getValueByRule($rule,$value,$global_params = []){
        switch ($rule){
            //固定值 规则：'fixed:123'
            case 'fixed':
                try{
                    $valueArr = explode('|',$value);
                    if (!isset($valueArr[1])){
                        if($valueArr[0] == '[]'){
                            $valueArr[0] = array();
                        }
                        return $valueArr[0];
                        break;
                    }
                    $global_params['fixed'] = $valueArr[0];

                    return CalculateHelper::calculate($valueArr[1],$global_params);
                    break;
                }catch (\Exception $e){
                    return '固定值规则错误，请检查！';
                    break;
                }
            //随机数 规则：'rand:0,10'
            case 'rand':
                try{
                    $valueArr = explode('|',$value);
                    $randArr = explode(',',$valueArr[0]);
                    $randValue = rand($randArr[0],$randArr[1]);
                    if (!isset($valueArr[1])){
                        return $randValue;
                        break;
                    }
                    $global_params['rand'] = $randValue;
                    return CalculateHelper::calculate($valueArr[1],$global_params);
                    break;
                }catch (\Exception $e){
                    return '随机数规则错误，请检查';
                    break;
                }

            //在特定范围内随机取 规则：'in:男,女'
            case 'enum':
                try{
                    $inArr = explode(',',$value);
                    return $inArr[rand(0,count($inArr)-1)];
                    break;
                }catch (\Exception $e){
                    return '枚举规则错误，请检查';
                    break;
                }
            //生成当前时间的任意格式 规则：'date:Y-m-d H*i*s'
            case 'date':
                try{
                    $str = str_replace('*',':',$value);
                    $arr = explode('|',$str);
                    $dateRule = $arr[0];
                    $addDateRule = isset($arr[1]) ? $arr[1] : '';
                    $time = date($dateRule);
                    if ($addDateRule){
                        $res = date($dateRule,strtotime("$time $addDateRule"));
                    }else{
                        $res = $time;
                    }
                    if (isset($arr[2])){
                        $res = date($arr[2],strtotime($res));
                    }
                    return $res;
                    break;
                }catch (\Exception $e){
                    return '时间规则错误，请检查';
                    break;
                }
            //随机电话号码
            case 'phone':
                return RuleValueServer::getPhoneNum(1,'array',true)[0];
                break;
            //随机姓名
            case 'name':
                return RuleValueServer::getName(1)[0];
                break;
            //全局参数
            case 'global':
                try{
                    if (!$global_params){
                        return 0;
                        break;
                    }
                    return (string)CalculateHelper::calculate($value,$global_params);
                }catch (\Exception $e){
                    return '全局参数规则错误，请检查';
                    break;
                }
        }

        return '';
    }

    /**
     * @var int
     * 树形结构的自增id
     */
    private static $id = 0;
    /**
     * @param $data
     * @param int $pid
     * 解析为树形结构的规则
     */
    public static function encodeRulesForTree(&$data,$pid = 0){
        foreach ($data as $key => &$item){
            self::$id++;
            if (is_array($item)){
                if (self::is_assoc($item)){
                    $item = [
                        'id' => self::$id,
                        'pid' => $pid,
                        'rule' => 'item',
                        'child' => $item,
                        'description' => $key,
                    ];
                }else{
                    $num = count($item);
                    $child = $item[0];
                    $item = [
                        'id' => self::$id,
                        'pid' => $pid,
                        'num' => $num,
                        'rule' => 'list',
                        'child' => $child,
                        'description' => $key,
                    ];
                }
                self::encodeRulesForTree($item['child'],self::$id);
            }else{
                $item = [
                    'id' => self::$id,
                    'pid' => $pid,
                    'rule' => 'fixed',
                    'value' => $item,
                    'description' => $key,
                ];
            }
        }
    }

    /**
     * @param $data
     * 解析树形结构规则
     */
    public static function decodeRulesForTree(&$data){
        foreach ($data as &$item){
            if ($item['rule'] == 'item'){
                $item = $item['child'];
                self::decodeRulesForTree($item);
            }elseif ($item['rule'] == 'list'){
                $temp = [];
                for ($i=0;$i<$item['num'];$i++){
                    $temp[] = [
                        'rule' => 'item',
                        'child' => $item['child'],
                    ];
                }
                $item = $temp;
                self::decodeRulesForTree($item);
            }else{
                $item = self::getValueByRule($item['rule'],$item['value']);
            }
        }
    }

    public static function decodeList(&$list,$global_params = []){
        foreach ($list as $k => &$item){
            foreach ($item as $key => &$value){
                if (is_array($value)){
                    if (!$value){
                        $value = "fixed:[]:空";
                    }else{
                        self::decodeList($value,$global_params);
                    }
                }
                $ruleArr = explode(':',$value);
                $valueArr = explode('|',$ruleArr[1]);
                $ps = isset($ruleArr[2]) ? $ruleArr[2] : '';
                if (!isset($valueArr[1])){
                    continue;
                }
                switch ($ruleArr[0]){
                    //将第一次的时间作为其后变化的基础 第一个规则是输入的时间格式，第二个是附加规则，第三个是输出格式
                    case 'date':
                        try{
                            $valueArr[0] = str_replace('*',':',$valueArr[0]);
                            if ($k == 0){ //先是尝试用全局变量中的月份，如果没有就用当前月
                                if (isset($global_params[$key])){
                                    $res = $global_params[$key];
                                }else{
                                    $res = date($valueArr[0]);
                                }
                                $global_params[$key] = $res;
                                if (isset($valueArr[2])){
                                    $res = date($valueArr[2],strtotime($res));
                                }
                                $value = 'fixed:' . $res . ':' . $ps;
                            }else{
                                $time = $global_params[$key];
                                $res = date($valueArr[0],strtotime("$time $valueArr[1]"));
                                $global_params[$key] = $res;
                                if (isset($valueArr[2])){
                                    $res = date($valueArr[2],strtotime($res));
                                }
                                $value = 'fixed:' . $res . ':' . $ps;
                            }
                            break;
                        }catch (\Exception $e){
                            $value = 'fixed:date-error:error';
                        }
                    //先是按照第一个规则计算出结果，保存到全局参数中，下次如果要用第一次计算出的值，就用fixed代替，此后每一次都是用附加规则来计算的
                    case 'fixed':
                        try{
                            if ($k == 0){
                                $value = 'fixed:'.$valueArr[0] . ':' . $ps;
                                $global_params[$key] = $valueArr[0];
                            }else{
                                $global_params['fixed'] = $global_params[$key];
                                $res = CalculateHelper::calculate($valueArr[1],$global_params);
                                $value = 'fixed:'.$res . ':' . $ps;
                                $global_params[$key] = $res;
                            }
                            break;
                        }catch (\Exception $e){
                            $value = 'fixed:fixed-error:error';
                        }
                    //先是按照第一个规则计算出结果，保存到全局参数中，下次如果要用第一次计算出的值，就用rand代替，此后每一次都是用附加规则来计算的
                    case 'rand':
                        try{
                            if ($k == 0){
                                $numArr = explode(',',$valueArr[0]);
                                $num = rand($numArr[0],$numArr[1]);
                                $value = 'fixed:'.$num . ':' . $ps;
                                $global_params[$key] = $num;
                            }else{
                                $global_params['rand'] = $global_params[$key];
                                $res = CalculateHelper::calculate($valueArr[1],$global_params);
                                $value = 'fixed:'.$res . ':' . $ps;
                                $global_params[$key] = $res;
                            }
                            break;
                        }catch (\Exception $e){
                            $value = 'fixed:rand-error:error';
                        }
                    //列表中的枚举，必须加 | 附加参数，目前就只有一个附加规则，order表示从列出的项中逐个取值，取完了就是空，随机取值不用给附加参数，默认就是随机取
                    case 'enum':
                        try{
                            if ($k == 0){
                                $enumArr = explode(',',$valueArr[0]);
                                $global_params[$key] = $enumArr;
                                if ($valueArr[1] == 'order'){
                                    $value = 'fixed:'.$enumArr[$k] . ':' . $ps;
                                    unset($global_params[$key][$k]);
                                }else{
                                    $value = 'fixed:'.'' . ':' . $ps;
                                }
                            }else{
                                if ($valueArr[1] == 'order'){
                                    $enumArr = $global_params[$key];
                                    if (!$enumArr){
                                        $enumArr[$k] = '';
                                    }
                                    $value = 'fixed:'.$enumArr[$k] . ':' . $ps;
                                    unset($global_params[$key][$k]);
                                }else{
                                    $value = 'fixed:'.'' . ':' . $ps;
                                }
                            }
                            break;
                        }catch (\Exception $e){
                            $value = 'enum:enum-error:error';
                        }
                    //先是按照第一个规则计算出结果，保存到全局参数中，下次如果要用第一次计算出的值，就用global代替，此后每一次都是用附加规则来计算的
                    case 'global':
                        try{
                            if ($k == 0){
                                $res = CalculateHelper::calculate($valueArr[0],$global_params);
                                $value = 'fixed:'.$res . ':' . $ps;
                                $global_params[$key] = $res;
                            }else{
                                $global_params['global'] = $global_params[$key];
                                $res = CalculateHelper::calculate($valueArr[1],$global_params);
                                $value = 'fixed:'.$res . ':' . $ps;
                                $global_params[$key] = $res;
                            }
                            break;
                        }catch (\Exception $e){
                            $value = 'fixed:global-error:error';
                        }
                }
            }
        }
    }
}