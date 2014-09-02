<?php

namespace Examples\Simple\Model;

use JMS\Serializer\Annotation as S;

class Transaction
{
    /**
     * @S\Type("string")
     */
    private $currency;

    /**
     * @S\Type("integer")
     */
    private $balance;

    public function __construct($currency, $balance)
    {
        $this->currency = $currency;
        $this->balance = $balance;
    }

    public function getCurrency()
    {
        return $this->currency;
    }

    public function getBalance()
    {
        return $this->balance;
    }
}
