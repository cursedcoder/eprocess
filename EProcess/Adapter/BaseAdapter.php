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
        if (!defined('EPROCESS_SOCKET_DIR')) {
            throw new \RuntimeException('EPROCESS_SOCKET_DIR is not defined.');
        }

        if (!defined('EPROCESS_AUTOLOAD')) {
            throw new \RuntimeException('EPROCESS_AUTOLOAD is not defined.');
        }

        if (!is_writable(EPROCESS_SOCKET_DIR)) {
            throw new \RuntimeException(sprintf('Cannot write to "%s".', EPROCESS_SOCKET_DIR));
        }

        $unixFile = sprintf('%s/%s.sock', EPROCESS_SOCKET_DIR, $this->node);
        $unix = sprintf('unix://%s', $unixFile);

        $cleanup = function () use ($unixFile) {
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
