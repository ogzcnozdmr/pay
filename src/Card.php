<?php

namespace Oguzcan;

class Card
{
    private string $number;
    private int $cvv;
    private int $type;//1 Mastercard - 2 VISA
    private int $expireYear;
    private int $expireMonth;
    private string $name;

    /**
     * Set Card Number
     * @param string $value
     * @return void
     */
    public function setNumber(string $value)
    {
        $this->number = str_replace(' ','', trim($value));
    }

    /**
     * Set Card Cvv
     * @param int $value
     * @return void
     */
    public function setCvv(int $value)
    {
        $this->cvv = $value;
    }

    /**
     * Set Card Type
     * @param int $value
     * 1-VISA | 2-MASTERCAD
     * @return void
     */
    public function setType(int $value)
    {
        $this->type = $value;
    }

    /**
     * Set Card Expire
     * @param string $value
     * @return void
     */
    public function setExpire(string $value)
    {
        $expire = explode('/', $value);
        if (strlen($expire[1]) > 2) {
            $expire[1] = substr($expire[1], -2);
        }
        $this->expireMonth = $expire[0];
        $this->expireYear = $expire[1];
    }

    /**
     * Set Card Name
     * @param string $value
     * @return void
     */
    public function setName(string $value)
    {
        $this->name = trim($value);
    }

    /**
     * Get Card Number
     * @return string
     */
    public function getNumber() : string
    {
        return $this->number;
    }

    /**
     * Get Card Cvv
     * @return int
     */
    public function getCvv() : int
    {
        return $this->cvv;
    }

    /**
     * Get Card Type
     * @return int
     */
    public function getType() : int
    {
        return $this->type;
    }

    /**
     * Get Card Expire Month
     * @return string
     */
    public function getExpireMonth() : string
    {
        return $this->expireMonth;
    }

    /**
     * Get Card Expire Year
     * @return string
     */
    public function getExpireYear() : string
    {
        return $this->expireYear;
    }

    /**
     * Get Card Name
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }
}