<?php

namespace EProcess\Behaviour;

use EProcess\Worker;

trait Workable
{
    public function createWorker($fqcn, array $data = [])
    {
        $worker = new Worker(
            $this->loop(),
            $fqcn,
            extension_loaded('pthreads') ? 'pthreads' : 'child_process',
            $data
        );

        $this->emitterEmit('worker.created', [$worker]);

        return $worker;
    }

    abstract public function loop();
}
