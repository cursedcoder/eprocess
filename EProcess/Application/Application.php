<?php

namespace EProcess\Application;

use EMessenger\Message;
use EMessenger\Messenger;
use EProcess\Behaviour\Workable;
use EProcess\Worker;
use Evenement\EventEmitterTrait;
use MKraemer\ReactPCNTL\PCNTL;
use React\EventLoop\LoopInterface;
use UniversalSerializer\UniversalSerializerTrait;

abstract class Application
{
    use EventEmitterTrait;
    use UniversalSerializerTrait;
    use Workable;

    private $loop;
    private $messenger;
    private $data;
    private $pcntl;
    private $workers = [];

    public function addWorker(Worker $worker)
    {
        $this->workers[] = $worker;
    }

    public function cleanWorkers()
    {
        foreach ($this->workers as $worker) {
            $worker->send('shutdown');
            unlink($worker->adapter()->getUnixSocketFile());
        }
    }

    public function pcntl(PCNTL $pcntl = null)
    {
        if ($pcntl) {
            $this->pcntl = $pcntl;
        }

        return $this->pcntl;
    }

    public function loop(LoopInterface $loop = null)
    {
        if ($loop) {
            $this->loop = $loop;
        }

        return $this->loop;
    }

    public function messenger(Messenger $messenger = null)
    {
        if ($messenger) {
            $messenger->on('message', function (Message $message) {
                $this->emit($message->getEvent(), [$message->getContent()]);
            });

            $this->messenger = $messenger;
        }

        return $this->messenger;
    }

    public function data(array $data = null)
    {
        if ($data) {
            $this->data = $data;
        }

        return $this->data;
    }

    public function send($event, $data = '')
    {
        $this->messenger->send($event, $data);
    }

    abstract public function run();
}
