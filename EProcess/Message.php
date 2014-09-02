<?php

namespace EProcess;

class Message implements \Serializable
{
    private $event;
    private $content;

    public function __construct($event, $content)
    {
        $this->event = $event;
        $this->content = $content;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function getEvent()
    {
        return $this->event;
    }

    public function serialize()
    {
        return json_encode([
            'event' => $this->event,
            'content' => base64_encode($this->content)
        ]);
    }

    public function unserialize($data)
    {
        $data = json_decode($data, true);

        $this->event = $data['event'];
        $this->content = base64_decode($data['content']);
    }

    public function __toString()
    {
        return $this->serialize();
    }
}
