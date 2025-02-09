<?php

namespace Oguzcan;

class Pay
{
    /*
     *
     */
    public function request(Bank $bankInfo, Order $orderInfo, Card $cardInfo, Url $urlInfo) {
        $type_class = implode('\\', ['App', 'Classes', 'Type', 'Type'.$bankInfo->getSettings()->type]);
        $type_model = new $type_class();
        $type_model->__start($bankInfo, $orderInfo, $cardInfo, $urlInfo);
    }

    public function result(Bank $bankInfo, mixed $installment) {
        $type_class = implode('\\', ['App', 'Classes', 'Type', 'Type'.$bankInfo->getSettings()->type]);
        $type_model = new $type_class();
        $type_model->__start($bankInfo, $installment);
    }
}