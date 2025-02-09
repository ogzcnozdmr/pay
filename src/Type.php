<?php

namespace Oguzcan;

class Type
{
    /**
     * @var Card Card Info
     */
    public Card $cardInfo;
    /**
     * @var Bank Bank Info
     */
    public Bank $bankInfo;
    /**
     * @var Url Url Info
     */
    public Url $urlInfo;
    /**
     * @var Order Order Info
     */
    public Order $orderInfo;
    /**
     * İp address
     * @var string
     */
    private string $ip;
    /**
     * Default mail address
     * @var string
     */
    private string $mail = '';
    protected string $request;
    /**
     * Construct
     */
    public function __construct()
    {
        /*
         * Ip adresini ekler
         */
        $this->ip = $_SERVER['HTTP_CLIENT_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'];
    }
    /**
     * Start Pay
     */
    public function __start(Bank $bankInfo, Order $orderInfo, Card $cardInfo, Url $urlInfo)
    {
        $this->bankInfo = $bankInfo;
        $this->orderInfo = $orderInfo;
        $this->cardInfo = $cardInfo;
        $this->urlInfo = $urlInfo;

        $this->urlInfo->setBank($bankInfo->getKey());
        $this->urlInfo->setInstallment($orderInfo->getInstallment());

        /*
         * Siparişi oluşturur
         */
        $this->orderInsertEvent();

        list($status, $error, $postRequest_url, $data) = $this->start();

        if (!$status) {
            if ($error !== '') {
                $error = "($error)";
            }
            return $this->paymentFinish([
                "result" => false,
                "message" => "İşlem onay almadı {$error}"
            ]);
        }
        echo $this->postRequest($postRequest_url, $data);
    }

    /**
     * Result Pay
     * @param Bank $bankInfo
     * @param int $installment
     * @return array
     */
    public function __result(Bank $bankInfo, mixed $installment) : array
    {
        $this->bankInfo = $bankInfo;

        $this->orderInfo = new Order();
        $this->orderInfo->setCode($this->resultOrderCode());
        $this->orderInfo->setInstallment($installment);

        /*$this->logsClass->__create([
            'logs_url' => request()->server('REQUEST_URI'),
            'logs_variables' => __encrypt(__pay_json_encode($this->request), $this->orderCode.$this->bankInfo->getKey()),
            'logs_bank' => $this->bankInfo->getKey(),
            'logs_installment' => $this->orderInfo->getInstallment(),
            'logs_code' => $this->orderInfo->getCode()
        ]);*/

        /**
         * Digital Signature Control
         */
        if (!$this->controlSignature()) {
            return $this->paymentFinish([
                "result"  => false,
                "message" => 'Güvenlik uyarısı. Sayısal imza geçerli değil.'
            ]);
        }

        /**
         * Digital 3D control
         */
        list($control3dResult, $control3dMessage) = $this->control3d();

        if (!$control3dResult) {
            if ($control3dMessage !== '') {
                $control3dMessage = "($control3dMessage)";
            }
            return $this->paymentFinish([
                "result" => false,
                "message" => "3D işlemi onay almadı {$control3dMessage}"
            ]);
        }

        $resultData = $this->resultData();
        /*
         * Ödemeyi apiye yollayacak değerler ayarlanır
         */
        $paymentData = $this->setPaymentValue($resultData);

        list($result, $data, $error) = $this->result($paymentData);

        $message = $result ? 'Ödeme işlemi başarıyla gerçekleştirildi' : $error;

        /*
         * Sonuçların veritabanından güncellenmesi
         */
        /*$this->updateEvent([
            "pay_json"    => __pay_json_encode($data, true),
            "pay_date"    => date('Y-m-d H:i:s'),
            "pay_result"  => $result ? 'success' : 'error',
            "pay_message" => $message
        ]);*/

        return $this->paymentFinish([
            'result' => $result,
            'message' => $message,
            'data' => $data
        ]);
    }

    /**
     * Payment finish die
     * @param array $json
     * @return array
     */
    protected function paymentFinish(array $json) : array
    {
        /**
        * İnsert database history
        */
        //$this->insertHistory($json['result'] ? 'success' : 'error', $json['message']);
        return $json;
    }

    /**
     * Override start function
     * @return array
     */
    protected function start() : array
    {
        return [true, '', '', []];
    }

    /**
     * Override result function
     * @param string|array $data
     * @return array
     */
    protected function result(string|array $data) : array
    {
        return [true, '', ''];
    }

    /**
     * Override result order code function
     * @return string
     */
    protected function resultOrderCode() : string
    {
        return '';
    }

    /**
     * Override control signature
     * @return bool
     */
    protected function controlSignature() : bool
    {
        return true;
    }

    /**
     * Override control 3d
     * @return array
     */
    protected function control3d() : array
    {
        return [true, ''];
    }

    /**
     * Override result data array
     * @return array
     */
    protected function resultData() : array
    {
        return [];
    }

    /**
     * Override set payment value data
     * @param array $value
     * @return string
     */
    protected function setPaymentValue(array $value) : string
    {
        return '';
    }

    /**
     * Insert database
     * @return void
     */
    private function orderInsertEvent() : void
    {
        /**
         * İnsert database history
         */
        $this->insertHistory('process', 'Ödeme bekleniyor');
    }

    /**
     * Update database history
     * @param string $result
     * @param string $message
     * @return void
     */
    private function insertHistory(string $result, string $message) : void
    {
        $this->history[] = [
            'order_number' => $this->orderInfo->getCode(),
            'pay_history_result' => $result,
            'pay_history_message' => $message
        ];
    }

    /**
     * Set Mail
     * @param string $value
     * @return void
     */
    public function setMail(string $value) {
        $this->mail =  $value;
    }

    /**
     * Get Ip
     * @return string
     */
    public function getIp() : string
    {
        return $this->ip;
    }

    /**
     * Get Mail
     * @return string
     */
    public function getMail() : string
    {
        return $this->mail;
    }

    /**
     * Post data and url redirect
     * @param string $url
     * @param array $params
     * @return false|string
     */
    private function postRequest(string $url, array $params) {
        $this->logs[] = [
            'url' => request()->server('REQUEST_URI'),
            'variables' => __encrypt(__pay_json_encode(request()->all()), $this->orderInfo->getCode().$this->bankInfo->getKey()),
            'bank' => 'post request',
            'installment' => __encrypt(__pay_json_encode($params), $this->orderInfo->getCode().$this->bankInfo->getKey()),
            'code' => $this->orderInfo->getCode()
        ];
        $query_content = http_build_query($params);
        $fp = fopen($url, 'r', FALSE, // do not use_include_path
            stream_context_create([
                'http' => [
                    'header'  => [ // header array does not need '\r\n'
                        'Content-type: application/x-www-form-urlencoded',
                        'Content-Length: ' . strlen($query_content)
                    ],
                    'method'  => 'POST',
                    'content' => $query_content
                ]
            ])
        );
        if ($fp === FALSE) {
            return __pay_json_encode(['error' => 'Failed to get contents...']);
        }
        $result = stream_get_contents($fp); // no maxlength/offset
        fclose($fp);
        return $result;
    }
}