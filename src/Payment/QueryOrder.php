<?php

namespace Alipay\Payment;

/* 统一收单线下交易查询 */
class QueryOrder extends Order
{
    /**
     * QueryOrder constructor.
     * @param $outTradeNo 商户订单号.
     */
    public function __construct($outTradeNo)
    {
        // 商户订单号.
        $this->bizContentarr['out_trade_no'] = $outTradeNo;
    }

    public function getMethod()
    {
        // 统一收单线下交易查询
        return 'alipay.trade.query';
    }
}