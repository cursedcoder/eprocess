<?php

namespace EProcess\Adapter;

use EProcess\Behaviour\UniversalSerializer;
use EProcess\MessengerFactory;
use EProcess\Stream\FullDrainStream;
use React\ChildProcess\Process;
use Symfony\Component\Process\PhpExecutableFinder;

class ChildProcess extends BaseAdapter
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

    private $process;

    public function create($class, array $data = [])
    {
        $executableFinder = new PhpExecutableFinder();

        if (false === $php = $executableFinder->find()) {
            throw new \RuntimeException('Unable to find the PHP executable.');
        }

        $unix = $this->createUnixSocket();
        $messenger = MessengerFactory::server($unix, $this->loop);

        $script = sprintf(
            $this->script,
            EPROCESS_AUTOLOAD,
            $unix,
            $class,
            base64_encode($this->serialize($data))
        );

        $this->process = new Process($php);
        $this->process->start($this->loop, 0.1);

        $this->process->stdin = new FullDrainStream(
            $this->process->stdin->stream,
            $this->loop
        );

        $this->process->stdin->write($script);

        $this->process->stdin->on('full-drain', function() {
            $this->process->stdin->close();
        });

        $this->process->stdout->on('data', function($data) {
            echo $data;
        });

        $this->process->stderr->on('data', function($data) {
            echo $data;
        });

        return $messenger;
    }

    public function kill()
    {
        $this->process->close();
    }
}
