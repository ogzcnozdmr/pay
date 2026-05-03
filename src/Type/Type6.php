<?php

namespace Oguzcan\Type;

use Oguzcan\Type;

class Type6 extends Type {
    /**
     * Result Map
     * @var array|string[]
     */
    public array $resultMap = [
        'code' => 'orderId',
        'total' => 'amount',
        'installment' => 'installCount'
    ];
    /**
     * Pay start
     * @return array
     */
    public function start() : array
    {
        // 3D modelinde hash hesaplamasında işlem tipi ve taksit kullanılmıyor
        $pay_hash_data = [
            'paymentModel' => '3D',
            'merchantSafeId' => $this->bankInfo->getSecurityName(),
            'terminalSafeId' => $this->bankInfo->getSecurityPassword(),
            'orderId' => $this->orderInfo->getCode(),
            'lang' => $this->bankInfo->getSettings('lang'),
            'amount' => number_format($this->orderInfo->getTotal(),2,'.',''),
            'currencyCode' => $this->orderInfo->getCurrency(),
            'installCount' => max(1, $this->orderInfo->getInstallment()),
            'okUrl' => $this->urlInfo->getOk(),
            'failUrl' => $this->urlInfo->getFail(),
            'emailAddress' => $this->getMail(),
            //'subMerchantId' => $this->bankInfo->getSecurityStoreKey(),
            'creditCard' => $this->cardInfo->getNumber(),
            'expiredDate' => $this->cardInfo->getExpireMonth().$this->cardInfo->getExpireYear(),
            'cvv' => $this->cardInfo->getCvv(),
            'cardHolderName' => $this->cardInfo->getName(),
            'randomNumber' => __pay_random_number_base16(),
            'requestDateTime' => __pay_date_time()
        ];
        $hash = __pay_param_hash_v2($pay_hash_data, $this->bankInfo->getSecurityClient());
        $data = $pay_hash_data + ['hash' => $hash];
        return [true, '', $this->bankInfo->getApiUrl3d(), $data];
    }
    /**
     * Pay result
     * @param $data
     * @return array
     */
    public function result($data) : array
    {
        echo $this->bankInfo->getApiUrl()."<br>";
        $json = __pay_json_encode($data);
        echo $json."<br>";
        $header = [
            'auth-hash: '.__pay_param_hash_v2($json, $this->bankInfo->getSecurityClient()),
            'Content-Type: application/json',
            'Accept: application/json'
        ];
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->bankInfo->getApiUrl(),
            CURLOPT_POST => TRUE,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_HTTPHEADER => $header,
            CURLOPT_POSTFIELDS => $json
        ]);
        $result = curl_exec($ch);
        curl_close($ch);
        print_r($result);die();
        $response = $result->responseCode == 'VPS-0000';
        $error = $result->responseMessage;
        return [$response, $result, $error];
    }
    /**
     * Control signature
     * @return bool
     */
    public function controlSignature() : bool
    {
        $params = explode('+', $this->request['hashParams']);
        $builder = '';
        foreach($params as $param) {
            $builder .= $this->request[$param];
        }
        $hash = __pay_param_hash_v2($builder, $this->bankInfo->getSecurityClient());
        return $hash == $this->request['hash'];
    }
    /**
     * Control 3d
     * @return array
     */
    public function control3d() : array
    {
        $mdStatus = $this->request['responseCode'];
        $status = $mdStatus == 'VPS-0000';
        $message = $this->request['responseMessage'] ?? '';
        return [$status, $message];
    }
    /**
     * Result data array
     * @return array
     */
    public function resultData() : array
    {
        return [
            'amount' => $this->request['amount'],
            'txnCode' => $this->request['txnCode'],
            'installCount' => $this->request['installCount'],
            'secureId' => $this->request['secureId'],
            'secureData' => $this->request['secureData'],
            'secureMd' => $this->request['secureMd'],
            'secureEcomInd' => $this->request['secureEcomInd'],
        ];
    }
    /**
     * Set payment value
     * @param array $value
     * @return array
     */
    public function setPaymentValue(array $value) : array
    {
        echo "values";
        echo "<pre>";
        print_r($value);
        echo "</pre>";
        return [
            'version' => '1.00',
            'txnCode' => '1000',//$value['txnCode'],
            'requestDateTime' => __pay_date_time(),
            'randomNumber' => __pay_random_number_base16(),
            'terminal' => [
                'merchantSafeId' => $this->bankInfo->getSecurityName(),
                'terminalSafeId' => $this->bankInfo->getSecurityPassword(),
            ],
            'order' => [
                'orderId' => $this->orderInfo->getCode()
            ],
            'transaction' => [
                'amount' => $value['amount'],
                'currencyCode' => $this->orderInfo->getCurrency(),
                'motoInd' => 0,
                'installCount' => (int) $value['installCount']
            ],
            'secureTransaction' => [
                'secureId' => $value['secureId'],
                'secureEcomInd' => $value['secureEcomInd'],
                'secureData' => $value['secureData'],
                'secureMd' => $value['secureMd'],
            ]
        ];
    }
}