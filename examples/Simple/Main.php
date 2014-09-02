<?php

namespace Examples\Simple;

use EProcess\Application\Application;
use Examples\Simple\Model\Transaction;

class Main extends Application
{
    private $crawler;
    private $someThing;
    private $bank;

    public function run()
    {
        $this->crawler = $this->createWorker(Crawler::class);
        $this->crawler->emit('crawl', 'http://google.com/');
        $this->crawler->on('result', function($data) {
            echo '[Crawler] Got new result: ' . strlen($data) . ' chars' . PHP_EOL;
        });

        $this->someThing = $this->createWorker(SomeThing::class);
        $this->someThing->on('status', function($data) {
            echo '[SomeThing] ' . $data . PHP_EOL;
        });

        $this->bank = $this->createWorker(Bank::class);
        $this->bank->on('transaction', function(Transaction $transaction) {
            echo sprintf(
                '[Bank] Got new transaction %d of %s' . PHP_EOL,
                $transaction->getBalance(),
                $transaction->getCurrency()
            );
        });
    }
}
