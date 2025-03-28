<?php

namespace Oguzcan\Type;

use Oguzcan\Type;

class Type5 extends Type {
    /**
     * Result Map
     * @var array|string[]
     */
    public array $resultMap = [
        'code' => 'reference_no',
        'total' => 'amount',
        'installment' => 'instalment'
    ];
    /**
     * Pay start
     * @return array
     */
    public function start() : array
    {
        // 3D modelinde hash hesaplamasında işlem tipi ve taksit kullanılmıyor
        $pay_hash_data = [
            'return_url' => $this->urlInfo->getOk(),
            'amount' => $this->orderInfo->getTotal(),
            'reference_no' => $this->orderInfo->getCode(),
            'domain' => $_SERVER['SERVER_NAME'],
            'card_holder' => $this->cardInfo->getName(),
            'pan' => $this->cardInfo->getNumber(),
            'month' => $this->cardInfo->getExpireMonth(),
            'year' => $this->cardInfo->getExpireMonth(),
            'cvc' => $this->cardInfo->getCvv()
        ];
        return [true, '', $this->bankInfo->getApiUrl3d(), $pay_hash_data, ['Authorization: Basic '.$this->bankInfo->getSecurityPassword()]];
    }
    /**
     * Pay result
     * @param $data
     * @return array
     */
    public function result($data) : array
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->bankInfo->getApiUrl());
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, __pay_json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 90);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Basic '.$this->bankInfo->getSecurityPassword()
        ]);
        $result = curl_exec($ch);
        curl_close($ch);
        $xml = __pay_json_decode($result);
        $response = $xml->code === '0';
        $error = $xml->message ?: '';
        return [$response, $xml, $error];
    }
    /**
     * Control signature
     * @return bool
     */
    public function controlSignature() : bool
    {
        return true;
    }
    /**
     * Control 3d
     * @return array
     */
    public function control3d() : array
    {
        $mdStatus = $this->request['mdStatus'];
        $status = $mdStatus == '1';
        $message = $this->request['message'] ?? '';
        return [$status, $message];
    }
    /**
     * Result data array
     * @return array
     */
    public function resultData() : array
    {
        return [
            'token_id'    => $this->request['token_id'],
            'session_id' => $this->request['session_id']
        ];
    }
    /**
     * Set payment value
     * @param array $value
     * @return array
     */
    public function setPaymentValue(array $value) : array
    {
        return $value;
    }
}