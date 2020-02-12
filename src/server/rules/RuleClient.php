<?php


namespace dsj\components\server\rules;


class RuleClient
{
    public static function decode($rule){

        $arr = explode('|',$rule);

        switch ($arr[0]){
            case 'time':
                return TimeRuleFactory::getInstance()->decodeRule($rule);
                break;
        }

        return $rule;
    }
}