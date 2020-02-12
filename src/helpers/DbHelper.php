<?php


namespace dsj\components\helpers;


class DbHelper
{
    /**
     * @param string $dbName
     * @return mixed
     * @property \yii\db\Connection $dbName The database connection. This property is read-only.
     */
    public static function getDb($dbName = 'db'){
        return \Yii::$app->$dbName;
    }
}