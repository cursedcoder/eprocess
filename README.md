EProcess
========

The idea is to have multiple non-blocking contexts with a transparent inter-process communication out of the box.

Use at your own risk.

Check out examples in `examples/` dir.

No tests – no problems.

Features
========
* 3 adapters: child process (react), pthreads, symfony process (not tested)
* inter-process communication between childs-parent using unix sockets
* simple serialization for objects(jms serializer), arrays, scalars
* async events driven flow (react eventloop)
* integration with frameworks, see `EProcess\Application\ContainerApplication` for Symfony
* child workers can have own child workers (i.e. `main -> worker -> worker ...`)

Install&try
===========
* git clone https://github.com/cursedcoder/eprocess
* cd eprocess
* composer install
* php examples/simple.php

Example explains features
=========================

```php
use EProcess\Application\Application;

class Data
{
  // jms serializer metadata
  private $id;
  // setters getters etc.
}

class Main extends Application // like that one in c++
{
    public function run()
    {
        $worker = $this->createWorker(MyWorker::class); // create external non-blocking thread of MyWorker class
        $worker->event('any_event', 'Hello my worker!');
        $worker->on('hello_master', function() {
            // Receive back-call from child
        });
    }
}

class MyWorker extends Application
{
    public function run()
    {
        $this->on('any_event', function($data) {
            echo 'Got any_event event from my master: ' . $data; // data == Hello my worker
            // Still we can send any event back to master
            $this->event('hello_master');
            $this->event('send-any-data', new Data()); // you can send any object, array or scalar
            // object should have jms serializer metadata to be serialized
        });
    }
}
```

You need to have proper autoloading established in order to use this example.
