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
        if(!defined('SOCKET_PATH'))
            throw new \Exception("SOCKET_PATH is not defined.");
        
        if(!is_writable(SOCKET_PATH)){
            if(!mkdir(SOCKET_PATH))
                throw new \Exception("Cannot create folder at SOCKET_PATH.");
        }

        $unixFile = sprintf('%s/%s.sock', SOCKET_PATH, $this->node);
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
