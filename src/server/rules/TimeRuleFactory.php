<?php


namespace dsj\components\server\rules;


class TimeRuleFactory implements ITRulesFactory
{
    private static $instance = null;

    private function __construct(){}

    private function __clone(){}

    public static function getInstance()
    {
        if (self::$instance === null){
            self::$instance = new TimeRule();
        }

        return self::$instance;
    }
}