<?php

namespace Oguzcan\Type;

use Oguzcan\Type;

class Type1 extends Type {
    /**
     * Pay start
     * @return array
     */
    public function start() : array
    {
        // 3D modelinde hash hesaplamasında işlem tipi ve taksit kullanılmıyor
        $pay_hash_data = [
            'amount' => $this->orderInfo->getTotal(),
            'BillToCompany' => $this->bankInfo->getSettings('billToCompany'),
            'BillToName' => $this->bankInfo->getSettings('billToName'),
            'clientid' => $this->bankInfo->getSecurityClient(),
            'currency' => $this->orderInfo->getCurrency(),
            'cv2' => $this->cardInfo->getCvv(),
            'Ecom_Payment_Card_ExpDate_Month' => $this->cardInfo->getExpireMonth(),
            'Ecom_Payment_Card_ExpDate_Year' => '20'.$this->cardInfo->getExpireYear(),
            'hashAlgorithm' => $this->bankInfo->getSettings('hashAlgorithm'),
            'Instalment' => $this->orderInfo->getInstallment(),
            'lang' => $this->bankInfo->getSettings('lang'),
            'oid' => $this->orderInfo->getCode(),
            'okurl' => $this->urlInfo->getOk(),
            'failUrl' => $this->urlInfo->getFail(),
            'pan' => $this->cardInfo->getNumber(),
            'refreshtime' => $this->bankInfo->getSettings('refreshTime'),
            'rnd' => $this->orderInfo->getRandom(),
            'storetype' => $this->bankInfo->getStoreType(),
            'TranType' => $this->bankInfo->getSettings('tranType')
        ];
        $hash = __pay_param_hash($pay_hash_data, $this->bankInfo->getSecurityStoreKey());
        $data = $pay_hash_data + ['HASH' => $hash];
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
        return [$response, $xml, $error];
    }
    /**
     * Result order code
     * @return string
     */
    public function resultOrderCode() : string
    {
        return $this->request['oid'];
    }
    /**
     * Control signature
     * @return bool
     */
    public function controlSignature() : bool
    {
        $hash = __pay_param_hash($this->request, $this->bankInfo->getSecurityStoreKey());
        return $hash == $this->request['HASH'];
    }
    /**
     * Control 3d
     * @return array
     */
    public function control3d() : array
    {
        $mdStatus = $this->request['mdStatus'];
        $status = $mdStatus == "1" || $mdStatus == "2" || $mdStatus == "3" || $mdStatus == "4";
        $message = $this->request['ErrMsg'] ?? '';
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