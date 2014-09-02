<?php

namespace EProcess\Application;

use React\EventLoop\Factory;

class ApplicationFactory
{
    public static function launch($fqcn)
    {
        $application = static::create($fqcn);

        try {
            $application->run();
            $application->loop()->run();
        } catch (\Exception $e) {
            echo $e;
        }
    }

    public static function create($fqcn)
    {
        if (!is_subclass_of($fqcn, Application::class)) {
            throw new \InvalidArgumentException('Should be a subclass of Application');
        }

        $loop = Factory::create();

        $application = new $fqcn();
        $application->loop($loop);

        return $application;
    }
}
