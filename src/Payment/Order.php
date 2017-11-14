<?php
/**
 * Created by PhpStorm.
 * User: huangfu
 * Date: 2017/11/13
 * Time: 16:53
 */

namespace Alipay\Payment;


abstract class Order
{
    protected $bizContent = [];

    public function getBizContent()
    {
        if (!empty($this->bizContent)) {
            return json_encode($this->bizContent, JSON_UNESCAPED_UNICODE);
        }
        return null;
    }

    /**
     * 获取当前订单的接口名称
     * @return mixed
     */
    public abstract function getMethod();
}