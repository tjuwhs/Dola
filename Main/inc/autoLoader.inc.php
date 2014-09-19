<?php
/***************************************************************************
 * 
 * Copyright (c) Dola , Inc. All Rights Reserved
 * 
 **************************************************************************/


/**
 * @file: ${FILE_NAME}.
 * @author: tjuwhs@gmail.com
 * @date: 14-9-15 下午9:33
 * @brief:
 */

function _Auto_Loader($class_name)
{
    if(class_exists($class_name, false))
    {
        return;
    }

    $class_arr = explode('_', $class_name);
    $class_name = str_replace('_', '/', $class_name);

    if('Dola' == $class_arr[0])
    {
        $real_class_file = ROOT . "/../Lib/". $class_arr[1] . ".php";
    }
    else
    {
        $real_class_file = ROOT . "/../" . $class_name . ".php";
    }
    if(file_exists($real_class_file))
    {
        include $real_class_file;
    }
}

spl_autoload_register('_Auto_Loader');