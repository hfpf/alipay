<?php

namespace Alipay\Payment;


class WebOrder extends Order
{
    /**
     * WebOrder constructor.
     * @param $outTradeNo 商户订单号.
     * @param $totalAmount 订单总金额，整形，此处单位为元，精确到小数点后2位，不能超过1亿元
     * @param $subject 订单标题，粗略描述用户的支付目的。
     * @param $body 订单描述，可以对交易或商品进行一个详细地描述，比如填写"购买商品2件共15.00元"
     * @param $passback_params 公用回传参数，如果请求时传递了该参数，则返回给商户时会回传该参数。支付宝只会
     * 在异步通知时将该参数原样返回。本参数必须进行UrlEncode之后才可以发送给支付宝
     */
    public function __construct($outTradeNo, $totalAmount, $subject, $body, $passback_params)
    {
        // 商户订单号.
        $this->bizContent['out_trade_no'] = $outTradeNo;
        // 订单总金额，整形，此处单位为元，精确到小数点后2位，不能超过1亿元
        $this->bizContent['total_amount'] = $totalAmount;
        // 订单标题，粗略描述用户的支付目的。
        $this->bizContent['subject'] = $subject;
        // 订单描述，可以对交易或商品进行一个详细地描述，比如填写"购买商品2件共15.00元"
        $this->bizContent['body'] = $body;
        // (推荐使用，相对时间) 该笔订单允许的最晚付款时间，逾期将关闭交易。取值范围：1m～15d。m-分钟，h-小时，d-天，1c-当天（1c-当天的情况下，无论交易何时创建，都在0点关闭）。 该参数数值不接受小数点， 如 1.5h，可转换为 90m
        $this->bizContent['timeout_express'] = '1c';
        // 产品标示码，固定值：FAST_INSTANT_TRADE_PAY
        $this->bizContent['product_code'] = "FAST_INSTANT_TRADE_PAY";
        /* 公用回传参数，如果请求时传递了该参数，则返回给商户时会回传该参数。支付宝只会
            在异步通知时将该参数原样返回。本参数必须进行UrlEncode之后才可以发送给支付宝 */
        $this->bizContent['passback_params'] = json_encode($passback_params);
    }

    public function getMethod()
    {
        return 'alipay.trade.page.pay';
    }
}