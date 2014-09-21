<?php

namespace EProcess\Adapter;

use EProcess\MessengerFactory;
use React\EventLoop\LoopInterface;

class PThreads
{
    private $loop;
    private $process;

    public function __construct(LoopInterface $loop)
    {
        $this->loop = $loop;
    }

    public function create($class, array $data = [])
    {
        $node = uniqid('thread_');
        $unix = sprintf('unix://tmp/%s.sock', $node);

        register_shutdown_function(function() use ($unix) {
            unlink($unix);
        });

        $messenger = MessengerFactory::server($unix, $this->loop);

        $this->process = new Thread($unix, $class, $data);
        $this->process->start(PTHREADS_INHERIT_NONE);

        return $messenger;
    }

    public function kill()
    {
        $this->process->kill();
    }
}
