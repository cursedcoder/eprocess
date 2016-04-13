<?php

namespace Examples\Simple;

use EProcess\Application\Application;

class SomeThing extends Application
{
    public function run()
    {
        $this->loop()->addPeriodicTimer(2.5, function() {
            $this->send('status', 'I am here too!');
        });
    }
}
