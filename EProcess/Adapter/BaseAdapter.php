<?php

namespace EProcess\Adapter;

use EMessenger\Transport\UnixTransport;
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

    public function getUnixSocketFile()
    {
        return sprintf('%s/%s.sock', EPROCESS_SOCKET_DIR, $this->node);
    }

    public function getUnixSocketAddress()
    {
        return sprintf('unix://%s', $this->getUnixSocketFile());
    }

    protected function createUnixTransport()
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

        return new UnixTransport($this->loop, $this->getUnixSocketAddress());
    }

    abstract public function create($class, array $data = []);

    abstract public function kill();
}
