<?php


namespace tools;

use tools\date\Date;

/**
 * 工具入口
 * Class Tools
 * @package tools
 */
class Tools
{

    protected $container = [];

    /**
     * 日期工具
     */
    public function date()
    {
        if (empty($container['date']))
            $container['date'] = Date::class;
        return $container['date'];
    }

    public function encrypt()
    {

    }
}