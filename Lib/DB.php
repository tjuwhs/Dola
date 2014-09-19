<?php
/***************************************************************************
 * 
 * Copyright (c) Dola , Inc. All Rights Reserved
 * 
 **************************************************************************/


/**
 * @file: ${FILE_NAME}.
 * @author: tjuwhs@gmail.com
 * @date: 14-9-15 下午3:31
 * @brief:
 */

class Dola_DB
{
    /**
     * DB配置
     * @var array
     */
    private $_host = Array();
    private $_userName = '';
    private $_passWord = '';
    private $_dbName = '';
    private $_port = '';
    private $_charset = 'utf-8';

    /**
     * SQL 语句
     * @var string
     */
    private $_strQueryStr = '';

    /**
     * 最后影响的ID
     * @var int
     */
    private $_intLastInsID = null;

    /**
     * 操作影响的条数
     * @var int
     */
    private $_intNumRows = null;

    /**
     * 链接对象
     * @var obj
     */
    private $_objLink = null;
    /**
     * 查询的结果
     * @var null
     */
    private $_objResult = null;
    private $_boolConnected = false;

    /**
     * @param null $arrConfig
     */
    public function __construct($arrConfig = null)
    {
        if (!extension_loaded('mysqli')) {
            throw new RuntimeException('Extension Mysqli is not loaded');
        }
        if (empty($arrConfig) || !isset($arrConfig['host']) || !isset($arrConfig['username']) || !isset($arrConfig['password'])
            || !isset($arrConfig['db']) || !isset($arrConfig['port'])
        ) {
            throw new RuntimeException('Dola_DB Configuration Error');
        }
        $this->_host     = $arrConfig['host'];
        $this->_userName = $arrConfig['username'];
        $this->_passWord = $arrConfig['password'];
        $this->_dbName   = $arrConfig['db'];
        $this->_port     = $arrConfig['port'];
        $this->_charset  = isset($arrConfig['charset']) ? $arrConfig['charset'] : 'utf-8';
    }

    public function __destruct()
    {
        if (!empty($this->_objResult)) {
            mysqli_free_result($this->_objResult);
        }
        if (!empty($this->_objLink)) {
            mysqli_close($this->_objLink);
        }
        unset($this->_objLink);
        unset($this->_objResult);
        $this->_boolConnected = false;
    }

    protected function _connect()
    {
        if(empty($this->_objLink) || !$this->_boolConnected)
        {
            $error_message = "No available db connection profile.";
            $hosts = $this->_host;
            shuffle($hosts);
            while(!empty($hosts))
            {
                $host = array_pop($hosts);
                //mysqli初始化
                $this->_objLink = mysqli_init();
                if(!$this->_objLink->options(MYSQLI_OPT_CONNECT_TIMEOUT, 1))
                {
                    throw new RuntimeException("Failed to set timeout to mysqli connection");
                }
                //mysqli 连接
                $this->_objLink->real_connect($host, $this->_userName, $this->_passWord, $this->_dbName, $this->_port);

                if(mysqli_connect_error())
                {
                    $error_message = 'Failed to connect DB [' . mysqli_connect_errno() . "]" . mysqli_connect_error();
                    error_log($error_message);
                    continue;
                }
                if(!$this->_objLink->set_charset($this->_charset))
                {
                    $error_message = "Failed to set character set " . $this->_charset;
                    error_log($error_message);
                    continue;
                }

                $this->_objLink->select_db($this->_dbName);
                $this->_boolConnected = true;
                break;
            }
            if(!$this->_boolConnected)
            {
                throw new RuntimeException($error_message);
            }
        }
        return $this->_objLink;
    }

    protected function _execute($strSql, $flag = false)
    {
        $this->_connect();
        $this->_strQueryStr = $strSql;
        if(defined('DOAL_DEBUG'))
        {
            error_log($strSql);
        }
        $result = $this->_objLink->query($this->_strQueryStr);
        if(false === $result)
        {
            throw new RuntimeException("SQL EXECUTE ERROR, SQL:" . $strSql);
        }
        if($flag)
        {
            $this->_objResult = $result;
            $this->_intNumRows = mysqli_num_rows($this->_objLink);
        }
        else
        {
            $this->_intNumRows = mysqli_affected_rows($this->_objLink);
            $this->_intLastInsID = mysqli_insert_id($this->_objLink);
        }
    }

    public function delete($strTable, $strWhere)
    {
        $sql = "DELETE FROM {$$strTable} WHERE {$$strWhere}";
        return $this->_execute($sql);
    }


    public function execute($strSql)
    {
        return $this->_execute($strSql);
    }
    /**
     * 向表中插入一行。
     * @param $strTable
     * @param $arrBind
     */
    public function insert($strTable, $arrBind)
    {
        $fields = "(";
        $values = "(";
        foreach($arrBind as $key => $value)
        {
            $fields .= "`" . $key . "`";
            $fields .= ",";
            $values .= $this->escape($value);
            $values .= ",";
        }
        $fields = rtrim($fields, ',');
        $values = rtrim($values, ',');
        $fields .= ")";
        $values .= ")";
        $sql = "INSERT INTO {$strTable} {$fields} VALUES {$values}";
        return $this->_execute($sql);
    }

    /**
     * @param $strTable
     * @param $arrBind
     * @param $strWhere
     * @return mixed
     */
    public function update($strTable, $arrBind, $strWhere)
    {
        $sql = "UPDATE {$strTable} SET ";
        $setString = "";
        foreach ($arrBind as $key => $value) {
            $strSeg = "`" . $key . "`";
            $valSeg = $this->escape($value);
            $strSeg .= (" = " . $valSeg);
            $strSeg .= ",";
            $setString .= $strSeg;
        }

        $setString = rtrim($setString, ',');
        $sql .= $setString;
        $sql .= (" WHERE " . $strWhere);
        return $this->execute($sql);
    }


    public function beginTransaction()
    {
        $this->_execute("START TRANSACTION");
        return null;
    }

    public function commit()
    {
        $this->_execute("COMMIT");
        return null;
    }
    /**
     * @param $string
     * @return string
     */
    public function escape($string)
    {
        $this->_connect();
        return "'" . $this->_objLink->real_escape_string($string) . "'";
    }

    /**
     * @param $string
     * @return mixed
     */
    public function real_escape($string)
    {
        $this->_connect();
        return $this->_objLink->real_escape_string($string);
    }


}