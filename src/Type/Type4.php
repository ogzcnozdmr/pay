<?php

namespace Oguzcan\Type;

use Oguzcan\Type;

class Type4 extends Type {
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

        $curldata = [
            'MerchantId' => $this->bankInfo->getSecurityName(),
            'MerchantPassword' => $this->bankInfo->getSecurityPassword(),
            'VerifyEnrollmentRequestId' => $this->orderInfo->getCode(),
            'Pan'=> $this->cardInfo->getNumber(),
            'ExpiryDate' => $this->cardInfo->getExpireYear().$this->cardInfo->getExpireMonth(),
            'PurchaseAmount' => number_format($this->orderInfo->getTotal(),2,'.','.'),
            'Currency' => $this->orderInfo->getCurrency(),
            'BrandName' => $this->cardInfo->getType() === 1 ? '100' : '200', //1 visa 2 mastercard
            'SuccessUrl' => $this->urlInfo->getOk(),
            'FailureUrl' => $this->urlInfo->getFail(),
            'SessionInfo' => $this->cardInfo->getCvv()
        ];

        if ($this->orderInfo->getInstallment() > 1) {
            $curldata['InstallmentCount'] = $this->orderInfo->getInstallment();
        }
        //TODO:CURL YERİNE REQUEST KULLAN
        $curlresult = $this->curl($curldata);
        if (isset($curlresult->Message->VERes->Status)) {
            /*
             * Başarılı
             */
            if ($curlresult->Message->VERes->Status == 'Y') {
                $success = true;
                $postRequest_url = (string) $curlresult->Message->VERes->ACSUrl;
                $data = [
                    'PaReq' => (string) $curlresult->Message->VERes->PaReq,
                    'TermUrl' => (string) $curlresult->Message->VERes->TermUrl,
                    'MD' => (string) $curlresult->Message->VERes->MD
                ];
            } else {
                $error = (string) $curlresult->ErrorMessage;
            }
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
        $result = $this->curl($data, 'result');
        $xml = simplexml_load_string($result);
        $response = (string) $xml->ResultCode === '0000';
        $error = isset($xml->ResultDetail) ? (string) $xml->ResultDetail : '';
        return [$response, $xml, $error];
    }
    /**
     * Result order code
     * @return string
     */
    public function resultOrderCode() : string
    {
        return $this->request['VerifyEnrollmentRequestId'];
    }
    /**
     * Control 3d
     * @return array
     */
    public function control3d() : array
    {
        $mdStatus = $this->request['Status'];
        $status = in_array($mdStatus, ['Y']);
        $message = '';
        return [$status, $message];
    }
    /**
     * Result data array
     * @return array
     */
    public function resultData() : array
    {
        return [
            'total' => number_format($this->request['PurchAmount'],2,'.','.'),//123456.67
            'pan' => $this->request['Pan'],
            'expiry' => '20'.$this->request['Expiry'],
            'cvv' => $this->request['SessionInfo'],
            'currencyamount' => $this->request['PurchAmount'],
            'transactionid' => $this->request['VerifyEnrollmentRequestId'],
            'cavv' => $this->request['Cavv'],
            'eci' => $this->request['Eci']
        ];
    }
    /**
     * Set payment value
     * @param array $value
     * @return string
     */
    public function setPaymentValue(array $value) : string
    {
        $data =
            "<?xml version=\"1.0\" encoding=\"ISO-8859-9\"?>".
            "<VposRequest>".
            "<MerchantId>{NAME}</MerchantId>".
            "<Password>{PASSWORD}</Password>".
            "<TerminalNo>{CLIENTID}</TerminalNo>".
            "<TransactionType>{TRANSACTIONTYPE}</TransactionType>";
        if ($this->orderInfo->getInstallment() > 1) {
            $data .= "<NumberOfInstallments>".$this->orderInfo->getInstallment()."</NumberOfInstallments>";
        }
        $data .= "<CurrencyAmount>{TOTAL}</CurrencyAmount>".
            "<CurrencyCode>{CURRENCYCODE}</CurrencyCode>".
            "<Pan>{PAN}</Pan>".
            "<Cvv>{CVV}</Cvv>".
            "<ECI>{ECI}</ECI>".
            "<CAVV>{CAVV}</CAVV>".
            "<MpiTransactionId>{MPITRANSACTIONID}</MpiTransactionId>".
            "<Expiry>{EXPIRY}</Expiry>".
            "<TransactionDeviceSource>0</TransactionDeviceSource>".
            "<ClientIp>{IP}</ClientIp>".
            "</VposRequest>";
        $data = str_replace("{NAME}", $this->bankInfo->getSecurityName(), $data);
        $data = str_replace("{PASSWORD}", $this->bankInfo->getSecurityPassword(), $data);
        $data = str_replace("{CLIENTID}", $this->bankInfo->getSecurityClient(), $data);
        $data = str_replace("{TRANSACTIONTYPE}", $this->bankInfo->getSettings()->transactionType, $data);
        $data = str_replace("{TOTAL}", $value['total'], $data);
        $data = str_replace("{CURRENCYCODE}", $this->orderInfo->getCurrency(), $data);
        $data = str_replace("{PAN}", $value['pan'], $data);
        $data = str_replace("{CVV}", $value['cvv'], $data);
        $data = str_replace("{ECI}", $value['eci'], $data);
        $data = str_replace("{CAVV}", $value['cavv'], $data);
        $data = str_replace("{MPITRANSACTIONID}", $value['transactionid'], $data);
        $data = str_replace("{EXPIRY}", $value['expiry'], $data);
        $data = str_replace("{IP}", $this->getIp(), $data);
        return $data;
    }

    private function curl($data, $type = 'start') {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 90);
        if ($type === 'start') {
            curl_setopt($ch, CURLOPT_URL, $this->bankInfo->getApiUrl3d());
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        } else {
            curl_setopt($ch, CURLOPT_URL, $this->bankInfo->getApiUrl());
            curl_setopt($ch, CURLOPT_POSTFIELDS, "prmstr=" . $data);
        }
        $result = curl_exec($ch);
        if ($type === 'start') {
            $result = simplexml_load_string($result);
        }
        curl_close($ch);
        return $result;
    }
}