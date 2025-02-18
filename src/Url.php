<?php

namespace Oguzcan;

class Url
{
    private string $ok;
    private string $fail;
    private string $bank;
    private string $order;
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
     * Set Installment
     * @param string $value
     * @return void
     */
    public function setOrder(string $value) : void
    {
        $this->order = $value;
    }

    /**
     * Get Ok
     * @return string
     */
    public function getOk() : string
    {
        //return "{$this->ok}?paybank={$this->bank}&payorder={$this->order}&payinstallment={$this->installment}&type=ok";
        return $this->ok;
    }

    /**
     * Get Fail
     * @return string
     */
    public function getFail() : string
    {
        //return "{$this->fail}?paybank={$this->bank}&payorder={$this->order}&payinstallment={$this->installment}&type=fail";
        return $this->fail;
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