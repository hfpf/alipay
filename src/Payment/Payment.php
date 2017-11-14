<?php

namespace Alipay\Payment;

use GuzzleHttp\Client;

class Payment
{
    //网关
    public $gatewayUrl = "https://openapi.alipay.com/gateway.do";
    //应用ID
    public $appId;
    //私钥文件路径
    public $rsaPrivateKeyFilePath;
    //支付宝公钥文件路径
    public $rsaPublicKeyFilePath;
    //app私钥
    public $rsaPrivateKey;
    //使用文件读取文件格式，请只传递该值
    public $alipayPublicKey = null;
    //支付宝公钥，使用读取字符串格式，请只传递该值
    public $alipayrsaPublicKey;
    //api版本
    public $apiVersion = "1.0";
    // 表单提交字符集编码
    public $postCharset = "UTF-8";
    //返回数据格式，仅支持JSON
    public $format = "json";
    //签名类型
    public $signType = "RSA2";
    //
    public $notifyUrl = '';

    public $returnUrl = '';

    public function __construct(array $config)
    {
        $this->appId = $config['app_id'];
        $this->rsaPrivateKeyFilePath
            = isset($config['private_key_file_path']) ? $config['private_key_file_path'] : null;
        $this->rsaPublicKeyFilePath
            = isset($config['public_key_file_path']) ? $config['public_key_file_path'] : null;
        $this->rsaPrivateKey = isset($config['private_key']) ? $config['private_key'] : null;
        $this->alipayrsaPublicKey = $config['public_key'] ? $config['public_key'] : null;
        $this->notifyUrl = isset($config['notify_url']) ? $config['notify_url'] : '';
        $this->returnUrl = isset($config['return_url']) ? $config['return_url'] : '';
        $this->postCharset = isset($config['charset']) ? $config['charset'] : 'UTF-8';
        $this->signType = isset($config['sign_type']) ? $config['sign_type'] : 'RSA2';
        $this->gatewayUrl = isset($config['gatewayUrl']) ? $config['gatewayUrl'] : "https://openapi.alipay.com/gateway.do";
    }

    /**
     * 获取订单支付参数
     * @param $order 订单参数信息
     * @param null $returnUrl 如果为null则使用配置文件中指定的默认return_url(因为特
     * 殊场景下支付成功后可能需要跳转的不同的页面)
     * @param null $notifyUrl 如果为null则使用配置文件中指定的默认notify_url
     */
    public function getPayParams(Order $order, $returnUrl = null, $notifyUrl = null)
    {
        //组装系统参数
        $sysParams["app_id"] = $this->appId;
        $sysParams["version"] = $this->apiVersion;
        $sysParams["format"] = $this->format;
        $sysParams["sign_type"] = $this->signType;
        $sysParams["method"] = $order->getMethod();
        $sysParams["timestamp"] = date("Y-m-d H:i:s");
        $sysParams["terminal_type"] = null;
        $sysParams["terminal_info"] = null;
        $sysParams["prod_code"] = null;
        $sysParams["notify_url"] = !empty($notifyUrl) ? $notifyUrl : $this->notifyUrl;
        $sysParams["return_url"] = !empty($returnUrl) ? $returnUrl : $this->returnUrl;
        $sysParams["charset"] = $this->postCharset;
        $sysParams["biz_content"] = $order->getBizContent();
        $sysParams["sign"] = $this->generateSign($sysParams, $this->signType);
        return $sysParams;
    }

    /**
     * 发起退款
     * @param $outTradeNo 商户订单号.
     * @param $refundAmount 退款金额
     * @param $outRequestNo 退款原因
     */
    public function refund($outTradeNo
        , $refundAmount
        , $outRequestNo)
    {

        $order = new RefundOrder($outTradeNo, $refundAmount, $outRequestNo);
        $parameters = $this->getPayParams($order);
        $client = new Client();
        $params = ['form_params' => $parameters];
        $response = $client->request('POST', $this->gatewayUrl, $params);
        $response = $response->getBody();
        return json_decode($response);
    }

