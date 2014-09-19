<?php
/***************************************************************************
 * 
 * Copyright (c) Dola , Inc. All Rights Reserved
 * 
 **************************************************************************/


/**
 * @file: ${FILE_NAME}.
 * @author: tjuwhs@gmail.com
 * @date: 14-9-17 下午10:21
 * @brief:
 */

class App_Response
{
    public function pack($status, $code, $data)
    {
        $res = array();
        $res['status'] = $status ? 1: 0;
        $res['code'] = $code;
        $res['data'] = $data;
        return $res;
    }
}