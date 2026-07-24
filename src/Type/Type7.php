<?php

namespace Oguzcan\Type;

use Oguzcan\Type;

class Type7 extends Type {
    /**
     * Result Map
     * @var array|string[]
     */
    public array $resultMap = [
        'code' => 'OtherTrxCode',
        'total' => '',
        'installment' => ''
    ];

    /**
     * CodeForHash for callback verification
     * @var string
     */
    private string $codeForHash = '';

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

        $dealerCode = $this->bankInfo->getSecurityName();
        $username = $this->bankInfo->getSecurityClient();
        $password = $this->bankInfo->getSecurityPassword();
        $checkKey = hash('sha256', $dealerCode . 'MK' . $username . 'PD' . $password);

        $expYear = $this->cardInfo->getExpireYear();
        if (strlen($expYear) === 2) {
            $expYear = '20' . $expYear;
        }

        $payload = [
            'PaymentDealerAuthentication' => [
                'DealerCode' => $dealerCode,
                'Username' => $username,
                'Password' => $password,
                'CheckKey' => $checkKey,
            ],
            'PaymentDealerRequest' => [
                'CardHolderFullName' => $this->cardInfo->getName(),
                'CardNumber' => $this->cardInfo->getNumber(),
                'ExpMonth' => str_pad($this->cardInfo->getExpireMonth(), 2, '0', STR_PAD_LEFT),
                'ExpYear' => $expYear,
                'CvcNumber' => (string) $this->cardInfo->getCvv(),
                'Amount' => (float) number_format($this->orderInfo->getTotal(), 2, '.', ''),
                'Currency' => $this->mapCurrency(),
                'InstallmentNumber' => max(1, $this->orderInfo->getInstallment()),
                'ClientIP' => $this->orderInfo->getIp() ?: $this->getIp(),
                'OtherTrxCode' => $this->orderInfo->getCode(),
                'IsPoolPayment' => 0,
                'IsPreAuth' => 0,
                'IsTokenized' => 0,
                'Software' => substr((string) ($this->bankInfo->getSettings('software') ?: 'Pay'), 0, 30),
                'ReturnHash' => 1,
                'RedirectUrl' => $this->urlInfo->getOk(),
                'RedirectType' => 0,
            ],
        ];

        if ($this->getMail() !== '') {
            $payload['PaymentDealerRequest']['BuyerInformation'] = [
                'BuyerFullName' => $this->cardInfo->getName(),
                'BuyerEmail' => $this->getMail(),
            ];
        }
        echo "<pre>";
        print_r($payload);
        echo "</pre>";
        $curlresult = __pay_json_decode($this->curl($this->bankInfo->getApiUrl3d(), $payload));

        if (($curlresult->ResultCode ?? '') === 'Success' && !empty($curlresult->Data->Url)) {
            $success = true;
            $postRequest_url = $curlresult->Data->Url;
            $this->storeCodeForHash(
                $this->orderInfo->getCode(),
                (string) ($curlresult->Data->CodeForHash ?? '')
            );
        } else {
            $error = trim(($curlresult->ResultMessage ?? '') . ' ' . ($curlresult->ResultCode ?? '')) ?: $error;
        }

        return [$success, $error, $postRequest_url, $data];
    }

    /**
     * Pay result — ödeme 3D akışında tamamlanır, ikinci API çağrısı yok
     * @param $data
     * @return array
     */
    public function result($data) : array
    {
        return [true, $this->request, ''];
    }

    /**
     * Control signature
     * @return bool
     */
    public function controlSignature() : bool
    {
        $codeForHash = $this->resolveCodeForHash();
        if ($codeForHash === '') {
            return false;
        }

        $hashValue = $this->request['hashValue'] ?? '';
        $hashT = hash('sha256', $codeForHash . 'T');
        $hashF = hash('sha256', $codeForHash . 'F');

        return $hashValue === $hashT || $hashValue === $hashF;
    }

    /**
     * Control 3d
     * @return array
     */
    public function control3d() : array
    {
        $codeForHash = $this->resolveCodeForHash();
        if ($codeForHash === '') {
            return [false, 'CodeForHash bulunamadı'];
        }

        $hashValue = $this->request['hashValue'] ?? '';
        $hashT = hash('sha256', $codeForHash . 'T');

        if ($hashValue === $hashT) {
            return [true, ''];
        }

        $message = $this->request['resultMessage'] ?? $this->request['resultCode'] ?? '3D doğrulama başarısız';
        return [false, $message];
    }

    /**
     * Result data array
     * @return array
     */
    public function resultData() : array
    {
        return [
            'trxCode' => $this->request['trxCode'] ?? '',
            'OtherTrxCode' => $this->request['OtherTrxCode'] ?? '',
            'resultCode' => $this->request['resultCode'] ?? '',
            'resultMessage' => $this->request['resultMessage'] ?? '',
            'hashValue' => $this->request['hashValue'] ?? '',
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

    /**
     * JSON POST
     * @param string $url
     * @param array $data
     * @return string|bool
     */
    private function curl(string $url, array $data)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 90);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, __pay_json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json',
        ]);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    /**
     * Order currency → Moka currency (TL/USD/EUR/GBP)
     * @return string
     */
    private function mapCurrency() : string
    {
        $currency = strtoupper((string) $this->orderInfo->getCurrency());
        return match ($currency) {
            '949', 'TL', 'TRY' => 'TL',
            '840', 'USD' => 'USD',
            '978', 'EUR' => 'EUR',
            '826', 'GBP' => 'GBP',
            default => (string) ($this->bankInfo->getSettings('currency') ?: 'TL'),
        };
    }

    /**
     * Resolve CodeForHash from request or temp store
     * @return string
     */
    private function resolveCodeForHash() : string
    {
        if ($this->codeForHash !== '') {
            return $this->codeForHash;
        }

        if (!empty($this->request['codeForHash'])) {
            $this->codeForHash = strtoupper((string) $this->request['codeForHash']);
            return $this->codeForHash;
        }

        $orderCode = (string) ($this->request['OtherTrxCode'] ?? '');
        if ($orderCode === '') {
            return '';
        }

        $path = $this->codeForHashPath($orderCode);
        if (!is_file($path)) {
            return '';
        }

        $this->codeForHash = strtoupper(trim((string) file_get_contents($path)));
        @unlink($path);

        return $this->codeForHash;
    }

    /**
     * Persist CodeForHash for callback verification
     * @param string $orderCode
     * @param string $codeForHash
     * @return void
     */
    private function storeCodeForHash(string $orderCode, string $codeForHash) : void
    {
        if ($orderCode === '' || $codeForHash === '') {
            return;
        }
        file_put_contents($this->codeForHashPath($orderCode), strtoupper($codeForHash));
    }

    /**
     * Temp file path for CodeForHash
     * @param string $orderCode
     * @return string
     */
    private function codeForHashPath(string $orderCode) : string
    {
        return sys_get_temp_dir() . '/moka_pay_' . md5($orderCode) . '.hash';
    }
}
