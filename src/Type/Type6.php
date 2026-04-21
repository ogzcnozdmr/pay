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
        'installment' => 'Instalment'
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
            'subMerchantId' => $this->bankInfo->getSecurityStoreKey(),
            'creditCard' => $this->cardInfo->getNumber(),
            'expiredDate' => $this->cardInfo->getExpireMonth().$this->cardInfo->getExpireYear(),
            'cvv' => $this->cardInfo->getCvv(),
            'cardHolderName' => $this->cardInfo->getName(),
            'randomNumber' => __pay_random_number_base16(),
            'requestDateTime' => __pay_date_time()
        ];
        echo "security = ".$this->bankInfo->getSecurityClient();
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
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->bankInfo->getApiUrl());
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "DATA={$data}");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 90);
        $result = curl_exec($ch);
        curl_close($ch);
        $xml = simplexml_load_string($result);
        $response = $xml->Response == 'Approved';
        $error = isset($xml->ErrMsg) ? (string) $xml->ErrMsg : '';
        switch ($error) {
            case 'Hata detayi icin HOSTMSG alanina bakin.':
                if (isset($xml->Extra) && !empty($xml->Extra->HOSTMSG)) {
                    $error = $xml->Extra->HOSTMSG;
                }
                break;
            case 'Gecersiz tutar.':
            case 'Gecersiz Transaction.':
                if (isset($xml->Extra) && !empty($xml->Extra->TEBACIKLAMA)) {
                    $error = $xml->Extra->TEBACIKLAMA;
                }
                break;
        }
        return [$response, $xml, $error];
    }
    /**
     * Control signature
     * @return bool
     */
    public function controlSignature() : bool
    {
        $hash = __pay_param_hash_v2($this->request, $this->bankInfo->getSecurityClient());
        return $hash == $this->request['hash'];
    }
    /**
     * Control 3d
     * @return array
     */
    public function control3d() : array
    {
        $mdStatus = $this->request['responseCode'];
        $status = $mdStatus == "VPS-0000";
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
            'total'    => $this->request['transaction']['amount'],
            'clientid' => $this->request['clientid'],
            'xid'      => $this->request['xid'],
            'eci'      => $this->request['eci'],
            'cavv'     => $this->request['cavv'],
            'md'       => $this->request['md']
        ];
    }
    /**
     * Set payment value
     * @param array $value
     * @return string
     */
    public function setPaymentValue(array $value) : string
    {
        $data = [
            "version" => "1.00",
            "txnCode" => "1000",
            "requestDateTime" => __pay_date_time(),
            "randomNumber" => "**********************************",
            "terminal" => [
                "merchantSafeId" => "********************************",
                "terminalSafeId" => "********************************"
            ],
            "order" => [
                "orderId" => "f17345db-2ea7-4294-a76c-bf0cb64b4ac9"
            ],
            "transaction" => [
                "amount" => "10.00",
                "currencyCode" => 949,
                "motoInd" => 0,
                "installCount" => 1
            ],
            "secureTransaction" => [
                "secureId" => "25be9a5b-8f1b-4948-8e78-071eb9acf62a",
                "secureEcomInd" => "22",
                "secureData" => "ABIBBicAAPYqAAAQAAAAAAAAAAA=",
                "secureMd" => "E36995E27583638978D6E1B766F8EA3E3F51955B98B1D3565D410A76F363D9BD"
            ]
        ];

        $data =
            "<?xml version=\"1.0\" encoding=\"ISO-8859-9\"?>".
            "<CC5Request>".
            "<Name>{NAME}</Name>".
            "<Password>{PASSWORD}</Password>".
            "<ClientId>{CLIENTID}</ClientId>".
            "<IPAddress>{IP}</IPAddress>".
            "<Email>{EMAIL}</Email>".
            "<Mode>{MODE}</Mode>".
            "<OrderId>{OID}</OrderId>".
            "<GroupId></GroupId>".
            "<TransId></TransId>".
            "<UserId></UserId>".
            "<Type>{TYPE}</Type>".
            "<Number>{MD}</Number>".
            "<Expires></Expires>".
            "<Cvv2Val></Cvv2Val>".
            "<Total>{TOTAL}</Total>".
            "<Currency>{CURRENCY}</Currency>".
            "<Taksit>{INSTALLMENT}</Taksit>".
            "<PayerTxnId>{XID}</PayerTxnId>".
            "<PayerSecurityLevel>{ECI}</PayerSecurityLevel>".
            "<PayerAuthenticationCode>{CAVV}</PayerAuthenticationCode>".
            "<CardholderPresentCode>13</CardholderPresentCode>".
            "<BillTo>".
            "<Name></Name>".
            "<Street1></Street1>".
            "<Street2></Street2>".
            "<Street3></Street3>".
            "<City></City>".
            "<StateProv></StateProv>".
            "<PostalCode></PostalCode>".
            "<Country></Country>".
            "<Company></Company>".
            "<TelVoice></TelVoice>".
            "</BillTo>".
            "<ShipTo>".
            "<Name></Name>".
            "<Street1></Street1>".
            "<Street2></Street2>".
            "<Street3></Street3>".
            "<City></City>".
            "<StateProv></StateProv>".
            "<PostalCode></PostalCode>".
            "<Country></Country>".
            "</ShipTo>".
            "<Extra></Extra>".
            "</CC5Request>";
        $data = str_replace("{NAME}", $this->bankInfo->getSecurityName(), $data);
        $data = str_replace("{PASSWORD}", $this->bankInfo->getSecurityPassword(), $data);
        $data = str_replace("{CLIENTID}", $value['clientid'], $data);
        $data = str_replace("{IP}", $this->getIp(), $data);
        $data = str_replace("{OID}", $this->orderInfo->getCode(), $data);
        $data = str_replace("{MODE}", $this->bankInfo->getSettings('mode'), $data);
        $data = str_replace("{TYPE}", $this->bankInfo->getSettings('type'), $data);
        $data = str_replace("{XID}", $value['xid'], $data);
        $data = str_replace("{ECI}", $value['eci'], $data);
        $data = str_replace("{CAVV}", $value['cavv'], $data);
        $data = str_replace("{MD}", $value['md'], $data);
        $data = str_replace("{TOTAL}", $value['total'], $data);
        $data = str_replace("{CURRENCY}", $this->orderInfo->getCurrency(), $data);
        $data = str_replace("{INSTALLMENT}", $this->orderInfo->getInstallment(), $data);
        $data = str_replace("{EMAIL}", $this->getMail(), $data);
        return $data;
    }
}