<?php

namespace EProcess\Stream;

use React\Stream\Stream;
use React\EventLoop\LoopInterface;
use React\Stream\Buffer;

class FullDrainStream extends Stream
{

    public function __construct($stream, LoopInterface $loop)
    {
        $this->stream = $stream;
        if (!is_resource($this->stream) || get_resource_type($this->stream) !== "stream") {
            throw new \InvalidArgumentException('First parameter must be a valid stream resource');
        }

        stream_set_blocking($this->stream, 0);

        // Use unbuffered read operations on the underlying stream resource.
        // Reading chunks from the stream may otherwise leave unread bytes in
        // PHP's stream buffers which some event loop implementations do not
        // trigger events on (edge triggered).
        // This does not affect the default event loop implementation (level
        // triggered), so we can ignore platforms not supporting this (HHVM).
        if (function_exists('stream_set_read_buffer')) {
            stream_set_read_buffer($this->stream, 0);
        }

        $this->loop = $loop;
        $this->buffer = new Buffer($this->stream, $this->loop);

        $that = $this;

        $this->buffer->on('error', function ($error) use ($that) {
            $that->emit('error', array($error, $that));
            $that->close();
        });

        $this->buffer->on('drain', function () use ($that) {
            $that->emit('drain', array($that));
        });

        $this->buffer->on('full-drain', function () use ($that) {
            $that->emit('full-drain', array($that));
        });

        $this->resume();
    }
}