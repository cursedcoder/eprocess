<?php

namespace EProcess;

use Concerto\Comms\Client;
use Concerto\Comms\Server;
use React\EventLoop\LoopInterface;

class MessengerFactory
{
    public static function server($address, LoopInterface $loop)
    {
        return new Messenger(new Server($loop, $address));
    }

    public static function client($address, LoopInterface $loop)
    {
        return new Messenger(new Client($loop, $address));
    }
}