    /**
     * 统一收单交易退款
     * @param $outTradeNo 商户订单号
     */
    public function query($outTradeNo)
    {
        $order = new QueryOrder($outTradeNo);
        $parameters = $this->getPayParams($order);
        $client = new Client();
        $params = ['form_params' => $parameters];
        $response = $client->request('POST', $this->gatewayUrl, $params);
        $response = $response->getBody();
        return json_decode($response);
    }

    /** rsaCheckV1 & rsaCheckV2
     *  验证签名
     *  在使用本方法前，必须初始化AopClient且传入公钥参数。
     *  公钥是否是读取字符串还是读取文件，是根据初始化传入的值判断的。
     **/
    public function rsaCheckV1($params, $signType = 'RSA')
    {
        $sign = $params['sign'];
        $params['sign_type'] = null;
        $params['sign'] = null;
        return $this->verify($this->getSignContent($params), $sign, $signType);
    }

    /**
     * 生成待签名字符串
     * @param $params
     * @return string
     */
    function getSignContent($params)
    {
        ksort($params);

        $stringToBeSigned = "";
        $i = 0;
        foreach ($params as $k => $v) {
            if (false === $this->checkEmpty($v) && "@" != substr($v, 0, 1)) {

                if ($i == 0) {
                    $stringToBeSigned .= "$k" . "=" . "$v";
                } else {
                    $stringToBeSigned .= "&" . "$k" . "=" . "$v";
                }
                $i++;
            }
        }

        unset ($k, $v);
        return $stringToBeSigned;
    }

    /**
     * 生成签名
     * @param $params
     * @param string $signType
     * @return string
     */
    function generateSign($params, $signType = "RSA")
    {
        return $this->sign($this->getSignContent($params), $signType);
    }

    function sign($data, $signType = "RSA")
    {
        if ($this->checkEmpty($this->rsaPrivateKeyFilePath)) {
            $priKey = $this->rsaPrivateKey;
            $res = "-----BEGIN RSA PRIVATE KEY-----\n" .
                wordwrap($priKey, 64, "\n", true) .
                "\n-----END RSA PRIVATE KEY-----";
        } else {
            $priKey = file_get_contents($this->rsaPrivateKeyFilePath);
            $res = openssl_get_privatekey($priKey);
        }

        ($res) or die('您使用的私钥格式错误，请检查RSA私钥配置');

        if ("RSA2" == $signType) {
            openssl_sign($data, $sign, $res, OPENSSL_ALGO_SHA256);
        } else {
            openssl_sign($data, $sign, $res);
        }

        if (!$this->checkEmpty($this->rsaPrivateKeyFilePath)) {
            openssl_free_key($res);
        }
        $sign = base64_encode($sign);
        return $sign;
    }

    /**
     * 校验$value是否非空
     *  if not set ,return true;
     *    if is null , return true;
     **/
    function checkEmpty($value)
    {
        if (!isset($value))
            return true;
        if ($value === null)
            return true;
        if (trim($value) === "")
            return true;

        return false;
    }


    function verify($data, $sign, $signType = 'RSA')
    {

        if ($this->checkEmpty($this->alipayPublicKey)) {

            $pubKey = $this->alipayrsaPublicKey;
            $res = "-----BEGIN PUBLIC KEY-----\n" .
                wordwrap($pubKey, 64, "\n", true) .
                "\n-----END PUBLIC KEY-----";
        } else {
            //读取公钥文件
            $pubKey = file_get_contents($this->rsaPublicKeyFilePath);
            //转换为openssl格式密钥
            $res = openssl_get_publickey($pubKey);
        }

        ($res) or die('支付宝RSA公钥错误。请检查公钥文件格式是否正确');

        //调用openssl内置方法验签，返回bool值

        if ("RSA2" == $signType) {
            $result = (bool)openssl_verify($data, base64_decode($sign), $res, OPENSSL_ALGO_SHA256);
        } else {
            $result = (bool)openssl_verify($data, base64_decode($sign), $res);
        }

        if (!$this->checkEmpty($this->alipayPublicKey)) {
            //释放资源
            openssl_free_key($res);
        }

        return $result;
    }
}