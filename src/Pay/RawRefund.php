<?php
/**
 * Created by PhpStorm.
 * User: pro4
 * Date: 2017/3/30
 * Time: 17:34
 */

namespace SwiftPass\Pay;
use Requests;
use Particle\Validator\Validator;
use SwiftPass\Exceptions\WechatException;
use SwiftPass\Library\Utils;
use SwiftPass\Library\Xml;

class RawRefund extends WebPay
{
    const SERVICE_NAME = 'unified.trade.refund';
    protected $debug = false;
    public function __construct($debug)
    {
        $this->debug = $debug;
    }


    /**预支付订单
     * @param $playLoad array 微信支付相关参数
     * @return mixed
     * @throws \Exception
     */
    public function Pay($playLoad)
    {
        $v= new Validator();
        $v->required('mch_id')->string()->lengthBetween(1,32);
        $v->required('out_trade_no')->string()->lengthBetween(1,32);
        $v->required('out_refund_no')->string()->lengthBetween(1,32);
        $v->required('total_fee')->integer();
        $v->required('refund_fee')->integer();
        $v->required('op_user_id')->string()->lengthBetween(1,32);
        $v->required('mchKey')->string();

        $valid = $v->validate($playLoad);
        if(!$valid->isValid()){
            throw  new \Exception(json_encode($valid->getMessages()));
        }

        $metaData = [
            'service' => self::SERVICE_NAME,
            'version' => parent::VERSION,
            'charset' => parent::CHARSET,
            'sign_type' => parent::SIGN_TYPE,
            'mch_id' => $playLoad['mch_id'],
            'sign_agentno' => $playLoad['sign_agentno'],
            'out_trade_no' => $playLoad['out_trade_no'],
            //'transaction_id' => $playLoad['out_trade_no'],
            'out_refund_no' => $playLoad['out_refund_no'],
            'total_fee' => $playLoad['total_fee'],
            'refund_fee' => $playLoad['refund_fee'],
            'op_user_id' => $playLoad['op_user_id'],
            //'refund_channel' => 'ORIGINAL',
            'nonce_str' => Utils::randomString()
        ];

        $metaData['sign'] = parent::Sign($metaData, $playLoad['mchKey']);
        $response = Requests::post($this->Url,[], parent::DataSerialization($metaData));
        return parent::DataDeserialization($response->body);

    }

    public function Notify($notifyXml)
    {
        $xmlObj = new Xml();
        $data = $xmlObj->XmlToArray($notifyXml);
        if($data['status'] > 0){
            throw new \Exception($data['message']);
        }

        if($data['result_code'] > 0){
            throw  new WechatException($data['err_msg'],$data['err_code']);
        }

        return $data;
    }
}