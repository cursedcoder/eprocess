<?php

require_once __DIR__ . '/autoload.php';

use Doctrine\Common\Annotations\AnnotationRegistry;
use EProcess\Application\ApplicationFactory;
use Examples\Simple\Main;

AnnotationRegistry::registerLoader(array($loader, 'loadClass'));

/**
 * We are using namespaces structure to let external autoloader know where to find things.
 */
ApplicationFactory::launch(Main::class);
