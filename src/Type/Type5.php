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
        $success = false;
        $error = 'İşlem Başarısız';
        $postRequest_url = '';
        $data = [];

        $curldata = // 3D modelinde hash hesaplamasında işlem tipi ve taksit kullanılmıyor
        [
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

        //TODO:CURL YERİNE REQUEST KULLAN
        $curlresult = __pay_json_decode($this->curl($this->bankInfo->getApiUrl3d(), $curldata));
        print_r($curlresult);
        /*
         * Başarılı
         */
        if ($curlresult->code === 0) {
            echo "success girdi";
            $success = true;
            $postRequest_url = $curlresult->post_url;
            $data = [
                'token_id' => $curlresult->token_id,
                'session_id' => $curlresult->session_id,
            ];
        } else {
            $error = $curlresult->message ?: '';
            echo "başarısız girdi $error";
        }
        return [$success, $error, $postRequest_url, $data];
    }
    /**
     * Pay result
     * @param $data
     * @return array
     */
    public function result($data) : array
    {
        $xml = __pay_json_decode($this->curl($this->bankInfo->getApiUrl(), $data));
        $response = isset($xml->session_id) && isset($xml->token_id) && isset($xml->reference_no);
        $error = $xml->bank_error_short_desc ?: 'İşlem başarısız';
        return [$response, $xml, $error];
    }

    private function curl($url, $data) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 90);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, __pay_json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Basic '.$this->bankInfo->getSecurityPassword(),
            'Content-Type: application/json'
        ]);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
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
        $status = isset($this->request['session_id']) && isset($this->request['token_id']) && isset($this->request['reference_no']);
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