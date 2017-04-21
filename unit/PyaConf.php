<?php

/**
 * Created by PhpStorm.
 * User: pro4
 * Date: 2017/4/21
 * Time: 16:13
 */
require "../vendor/autoload.php";
use SwiftPass\Store\PayConf;
class PyaConf extends PHPUnit_Framework_TestCase
{
    public function testPayConfAdd(){
        $obj = new PayConf('103540003211','a22ffc6580b8afce8794e17d1201c50f','xml');
        $data = array();
        $data['mchPayConf'] = [
            'merchantId' => '199500148164',
            'payTypeId' => 223,
            'billRate' => 6
        ];

        $response = $obj->PayConfAdd($data);
        var_dump($response);
    }
}