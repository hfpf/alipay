# alipay
一个简单的支付宝sdk，就是在官方SDK的基础上的简单修改并剥离了Lotus框架的内容，使其支持composer，目前只实现了支付相关的以下5个接口：
* alipay.trade.wap.pay
* alipay.trade.page.pay
* alipay.trade.app.pay
* alipay.trade.query
* alipay.trade.refund
# Installation
安装最新版本
```bash
$ composer require hfpf/alipay
```
# Basic Usage
```php
<?php
use Alipay\AopClient;
use Alipay\BuilderModel\AlipayTradePagePayContentBuilder;
use Alipay\Request\AlipayTradePagePayRequest;

$payRequestBuilder = new AlipayTradePagePayContentBuilder();
            $payRequestBuilder->setBody('body');
            $payRequestBuilder->setSubject('subject');
            $payRequestBuilder->setTotalAmount(1.00);
            $payRequestBuilder->setOutTradeNo('xxxxxxxxxxxxxx');

            $request = new AlipayTradePagePayRequest();
            $request->setNotifyUrl('https://xxxx.com/notify');
            $request->setReturnUrl('https://xxxx.com/return');
            $request->setBizContent($payRequestBuilder->getBizContent());

            $aop = new AopClient();
            $aop->appId = 'app_id';
            $aop->rsaPrivateKey = 'private_key';
            $aop->alipayrsaPublicKey= 'public_key';
            $aop->signType = 'RSA2';
            $aop->logPath = storage_path('logs/alipay.log');
            
            $pay_url = $aop->pageExecute($request,"get");
```

# About
使用app支付的时候，APP支付的sign需要做url encode,所以需要多出以下步骤，然后将参数返回给app:

```php
$result['sign'] = urlencode($result['sign']);
```