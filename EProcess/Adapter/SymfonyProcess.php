<?php

namespace EProcess\Adapter;

use Symfony\Component\Process\PhpProcess;
use EMessenger\MessengerFactory;
use UniversalSerializer\UniversalSerializerTrait;

class SymfonyProcess extends BaseAdapter
{
    use UniversalSerializerTrait;

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

\$messenger->send('initialized', true);

try {
    \$application->run();
    \$loop->run();
} catch (\Exception \$e) {
    echo \$e;
}
PHP;

    private $process;

    public function create($class, array $data = [])
    {
        $transport = $this->createUnixTransport();
        $messenger = MessengerFactory::server($transport);

        $script = sprintf(
            $this->script,
            EPROCESS_AUTOLOAD,
            $transport, $class,
            base64_encode($this->serialize($data))
        );

        $this->process = new PhpProcess($script);
        $this->process->start();

        return $messenger;
    }

    public function kill()
    {
        $this->process->stop();
    }
}
