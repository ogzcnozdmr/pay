<?php

namespace Oguzcan\Type;

use Oguzcan\Type;

class Type2 extends Type {
    /**
     * Result Map
     * @var array|string[]
     */
    public array $resultMap = [
        'code' => 'oid',
        'total' => 'amount',
        'installment' => 'Installment'
    ];
    /**
     * Pay start
     * @return array
     */
    public function start() : array
    {
        $MbrId = $this->bankInfo->getType() === 1 ? '5' : '12';
        $hashstr = $MbrId . $this->orderInfo->getCode() . $this->orderInfo->getTotal() . $this->urlInfo->getOk() . $this->urlInfo->getFail() . $this->bankInfo->getSettings('txnType'). $this->orderInfo->getInstallment() . $this->orderInfo->getRandom()  . $this->bankInfo->getSecurityStoreKey();

        $data = [
            'MbrId' => $MbrId,
            'MerchantID' => $this->bankInfo->getSecurityClient(),
            'UserCode' => $this->bankInfo->getSecurityName(),
            'UserPass' => $this->bankInfo->getSecurityPassword(),
            'SecureType' => $this->bankInfo->getStoreType(),
            'TxnType' => $this->bankInfo->getSettings('txnType'),
            'InstallmentCount' => $this->orderInfo->getInstallment(),
            'Currency' => $this->orderInfo->getCurrency(),
            'OkUrl' => $this->urlInfo->getOk(),
            'FailUrl' => $this->urlInfo->getFail(),
            'OrderId' => $this->orderInfo->getCode(),
            'OrgOrderId' => $this->orderInfo->getCode(),
            'PurchAmount' => $this->orderInfo->getTotal(),
            'Lang' => $this->bankInfo->getSettings('lang'),
            'Rnd' => $this->orderInfo->getRandom(),
            'Hash' => base64_encode(pack('H*', sha1($hashstr))),
            'Pan'=> $this->cardInfo->getNumber(),
            'Cvv2' => $this->cardInfo->getCvv(),
            'Expiry' => $this->cardInfo->getExpireMonth() .'/'. $this->cardInfo->getExpireYear(),
        ];
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
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 90);
        $result = curl_exec($ch);
        curl_close($ch);
        $xml = simplexml_load_string($result);
        $response = $xml->Response == 'Approved';
        $error = isset($xml->ErrMsg) ? (string) $xml->ErrMsg : '';
        return [$response, $xml, $error];
    }
    /**
     * Control signature
     * @return bool
     */
    public function controlSignature() : bool
    {
        $hashparams = $this->request['HASHPARAMS'];
        $hashparamsval = $this->request['HASHPARAMSVAL'];
        $hashparam = $this->request['HASH'];
        $paramsval = '';
        $index1 = 0;
        $index2 = 0;

        while ($index1 < strlen($hashparams)) {
            $index2 = strpos($hashparams, ":", $index1);
            $vl = $this->request[substr($hashparams, $index1, $index2 - $index1)];
            if ($vl == null)
                $vl = '';
            $paramsval = $paramsval . $vl;
            $index1 = $index2 + 1;
        }

        if (!str_starts_with($paramsval, $this->request['clientId'])) {
            $paramsval = $this->request['clientId'].$paramsval;//clientId != clientid
        }

        $hashval = $paramsval.$this->bankInfo->getSecurityStoreKey();
        $hash = base64_encode(pack('H*', sha1($hashval)));

        return $paramsval == $hashparamsval && $hashparam == $hash;
    }
    /**
     * Control 3d
     * @return array
     */
    public function control3d() : array
    {
        $mdStatus = $this->request['mdStatus'];
        $status = in_array($mdStatus, ['1', '2', '3', '4']);
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
            'total'    => $this->request['amount'],
            'clientid' => $this->request['clientid'],
            'expires'  => $this->request['Ecom_Payment_Card_ExpDate_Month'].'/'.$this->request['Ecom_Payment_Card_ExpDate_Year'],
            'cv2'      => $this->request['cv2'],
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
    public function setPaymentValue($value) : string
    {
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
            "<Taksit>{TAKSIT}</Taksit>".
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
        $data = str_replace("{TAKSIT}", $this->orderInfo->getInstallment(), $data);
        $data = str_replace("{EMAIL}", $this->getMail(), $data);
        return $data;
    }
}