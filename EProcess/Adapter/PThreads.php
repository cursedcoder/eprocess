<?php

namespace EProcess\Adapter;

use EMessenger\MessengerFactory;

class PThreads extends BaseAdapter
{
    private $process;

    public function create($class, array $data = [])
    {
        $transport = $this->createUnixTransport();
        $messenger = MessengerFactory::server($transport);

        $this->process = new Thread($transport, $class, $data);
        $this->process->start(PTHREADS_INHERIT_NONE);

        return $messenger;
    }

    public function kill()
    {
        $this->process->kill();
    }
}
