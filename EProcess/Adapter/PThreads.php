<?php

namespace EProcess\Adapter;

use EProcess\MessengerFactory;

class PThreads extends BaseAdapter
{
    private $process;

    public function create($class, array $data = [])
    {
        $unix = $this->createUnixSocket();
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
