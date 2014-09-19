<?php
/***************************************************************************
 * 
 * Copyright (c) Dola , Inc. All Rights Reserved
 * 
 **************************************************************************/


/**
 * @file: ${FILE_NAME}.
 * @author: tjuwhs@gmail.com
 * @date: 14-9-15 下午3:32
 * @brief:
 */

class Dola_Log
{
    const LOG_FATAL = 1;
    const LOG_ERROR = 2;
    const LOG_WARNING = 4;
    const LOG_MONITOR = 8;
    const LOG_INFO = 16;
    const LOG_TRACE = 32;
    const LOG_DEBUG = 64;

    static $LOG_NAME = Array(
        self::LOG_FATAL => 'FATAL',
        self::LOG_ERROR => 'ERROR',
        self::LOG_WARNING => 'WARNING',
        self::LOG_MONITOR => 'MONITOR',
        self::LOG_INFO => 'INFO',
        self::LOG_TRACE => 'TRACE',
        self::LOG_DEBUG => 'DEBUG',
    );

    private $_strLogName = '';

    private $_strLogPath = '';

    private $_strWfLogPath = '';

    private $_strIp = '';

    private $_intLogLevel = 255;

    private $_strToken = '';

    /**
     * @brief  构造函数
     * @param $arrConfig
     */
    public function __construct($arrConfig)
    {
        $this->_strLogName = $arrConfig['logname'];
        $this->_strLogPath = $arrConfig['logpath'];
        $this->_strWfLogPath = $arrConfig['logpath'] . '.wf';
        $this->_strIp = $arrConfig['reqip'];
        $this->_intLogLevel = $arrConfig['loglevel'];
        $this->_strToken = $arrConfig['token'];
    }


    public function refreshIP($ip)
    {
        $this->_strIp = $ip;
    }
    /**
     * Fatal 记录
     */
    public function Fatal()
    {
        $args = func_get_args();
        $this->_writeLog(self::LOG_FATAL, $args);
    }

    public function Error()
    {
        $args = func_get_args();
        $this->_writeLog(self::LOG_ERROR, $args);
    }

    public function Trace()
    {
        $args = func_get_args();
        $this->_writeLog(self::LOG_TRACE, $args);
    }
    
    public function Debug()
    {
        $args = func_get_args();
        $this->_writeLog(self::LOG_DEBUG, $args);
    }
    
    public function Warning()
    {
        $args = func_get_args();
        $this->_writeLog(self::LOG_WARNING, $args);
    }

    public function Info()
    {
        $args = func_get_args();
        $this->_writeLog(self::LOG_INFO, $args);
    }
    
    public function Monitor()
    {
        $args = func_get_args();
        $this->_writeLog(self::LOG_MONITOR, $args);
    }
    
    private function _writeLog($logType, $arrData)
    {
        if (!(intval($logType) & $this->_intLogLevel))
        {
            return;
        }

        $str = sprintf("%s\t%s\t%s\t%s\t%s\t",
            self::$LOG_NAME[$logType],
            date("Y-m-d H:i:s"),
            $this->_strLogName,
            $this->_strIp,
            $this->_strToken
        );

        if(!empty($arrData))
        {
            $str .= implode("\t", $arrData);
        }

        $strLog = '';
        $strWfLog = '';
        switch($logType)
        {
            case self::LOG_MONITOR:
            case self::LOG_WARNING:
            case self::LOG_FATAL:
            case self::LOG_ERROR:
                $debugInfo = debug_backtrace();
                $strWfLog .= "[{$debugInfo[1]['file']}" . ":" . $debugInfo[1]['line'] . "]\t";
                $strWfLog .= $str;
                break;
            case self::LOG_DEBUG:
            case self::LOG_TRACE:
            case self::LOG_INFO:
                $strLog .= $str;
                break;
            default:
                break;
        }

        if(!empty($strWfLog))
        {
            @error_log($strWfLog."\n", 3, $this->_strLogPath);
        }
        if(!empty($strLog))
        {
            @error_log($strLog."\n", 3, $this->_strLogPath);
        }
    }
}