<?php

namespace EProcess\Adapter;

use EProcess\Behaviour\UniversalSerializer;
use Symfony\Component\Process\PhpProcess;
use React\EventLoop\LoopInterface;
use EProcess\MessengerFactory;

class SymfonyProcess
{
    use UniversalSerializer;

    private $script = <<<PHP
<?php

require_once '%s';

set_time_limit(0);

use EProcess\MessengerFactory;
use EProcess\Application\ApplicationFactory;
use React\EventLoop\Factory;

\$loop = Factory::create();

\$messenger = MessengerFactory::client(
    '%s',
    \$loop
);

\$application = ApplicationFactory::create('%s');

\$application->messenger(\$messenger);
\$application->loop(\$loop);
\$application->data(\$application->unserialize(base64_decode('%s')));

\$messenger->emit('initialized', true);

try {
    \$application->run();
    \$loop->run();
} catch (\Exception \$e) {
    echo \$e;
}
PHP;

    private $loop;
    private $process;

    public function __construct(LoopInterface $loop)
    {
        $this->loop = $loop;
    }

    public function create($class, array $data = [])
    {
        $node = uniqid('thread_');
        $unix = sprintf('unix://app/cache/%s.sock', $node);

        $messenger = MessengerFactory::server($unix, $this->loop);

        $script = sprintf($this->script, EPROCESS_AUTOLOAD, $unix, $class, base64_encode($this->serialize($data)));

        $this->process = new PhpProcess($script);
        $this->process->start();

        return $messenger;
    }

    public function kill()
    {
        $this->process->stop();
    }
}
