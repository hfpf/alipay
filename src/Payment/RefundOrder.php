<?php

namespace Alipay\Payment;


class RefundOrder extends Order
{
    /**
     * RefundOrder constructor.
     * @param $outTradeNo 商户订单号.
     * @param $refundAmount 退款金额
     * @param $outRequestNo 退款原因
     * @param $refundReason 退款请求号
     */
    public function __construct($outTradeNo
        , $refundAmount
        , $outRequestNo)
    {
        // 商户订单号.
        $this->bizContent['out_trade_no'] = $outTradeNo;
        // 退款金额
        $this->bizContent['refund_amount'] = $refundAmount;
        // 退款请求号
        $this->bizContent['out_request_no'] = $outRequestNo;
    }

    public function getMethod()
    {
        return 'alipay.trade.refund';
    }
}