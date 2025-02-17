<?php

namespace Oguzcan;

class Order
{
    private string $code;
    private float $total;
    private string $currency = '949';
    private int $installment;
    private string $random;
    public function __construct()
    {
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
     * @param float $value
     * @return void
     */
    public function setTotal(float $value)
    {
        echo "total geldi - ".$value;
        $this->total = floatval(number_format($value, 2, '.', ''));
        echo " - total çıktı - ".$this->total;
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
     * Get Code
     * @return string
     */
    public function getCode() : string
    {
        return $this->code;
    }

    /**
     * Get Total
     * @return int
     */
    public function getTotal() : int
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
     * Random Order Code
     * @return string
     */
    private function randomOrderCode() {
        return date('dmYHi') . 'OZI' . rand(10000, 99999);
    }
}