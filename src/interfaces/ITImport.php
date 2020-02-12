<?php
/**
 * Created by PhpStorm.
 * User: 黄定波
 * Date: 2019/12/19
 * Time: 16:49
 */
namespace dsj\components\interfaces;

interface ITImport
{
    /**
     * @param $data
     * @return boolean
     */
    public function dealExcelData($data);

    /**
     * @return array
     */
    public function getExcelKeyForDatabaseKeyMap();
}