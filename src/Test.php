<?php

namespace Oguzcan;

class Test
{
    public function request() {
        $cardInfo = new Card();
        $cardInfo->setNumber('4444 5555 6666 7777');
        $cardInfo->setExpire('10/24');
        $cardInfo->setCvv('590');
        $cardInfo->setName('Oğuzcan Özdemir');
        $cardInfo->setType(1);

        $bankInfo = new Bank('teb');
        $bankInfo->setSecurityName('test test');
        $bankInfo->setSecurityPassword('password123');
        $bankInfo->setSecurityClient('client test');
        $bankInfo->setSecurityStoreKey('store key test');

        $orderInfo = new Order();
        $orderInfo->setCode();
        $orderInfo->setTotal(156.69);
        $orderInfo->setCurrency('949');
        $orderInfo->setInstallment(4);
        $orderInfo->setRandom();

        $urlInfo = new Url();
        $urlInfo->setOk('ok url link');
        $urlInfo->setFail('fail url link');

        $payClass = new Pay();
        $payClass->request($bankInfo, $orderInfo, $cardInfo, $urlInfo);
    }

    public function result(string $bank, mixed $installment) {
        $bankInfo = new Bank($bank);
        $bankInfo->setSecurityName('test test');
        $bankInfo->setSecurityPassword('password123');
        $bankInfo->setSecurityClient('client test');
        //$bankInfo->setSecurityStoreKey('store key test');

        $payClass = new Pay();
        $payClass->result($bankInfo, $installment);



    }
}