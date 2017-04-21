<?php
namespace SwiftPass\Store;
use Requests;
use SwiftPass\Library\Utils;
use Particle\Validator\Validator;
abstract class Store{
    protected $partner;
    protected $dataType;
    protected $charset;
    protected $security_key;
    protected $serviceName ;

    const URL = 'https://interface.swiftpass.cn/sppay-interface-war/gateway';

    public function __construct($partner,$security_key,$dataType = 'json',$charset = 'UTF-8')
    {
      $this->partner = $partner;
      $this->dataType = $dataType;
      $this->charset = $charset;
      $this->security_key = $security_key;
    }

    protected function Request($serviceName,$metaData){
        $this->serviceName = $serviceName;
        $data = $this->DataSerialization($metaData);
        $postData = $this->GetPostData($data);
        $response =  $this->Submit($postData);
        return $this->DataDeserialization($response);
    }

    /** 根据不同的dataType 转换要发送的的格式（xml or json）
     * @param $postData
     * @return mixed|string
     */
    protected function DataSerialization($postData){
        if($this->dataType == 'json'){
            $data = json_encode($postData,JSON_UNESCAPED_UNICODE);
        }else{
            $data = Utils::partner_array2xml($postData, $level = 1);
        }
        return $data;
    }


    protected function DataDeserialization($response){
        if($this->dataType == 'json'){
            $data = json_decode($response,true);
        }else{
            $data = Utils::xmlToArray($response);
        }
        return $data;
    }


    /** 组装最终的请求data（已经做了sign签名）
     * @param $data
     * @return array
     * @throws \Exception
     */
    protected function GetPostData($data){
        $v = new Validator();
        $v->required('serviceName')->string();
        $v->required('partner')->string();
        $v->required('dataType')->string()->inArray(['json','xml']);
        $v->required('charset')->string();
        $v->required('data')->string();
        $metaData = [
            'partner' => $this->partner,
            'serviceName' => $this->serviceName,
            'dataType' => $this->dataType,
            'charset' => $this->charset,
            'data' => $data
        ];

        $valid = $v->validate($metaData);
        if(!$valid->isValid()){
            throw  new \Exception(json_encode($valid->getMessages()));
        }
        $sign = Utils::partner_signing($metaData,$this->security_key);
        $metaData['dataSign'] = $sign;
        return $metaData;
    }

    /** 发送请求到接口
     * @param $data
     * @return string
     * @throws \HttpException
     */
    protected function Submit($data){
        $response = Requests::post(self::URL,[
            'Content-Type'=>'application/x-www-form-urlencoded;charset=UTF-8'
        ],$data);
        if(!$response->success)
            throw  new \HttpException('http request error');
        return $response->body;
    }
}