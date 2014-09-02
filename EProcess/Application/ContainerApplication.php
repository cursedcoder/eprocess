<?php

namespace EProcess\Application;

abstract class ContainerApplication extends Application
{
    private $container;

    public function __construct()
    {
        require_once __DIR__ . '/../../app/bootstrap.php.cache';
        require_once __DIR__ . '/../../app/AppKernel.php';

        $kernel = new \AppKernel('test', true);
        $kernel->boot();

        register_shutdown_function(function() {
            if ($error = error_get_last()) {
                var_dump(
                    array_merge($error, [
                        'class' => get_called_class()
                    ])
                );
            }
        });

        $this->container = $kernel->getContainer();
    }

    public function container()
    {
        return $this->container;
    }

    public function get($id)
    {
        return $this->container()->get($id);
    }

    public function param($id)
    {
        return $this->container()->getParameter($id);
    }
}
