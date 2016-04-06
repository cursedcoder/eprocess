<?php

namespace EProcess\Application;

use EProcess\Behaviour\UniversalSerializer;
use EProcess\Behaviour\Workable;
use EProcess\Message;
use EProcess\Messenger;
use EProcess\Worker;
use Evenement\EventEmitterTrait;
use MKraemer\ReactPCNTL\PCNTL;
use React\EventLoop\LoopInterface;

abstract class Application
{
    use EventEmitterTrait {
        EventEmitterTrait::emit as emitterEmit;
    }
    use UniversalSerializer;
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
            $worker->emit('shutdown');
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
                $this->emitterEmit($message->getEvent(), [$message->getContent()]);
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

    public function emit($event, $data = '')
    {
        $this->messenger->emit($event, $data);
    }

    abstract public function run();
}
