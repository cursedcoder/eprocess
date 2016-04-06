<?php

namespace EProcess;

use Concerto\Comms\ServerInterface;
use EProcess\Behaviour\UniversalSerializer;
use Evenement\EventEmitterTrait;

class Messenger
{
    use EventEmitterTrait {
        EventEmitterTrait::emit as emitterEmit;
    }

    use UniversalSerializer;

    private $connection;

    public function __construct($connection)
    {
        $this->connection = $connection;

        $this->connection->on('message', function($message) {
            $data = json_decode($message, true);
            $message = new Message($data['event'], $this->unserialize(base64_decode($data['content'])));

            $this->emitterEmit('message', [$message]);
            $this->emitterEmit($message->getEvent(), [$message->getContent()]);
        });

        $this->connection->on('close', array($this, 'close'));

        if ($this->connection instanceof ServerInterface) {
            $this->connection->listen();
        } else {
            $this->connection->connect();
        }
    }

    public function emit($event, $data = [])
    {
        $this->connection->send((string) new Message($event, $this->serialize($data)));
    }

    public function close()
    {
        $this->emitterEmit('close');
    }
}
