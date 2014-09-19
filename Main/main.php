<?php
/***************************************************************************
 * 
 * Copyright (c) 2014 DOLA, Inc. All Rights Reserved
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
require ROOT . "/inc/env.inc.php";
require ROOT . "/inc/autoLoader.inc.php";
mb_internal_encoding("UTF-8");

$dola_app = new Dola_Application();
$dola_app->run();

final class Dola_Application
{

    static $_instance = null;
    private $_module = '';
    private $_controller = '';
    private $_action = '';
    private $_initialized = false;
    private $_uiRequest = null;
    private $_uiResponse = null;
    private $_moduleInstance = null;
    private $_requestUri = '';

    public function __construct($request = null)
    {
        if(isset($_SERVER['HTTP_HHVM_PATHINFO']))
        {
            $_SERVER['REQUEST_URI'] = $_SERVER['HTTP_HHVM_PATHINFO'];
        }
        if(self::$_instance !== null)
        {
            throw new LogicException('Dola Application cannot be initialized again');
        }
        self::$_instance = $this;

        $this->printRequestURI();

        //获得module，controller，action
        $this->initRequest($request);

        if( !ctype_alnum($this->_module) || !in_array($this->_module, Env::getAllModule())|| 'index' == $this->_controller)
        {
            Env::getLogger()->Info('Illegal Module or Controller---Module:'.$this->_module . " Controller:" . $this->_controller);
            exit(22);
        }

        $module_class_name = $this->_module . '_Module';
        if(class_exists($module_class_name))
        {
            $this->_moduleInstance = new $module_class_name ();
        }
        else
        {
            Env::getLogger()->Info('Fatal Load Module ' . $this->_module . ' Cannot Find Class');
            exit(22);
        }

        ini_set("error_log", str_replace("#module#", strtolower($this->_module), EnvConf::$phpLogPath));

        Env::setLogName(ucfirst($this->_module));
        Env::getLogger()->refreshIP(Env::getIP());

        return self::$_instance;
    }


    public function run()
    {
        $this->_bootstrap();
        $controller_name = "App_Controller_" . ucfirst($this->_controller);
        $controller_class = new $controller_name ($this->_action);
        $controller_class->render();
    }

    public function _bootstrap()
    {
        if(!$this->_initialized)
        {
            $module_instance = $this->_moduleInstance;
            $module_instance->initialized();
            $this->_initialized = true;
        }
    }
    private function printRequestURI()
    {
        if (isset($_SERVER['REQUEST_URI']))
        {
            $request_uri = $_SERVER['REQUEST_URI'];
        }
        elseif (isset($_SERVER['QUERY_STRING']))
        {
            $request_uri = $_SERVER['QUERY_STRING'];
        }
        else
        {
            $request_uri = $_SERVER['PHP_SELF'];
        }
        $this->_requestUri = $request_uri;
        Env::getLogger()->Info('RequestLog', $request_uri);
    }

    private function initRequest($request)
    {
        $_request_uri = $_SERVER['REQUEST_URI'];
        $_index_query_string = strpos($_request_uri, '?');
        if(false === $_index_query_string)
        {
            $_path_info = $_request_uri;
        }
        else
        {
            $_path_info = substr($_request_uri, 0, $_index_query_string);
        }
        $_path_info = trim($_path_info, '/');
        $_segment = explode('/', $_path_info);
        $this->_module = (isset ($_segment[0]) && ($_segment[0] != '')) ? $_segment[0] : 'index';
        $this->_controller = (isset ($_segment[1]) && ($_segment[1] != '')) ? $_segment[1] : 'index';
        $this->_action= (isset ($_segment[2]) && ($_segment[2] != '')) ? $_segment[2] : 'index';
    }
}

class Dola_Controller
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
            case 'app' :
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
        case 'app' :
            header('Content-Type: text/javascript; charset=utf-8');
            echo json_encode($this->result);
            break;
        case 'smarty':
            $this->smartyEngine->display($this->tplName);
            break;
        }
    }
}
/* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */
