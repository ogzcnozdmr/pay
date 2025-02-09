<?php

namespace Oguzcan\Type;

use Oguzcan\Type;

class Type3 extends Type {
    /**
     * Pay start
     * @return array
     */
    public function start() : array
    {
        /*
         * Tarih veya her seferinde değişen bir değer, güvenlik amaçlı
         */
        $microtime = microtime();

        $MbrId = $this->bankInfo->getType() === 2 ? '12' : '5';
        $TxnType = 'Auth';
        $Lang = 'TR';
        $hashstr = $MbrId . $this->orderCode . $this->orderTotal . $this->urlInfo->getOk() . $this->urlInfo->getFail() . $TxnType. $this->cardInfo->getInstallment() . $microtime  . $this->bankInfo->getSecurityStoreKey();
        $hash = base64_encode(pack('H*', sha1($hashstr)));

        $postRequest_url = $this->bankInfo->getApiUrl3d();
        $data = [
            'MbrId' => $MbrId,
            'MerchantID' => $this->bankInfo->getSecurityClient(),
            'UserCode' => $this->bankInfo->getSecurityName(),
            'SecureType' => $this->bankInfo->getStoreType(),
            'TxnType' => $TxnType,
            'InstallmentCount' => $this->cardInfo->getInstallment(),
            'Currency' => $this->currency,
            'OkUrl' => $this->urlInfo->getOk(),
            'FailUrl' => $this->urlInfo->getFail(),
            'OrderId' => $this->orderCode,
            'OrgOrderId' => '',
            'PurchAmount' => $this->orderTotal,
            'Lang' => $Lang,
            'Rnd' => $microtime,
            'Hash' => $hash,
            'CardHolderName'=> $this->cardInfo->getName(),
            'Pan'=> $this->cardInfo->getNumber(),
            'Cvv2' => $this->cardInfo->getCvv(),
            'Expiry' => $this->cardInfo->getExpireYear().$this->cardInfo->getExpireMonth()
        ];
        return [true, '', $postRequest_url, $data];
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
     * Result order code
     * @return string
     */
    public function resultOrderCode() : string
    {
        return $this->request['OrderId'];
    }
    /**
     * Control 3d
     * @return array
     */
    public function control3d() : array
    {
        $mdStatus = $this->request['3DStatus'];
        $status = $mdStatus == "1";
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