```php
use EProcess\Application\Application;
use EProcess\Application\ApplicationFactory;

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
        $worker->send('any_event', 'Hello my worker!');
        $worker->subscribe('hello_master', function() {
            // Receive back-call from child
        });
        
        // tracker 
        $worker->getTracker()->getMemoryUsage();
        
        // logger
        $worker->getLogger()->getLogs();
        
        // remote worker
        $remoteWorker = $this->connectWorker();
    }
}

class MyWorker extends Application
{
    public function run()
    {
        $this->subscribe('any_event', function($data) {
            echo 'Got any_event event from my master: ' . $data; // data == Hello my worker
            // Still we can send any event back to master
            $this->send('hello_master');
            $this->send('send-any-data', new Data()); // you can send any object, array or scalar
            // object should have jms serializer metadata to be serialized
        });
        
        $this->getSubscribedEvents();
    }
}

ApplicationFactory::launch(Main::class);
```