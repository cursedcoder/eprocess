<?php

namespace Examples\Simple;

use EProcess\Application\Application;
use Examples\Simple\Model\Transaction;

class Bank extends Application
{
    public function run()
    {
        $this->loop()->addPeriodicTimer(1.5, function() {
            $this->send('transaction', new Transaction(
                ['usd', 'eur'][rand(0, 1)],
                rand(10, 250)
            ));
        });
    }
}
