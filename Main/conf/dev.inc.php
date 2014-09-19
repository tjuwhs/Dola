<?php
/***************************************************************************
 * 
 * Copyright (c) Dola , Inc. All Rights Reserved
 * 
 **************************************************************************/


/**
 * @file: ${FILE_NAME}.
 * @author: tjuwhs@gmail.com
 * @date: 14-9-19 下午9:20
 * @brief:
 */

EnvConf::$logPath = getenv('HOME') . '/var/log/#module#.log';
EnvConf::$phpLogPath = getenv('HOME') . '/var/log/php.#module#.log';
EnvConf::$showLogPath = getenv('HOME') . '/var/log/show.#module#.log';
EnvConf::$logLevel = 127;



class EnvConf
{
    public static $dbOptions = array(
        'host' => array(),
        'port' => 0,
        'username' => '',
        'password' => '',
        'db' => '',
        'charset' => 'utf8',
    );

    public static $logPath = '';
    public static $phpLogPath = '';
    public static $showLogPath = '';
    public static $logLevel = 255;

    public static $moduleList = Array('App','Page');
}