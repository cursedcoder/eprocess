<?php

namespace EProcess\Adapter;

use EProcess\Behaviour\UniversalSerializer;
use EProcess\MessengerFactory;

use React\ChildProcess\Process;
use React\EventLoop\LoopInterface;
use Symfony\Component\Process\PhpExecutableFinder;

class ChildProcess
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
    private $executableFinder;

    public function __construct(LoopInterface $loop)
    {
        $this->loop = $loop;
        $this->executableFinder = new PhpExecutableFinder();
    }

    public function create($class, array $data = [])
    {
        if (false === $php = $this->executableFinder->find()) {
            throw new \RuntimeException('Unable to find the PHP executable.');
        }

        $node = uniqid('thread_');
        $unix = sprintf('unix://tmp/%s.sock', $node);

        $messenger = MessengerFactory::server($unix, $this->loop);

        $file = sprintf(__DIR__ . '/../../tmp/%s.php', $node);

        file_put_contents($file, sprintf(
            $this->script,
            EPROCESS_AUTOLOAD,
            $unix,
            $class,
            base64_encode($this->serialize($data))
        ));

        $this->process = new Process(sprintf('exec %s %s', $php, realpath($file)));
        $this->process->start($this->loop);

        $this->loop->addTimer(5, function() use ($file) {
            unlink($file);
        });

        $this->process->stdout->on('data', function($data) {
            echo $data;
        });

        $this->process->stderr->on('data', function($data) {
            echo $data;
        });

        register_shutdown_function(function() use ($unix) {
            unlink($unix);
        });

        return $messenger;
    }

    public function kill()
    {
        $this->process->close();
    }
}
