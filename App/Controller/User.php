<?php
/***************************************************************************
 * 
 * Copyright (c) Dola , Inc. All Rights Reserved
 * 
 **************************************************************************/


/**
 * @file: ${FILE_NAME}.
 * @author: tjuwhs@gmail.com
 * @date: 14-9-17 下午8:24
 * @brief:
 */

class App_Controller_User extends Dola_Controller
{
    protected $action = '';

    protected $actionMap = Array(
        'Login' => 'App_Actions_User_Login',
        'Register' => 'App_Actions_User_Register',
    );

    protected $renderMap = Array(
        'Login' => 'app',
        'Register' => 'app',
    );

    public function __construct($action = null)
    {
        $this->action = $action;
    }
}