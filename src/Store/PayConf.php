<?php

namespace  SwiftPass\Store;

use Particle\Validator\Validator;

class PayConf extends Store {
    protected $Service = 'normal_mch_pay_conf_add';
    const PAY_CONF = [
        221,  //浦发广州-微信三通道线下扫码
        222,  //浦发广州-微信三通道线下小额
        223,  //浦发广州-微信三通道公众账号
        10000123,  //浦发广州-微信五通道APP
        10000164,  //浦发广州-支付宝三通道服务窗支付
        10000165,  //浦发广州-支付宝三通道扫码支付
        10000166,  //浦发广州-支付宝三通道小额支付
        10000432,  //浦发广州-QQ钱包三通道线下小额
        10000434,  //浦发广州-QQ钱包三通道公众号
        10000433  //浦发广州-QQ钱包三通道线下扫码
    ];


    public function PayConfAdd($MchPayConf){
        $v = new Validator();
        $v->required('mchPayConf.merchantId')->string();
        $v->required('mchPayConf.payTypeId')->inArray(self::PAY_CONF);
        $v->required('mchPayConf.billRate')->integer()->between(1,6);
        $v->optional('mchPayConf.partner')->string();
        $v->optional('mchPayConf.pid')->string();

        $response = $v->validate($MchPayConf);
        if(!$response->isValid()){
            throw new \Exception(json_encode($response->getMessages()));
        }

        return $this->Request($this->Service,$MchPayConf);
    }
}