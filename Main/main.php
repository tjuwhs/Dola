<?php
/***************************************************************************
 * 
 * Copyright (c) 2014 Baidu.com, Inc. All Rights Reserved
 * 
 **************************************************************************/
 
 
 
/**
 * @file main.php
 * @author wanghangsheng(com@baidu.com)
 * @date 2014/07/07 11:22:41
 * @brief 
 *  


 /**
  *
  *AutoLoader Function
  *
  **/

define ('ROOT',realpath(dirname(__FILE__)));

function _auto_loader($class_name)
{
    if (class_exists($class_name, false))
    {
        return;
    }


    //特别处理Smarty类加载
    if('Smarty' === $class_name)
    {
        include ROOT . "/Lib/Smarty/Smarty.class.php";
    }
    else
    {
        $class_name = str_replace('_','/',$class_name);

        $real_class_file = ROOT . "/$class_name.php";

        if(file_exists($real_class_file))
        {
            include $real_class_file;
        }
    }
}

spl_autoload_register('_auto_loader');


/**
 *Router Process
 *
 **/

$request_uri = $_SERVER['REQUEST_URI'];
$query_string = strpos($request_uri,'?');

if (false === $query_string)
{
    $path_info = $request_uri;
}
else
{
    $path_info = substr($request_uri, 0, $query_string);
}

$path_info = trim($path_info, '/');

$segment = explode('/', $path_info);

$controller = (isset($segment[0]) && $segment[0] != '') ? $segment[0] : 'index';

$action = (isset($segment[1]) && $segment[1] != '') ? $segment[1] : 'index';
$controllerName = "Ui_" . ucfirst($controller);
$controlClass = new $controllerName($action);
$controlClass->render();

// Base Class
//

class UI_Controller
{ 

    protected $renderType = '';

    protected $tplName = '';

    protected $smartyEngine = null;

    protected $action = '';

    protected $actionMap = Array();

    protected $renderMap = Array();

    protected $tplMap = Array();

    protected $result = Array();
    public function _initView()
    {
        switch($this->renderType)
        {
            case 'ajax' :    
                break;
            case 'smarty' :
                $smarty = new Smarty();
                $smarty->template_dir = ROOT . "/views";
                $smarty->compile_dir = ROOT . "templates_c";

                $this->smartyEngine = $smarty;
                $this->tplName = "index.tpl";
                break;
        }
    }

    final public function render()
    {

        if(isset($this->renderMap[$this->action]))
        {
            $this->renderType = $this->renderMap[$this->action];
        }

        $this->_initView();
        //如果是smarty，则设置模板

        if ('smarty' == $this->renderType && isset($this->tplMap[$this->action]))
        {
            $this->tplName = $this->tplMap[$this->action];
        }
        if(isset($this->actionMap[$this->action]))
        {
            $actionName = $this->actionMap[$this->action];
            $actionClass = new $actionName ();
            $this->result = $actionClass->run();
        }

        if('smarty' == $this->renderType && !empty($this->result))
        {
            foreach ($this->result as $key => $value)
            {
                $this->smartyEngine->assign($key, $value);
            }
        }

        $this->display();
    }

    final public function display()
    {
        switch($this->renderType)
        {
        case 'ajax':
            echo json_encode($this->result);
            break;
        case 'smarty':
            $this->smartyEngine->display($this->tplName);
            break;
        }
    }
}
/* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */
