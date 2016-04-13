<?php

namespace EProcess\Adapter;

use EProcess\Application\ApplicationFactory;
use EProcess\MessengerFactory;
use React\EventLoop\Factory;

class Thread extends \Thread
{
    private $class;
    private $unix;
    private $data;

    public function __construct($unix, $class, array $data)
    {
        $this->unix = $unix;
        $this->class = $class;
        $this->data = $data;
    }

    public function run()
    {
        require_once EPROCESS_AUTOLOAD;

        $loop = Factory::create();

        $messenger = MessengerFactory::client($this->unix, $loop);
        $application = ApplicationFactory::create($this->class);

        $application->messenger($messenger);
        $application->loop($loop);
        $application->data($this->data);

        $messenger->send('initialized', true);

        try {
            $application->run();
            $loop->run();
        } catch (\Exception $e) {
            echo $e;
        }
    }
}
