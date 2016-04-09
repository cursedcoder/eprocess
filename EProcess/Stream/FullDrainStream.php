<?php

namespace EProcess\Stream;

use React\Stream\Stream;
use React\EventLoop\LoopInterface;

class FullDrainStream extends Stream
{
    public function __construct($stream, LoopInterface $loop)
    {
        parent::__construct($stream, $loop);

        $this->buffer->on('full-drain', function () {
            $this->emit('full-drain', array($this));
        });
    }
}