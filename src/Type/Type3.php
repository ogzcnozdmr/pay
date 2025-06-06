<?php

namespace Oguzcan\Type;

use Oguzcan\Type;

class Type3 extends Type {
    /**
     * Result Map
     * @var array|string[]
     */
    public array $resultMap = [
        'code' => 'OrderId',
        'total' => 'PurchAmount',
        'installment' => 'InstallmentCount'
    ];
    /**
     * Pay start
     * @return array
     */
    public function start() : array
    {
        echo "geldiiii"; die();
        $MbrId = $this->cardInfo->getType() === 1 ? '5' : '12';
        $hashstr = $MbrId . $this->orderInfo->getCode() . $this->orderInfo->getTotal() . $this->urlInfo->getOk() . $this->urlInfo->getFail() . $this->bankInfo->getSettings('txnType'). $this->orderInfo->getInstallment() . $this->orderInfo->getRandom()  . $this->bankInfo->getSecurityStoreKey();

        $data = [
            'MbrId' => $MbrId,
            'MerchantID' => $this->bankInfo->getSecurityClient(),
            'UserCode' => $this->bankInfo->getSecurityName(),
            'SecureType' => $this->bankInfo->getStoreType(),
            'TxnType' => $this->bankInfo->getSettings('txnType'),
            'InstallmentCount' => $this->orderInfo->getInstallment(),
            'Currency' => $this->orderInfo->getCurrency(),
            'OkUrl' => $this->urlInfo->getOk(),
            'FailUrl' => $this->urlInfo->getFail(),
            'OrderId' => $this->orderInfo->getCode(),
            'OrgOrderId' => '',
            'PurchAmount' => $this->orderInfo->getTotal(),
            'Lang' => $this->bankInfo->getSettings('lang'),
            'Rnd' => $this->orderInfo->getRandom(),
            'Hash' => base64_encode(pack('H*', sha1($hashstr))),
            'CardHolderName'=> $this->cardInfo->getName(),
            'Pan'=> $this->cardInfo->getNumber(),
            'Cvv2' => $this->cardInfo->getCvv(),
            'Expiry' => $this->cardInfo->getExpireMonth().$this->cardInfo->getExpireYear()
        ];
        echo "<pre>";
        print_r($data);
        echo "</pre> sonuç ";
        die();
        return [true, '', $this->bankInfo->getApiUrl3d(), $data];
    }
    /**
     * Pay result
     * @param $data
     * @return array
     */
    public function result($data) : array
    {
        $xml = [];
        foreach(explode(';;', $this->curl($data)) as $send) {
            list($key, $value)= explode('=', $send);
            $xml[$key] = $value;
        }
        $response = $xml['ProcReturnCode'] === '00';
        $error = isset($xml['ErrMsg']) ? (string) $xml['ErrMsg'] : '';
        return [$response, $xml, $error];
    }
    /**
     * Control 3d
     * @return array
     */
    public function control3d() : array
    {
        $mdStatus = $this->request['3DStatus'];
        $status = $mdStatus == '1';
        $message = $this->request['ErrMsg'] ?: '';
        return [$status, $message];
    }
    /**
     * Result data array
     * @return array
     */
    public function resultData() : array
    {
        return [
            'RequestGuid' => $this->request['RequestGuid'],
            'OrderId' => $this->request['OrderId'],
            'UserCode' => $this->bankInfo->getSecurityName(),
            'UserPass' => $this->bankInfo->getSecurityPassword(),
            'SecureType' => $this->bankInfo->getStoreType3d()
        ];
    }
    /**
     * Set payment value
     * @param array $value
     * @return string
     */
    public function setPaymentValue($value) : string
    {
        return http_build_query([
            'RequestGuid' => $value['RequestGuid'],
            'OrderId' => $value['OrderId'],
            'UserCode' => $value['UserCode'],
            'UserPass' => $value['UserPass'],
            'SecureType' => $value['SecureType']
        ]);
    }

    private function curl($data) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->bankInfo->getApiUrl());
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 90);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
}