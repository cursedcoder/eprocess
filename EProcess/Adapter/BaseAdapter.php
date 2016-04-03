<?php

namespace EProcess\Adapter;

use React\EventLoop\LoopInterface;

abstract class BaseAdapter
{
    protected $loop;
    protected $node;

    public function __construct(LoopInterface $loop)
    {
        $this->loop = $loop;
        $this->node = uniqid('thread_');
    }

    protected function createUnixSocket()
    {
        $unixFile = sprintf('tmp/%s.sock', $this->node);
        $unix = sprintf('unix://%s', $unixFile);

        $cleanup = function() use ($unixFile) {
            $this->loop->stop();
            @unlink($unixFile);
        };

        register_shutdown_function($cleanup);
        pcntl_signal(SIGINT, $cleanup);

        return $unix;
    }

    abstract public function create($class, array $data = []);
    abstract public function kill();
}
