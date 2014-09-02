<?php

namespace Examples\Simple;

use EProcess\Application\Application;

class Crawler extends Application
{
    public function run()
    {
        $this->on('crawl', [$this, 'crawl']);
    }

    public function crawl($url)
    {
        $crawl = function() use ($url) {
            $data = file_get_contents($url);
            $this->emit('result', $data);
        };

        $this->loop()->addPeriodicTimer(5, $crawl);
        $crawl();
    }
}
