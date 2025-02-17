<?php

namespace Oguzcan;

class Pay
{
    /**
     * Request
     * @param Bank $bankInfo
     * @param Order $orderInfo
     * @param Card $cardInfo
     * @param Url $urlInfo
     * @return void
     */
    public function request(Bank $bankInfo, Order $orderInfo, Card $cardInfo, Url $urlInfo) {
        $type_class = implode('\\', ['Oguzcan', 'Type', 'Type'.$bankInfo->getSettings()->type]);
        $type_model = new $type_class();
        $type_model->__start($bankInfo, $orderInfo, $cardInfo, $urlInfo);
    }

    /**
     * Result
     * @param array $request
     * @param Bank $bankInfo
     * @param mixed $installment
     * @return void
     */
    public function result(array $request, Bank $bankInfo, mixed $installment = '') {
        $type_class = implode('\\', ['Oguzcan', 'Type', 'Type'.$bankInfo->getSettings()->type]);
        $type_model = new $type_class();
        $type_model->__start($request, $bankInfo, $installment);
    }
}