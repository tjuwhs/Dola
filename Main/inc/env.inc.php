<?php
/***************************************************************************
 * 
 * Copyright (c) Dola , Inc. All Rights Reserved
 * 
 **************************************************************************/


/**
 * @file: ${FILE_NAME}.
 * @author: tjuwhs@gmail.com
 * @date: 14-9-15 下午9:59
 * @brief:
 */

require ROOT . "/conf/" . Env::getEnvironment() . ".inc.php";

final class Env
{
    private static $token = 0;
    private static $logName = 'Main';
    private static $remoteIP = null;
    private static $uiEnv = null;
    private static $dbObj = null;
    private static $logObjs = array();

    public static function setLogName($name)
    {
        self::$logName = $name;
    }

    public static function getEnvironment()
    {
        if(self::$uiEnv !== null)
        {
            return self::$uiEnv;
        }
        $_ui_env     = get_cfg_var('ui.environment');
        $_ui_env     = $_ui_env ? $_ui_env : 'dev';
        self::$uiEnv = $_ui_env;
        return $_ui_env;
    }
    public static function getLogger($name = null)
    {
        if(null === $name)
        {
            $name = self::$logName;
        }
        if(!isset(self::$logObjs[$name]))
        {
            $name_lower = mb_strtolower($name);
            $logpath = str_replace("#module#", $name_lower, EnvConf::$logPath);
            $logger = new Dola_Log(array(
                'logname' => $name,
                'logpath' => $logpath,
                'loglevel' => EnvConf::$logLevel,
                'reqip' => self::getIP(),
                'token' => dechex(self::getToken()),
            ));
            self::$logObjs[$name] = $logger;
        }

        return self::$logObjs[$name];
    }

    public static function getIP()
    {
        if(!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
        {
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $ip = $ips[0];
        }
        else
        {
            if(!empty($_SERVER['HTTP_CLIENTIP']))
            {
                $ip = $_SERVER['HTTP_CLIENTIP'];
            }
            else
            {
                if(!empty($_SERVER['REMOTE_ADDR']))
                {
                    $ip = $_SERVER['REMOTE_ADDR'];
                }
                else
                {
                    $ip = '127.0.0.1';
                }
            }
        }
        self::$remoteIP = $ip;
        return self::$remoteIP;
    }

    public static function getToken()
    {
        if(0 === self::$token)
        {
            self::$token = intval(microtime(true) * 1000000) + mt_rand(0, 999);
        }
        return self::$token;
    }

    public static function getDB()
    {
        if(isset(self::$dbObj) && self::$dbObj instanceof Dola_DB)
        {
            return self::$dbObj;
        }
        else
        {
            $config = EnvConf::$dbOptions;
            self::$dbObj = new Dola_DB($config);
            return self::$dbObj;
        }
    }

    public static function getAllModule()
    {
        return EnvConf::$moduleList;
    }
}