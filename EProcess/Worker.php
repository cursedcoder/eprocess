<?php

namespace EProcess;

use EProcess\Adapter\ChildProcess;
use EProcess\Adapter\PThreads;
use EProcess\Application\Application;

use Evenement\EventEmitterTrait;
use React\EventLoop\LoopInterface;
use React\EventLoop\Timer\Timer;

class Worker
{
    use EventEmitterTrait {
        EventEmitterTrait::emit as emitterEmit;
    }

    private $loop;
    private $adapter;
    private $messenger;
    private $initialized = false;

    public function __construct(LoopInterface $loop, $class, $adapter = null, array $data = [])
    {
        if (!is_subclass_of($class, Application::class)) {
            throw new \InvalidArgumentException('Should be a subclass of Application');
        }

        $this->loop = $loop;
        $this->adapter = $this->createAdapter($adapter);
        $this->messenger = $this->adapter->create($class, $data);

        $this->messenger->on('message', function(Message $message) {
            $this->emitterEmit($message->getEvent(), [$message->getContent()]);
        });

        $this->messenger()->on('initialized', function() {
            $this->initialized = true;
        });

        register_shutdown_function(function() {
            $this->kill();
        });
    }

    public function kill()
    {
        $this->adapter->kill();
    }

    public function messenger()
    {
        return $this->messenger;
    }

    public function emit($event, $data)
    {
        if ($this->initialized) {
            $this->messenger->emit($event, $data);
        } else {
            $this->loop->addPeriodicTimer(0.1, function(Timer $timer) use ($event, $data) {
                if ($this->initialized) {
                    $this->messenger->emit($event, $data);
                    $timer->cancel();
                }
            });
        }
    }

    public function createAdapter($name)
    {
        return 'pthreads' !== $name
            ? new ChildProcess($this->loop)
            : new PThreads($this->loop)
        ;
    }
}
