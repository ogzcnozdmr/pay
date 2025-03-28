<?php

namespace Oguzcan;

class Pay
{
    /**
     * Default mail address
     * @var string
     */
    private string $mail = '';

    /**
     * Request
     * @param Bank $bankInfo
     * @param Order $orderInfo
     * @param Card $cardInfo
     * @param Url $urlInfo
     * @return void
     */
    public function request(Bank $bankInfo, Order $orderInfo, Card $cardInfo, Url $urlInfo) {
        $type_class = implode('\\', ['Oguzcan', 'Type', 'Type'.$bankInfo->getType()]);
        $type_model = new $type_class();
        $type_model->setMail($this->mail);
        return $type_model->__start($bankInfo, $orderInfo, $cardInfo, $urlInfo);
    }

    /**
     * Result
     * @param array $request
     * @param Bank $bankInfo
     * @return object
     */
    public function result(array $request, Bank $bankInfo) : object
    {
        $type_class = implode('\\', ['Oguzcan', 'Type', 'Type'.$bankInfo->getType()]);
        $type_model = new $type_class();
        return $type_model->__result($request, $bankInfo);
    }

    /**
     * Set Mail
     * @param string $value
     * @return void
     */
    public function setMail(string $value) {
        $this->mail =  $value;
    }
}