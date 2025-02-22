<?php

namespace Oguzcan;

class Url
{
    private string $ok;
    private string $fail;
    private string $bank;
    private string $orderCode;
    private int $installment = 0;

    /**
     * Set Ok
     * @param string $value
     * @return string
     */
    public function setOk(string $value) : string
    {
        return $this->ok = $value;
    }

    /**
     * Set Fail
     * @param string $value
     * @return string
     */
    public function setFail(string $value) : string
    {
        return $this->fail = $value;
    }

    /**
     * Set Bank
     * @param string $value
     * @return void
     */
    public function setBank(string $value) : void
    {
        $this->bank = $value;
    }

    /**
     * Set Installment
     * @param int $value
     * @return void
     */
    public function setInstallment(int $value) : void
    {
        $this->installment = $value;
    }

    /**
     * Set Order Code
     * @param string $value
     * @return void
     */
    public function setOrdeCode(string $value) : void
    {
        $this->orderCode = $value;
    }

    /**
     * Get Ok
     * @return string
     */
    public function getOk() : string
    {
        return "{$this->ok}/{$this->bank}";
    }

    /**
     * Get Fail
     * @return string
     */
    public function getFail() : string
    {
        return "{$this->fail}/{$this->bank}";
    }

    /**
     * Get Bank
     * @return string
     */
    public function getBank() : string
    {
        return $this->bank;
    }

    /**
     * Get Installment
     * @return int
     */
    public function getInstallment() : int
    {
        return $this->installment;
    }

    /**
     * Get Order Code
     * @return int
     */
    public function getOrderCode() : int
    {
        return $this->orderCode;
    }
}