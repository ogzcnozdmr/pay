<?php

namespace Oguzcan;

class Order
{
    private string $code;
    private float $total;
    private string $currency = '949';
    private int $installment;
    private string $random;
    private string $ip;
    public function __construct()
    {
        /*
         * Ip adresini ekler
         */
        $this->setIp(__pay_ip());
        $this->setCode($this->randomOrderCode());
        $this->setRandom(microtime());
    }

    /**
     * Set Code
     * @param ?string $value
     * @return void
     */
    public function setCode(?string $value = null)
    {
        $this->code = $value !== null ? str_replace(' ','', trim($value)) : $this->randomOrderCode();
    }

    /**
     * Set Total
     * @param string|float $value
     * @return void
     */
    public function setTotal(string|float $value)
    {
        $this->total = floatval(number_format($value, 2, '.', ''));
    }

    /**
     * Set Card Installment
     * @param mixed $value
     * @return void
     */
    public function setInstallment(mixed $value) : void
    {
        if (gettype($value) !== 'integer') {
            $value = intval($value);
        }
        $this->installment = $value < 2 ? 0 : $value;
    }

    /**
     * Set Pay Currency
     * @param string $value
     * @return void
     */
    public function setCurrency(string $value)
    {
        $this->currency = $value;
    }

    /**
     * Set Random
     * @param ?string $value
     * @return void
     */
    public function setRandom(?string $value = null)
    {
        $this->random = $value ?: microtime();
    }

    /**
     * Set Ip
     * @param ?string $value
     * @return void
     */
    public function setIp(?string $value = null)
    {
        $this->ip = $value ?: __pay_ip();
    }

    /**
     * Get Code
     * @return string
     */
    public function getCode() : string
    {
        return $this->code;
    }

    /**
     * Get Total
     * @return float
     */
    public function getTotal() : float
    {
        return $this->total;
    }

    /**
     * Get Card Installment
     * @return int
     */
    public function getInstallment() : int
    {
        return $this->installment;
    }

    /**
     * Get Currency
     * @return int
     */
    public function getCurrency() : int
    {
        return $this->currency;
    }

    /**
     * Get Random
     * @return string
     */
    public function getRandom() : string
    {
        return $this->random;
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
     * Random Order Code
     * @return string
     */
    private function randomOrderCode() {
        return date('dmYHi') . 'OZI' . rand(10000, 99999);
    }
}