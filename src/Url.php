<?php

namespace Oguzcan;

class Url
{
    private string $ok;
    private string $fail;
    private string $bank;
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
     * @return string
     */
    public function setBank(string $value) : string
    {
        return $this->bank = $value;
    }

    /**
     * Set Installment
     * @param int $value
     * @return int
     */
    public function setInstallment(int $value) : int
    {
        return $this->installment = $value;
    }

    /**
     * Get Ok
     * @return string
     */
    public function getOk() : string
    {
        return "{$this->ok}/{$this->bank}/{$this->installment}&type=ok";
    }

    /**
     * Get Fail
     * @return string
     */
    public function getFail() : string
    {
        return "{$this->fail}/{$this->bank}/{$this->installment}&type=fail";
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
}