<?php

namespace EProcess\Behaviour;

use EProcess\Worker;

trait Workable
{
    public function createWorker($fqcn, array $data = [])
    {
        return new Worker($this->loop(), $fqcn, extension_loaded('pthreads') ? 'pthreads' : 'child_process', $data);
    }

    abstract public function loop();
}
