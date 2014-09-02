<?php

namespace EProcess\Behaviour;

use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\SerializerBuilder;

trait UniversalSerializer
{
    protected $serializationFormat = 'json';
    protected $sharedSerializer;

    public function serialize($data)
    {
        $pack = [];

        if ($data instanceof ArrayCollection) {
            $data = $data->toArray();
        } elseif (!is_array($data)) {
            $data = [$data];
        }

        foreach ($data as $key => $piece) {
            switch (gettype($piece)) {
                case 'object':
                    $pack[$key] = serialize([
                        'class' => get_class($piece),
                        'data' => $this->findSerializer()->serialize($piece, $this->serializationFormat)
                    ]);
                    break;

                case 'array':
                    $pack[$key] = $this->serialize($piece);
                    break;

                default:
                    $pack[$key] = serialize($piece);
                    break;
            }
        }

        return serialize($pack);
    }

    public function unserialize($data)
    {
        $unpack = [];
        $data = is_array($data) ? $data : unserialize($data);

        foreach ($data as $key => $piece) {
            $piece = unserialize($piece);

            switch (gettype($piece)) {
                case 'array':
                    if (isset($piece['class'])) {
                        $unpack[$key] = $this->findSerializer()->deserialize($piece['data'], $piece['class'], $this->serializationFormat);

                        if (is_a($piece['class'], ArrayCollection::class, true)) {
                            $unpack[$key] = new ArrayCollection($unpack[$key]);
                        }
                    } else {
                        $unpack[$key] = $this->unserialize($piece);
                    }

                    break;

                default:
                    $unpack[$key] = $piece;
                    break;
            }
        }

        return 1 === count($unpack) ? current($unpack) : $unpack;
    }

    public function findSerializer()
    {
        if ($this->sharedSerializer) {
            return $this->sharedSerializer;
        } elseif (isset($this->serializer)) {
            return $this->serializer;
        } elseif (method_exists($this, 'serializer')) {
            return $this->serializer();
        } else {
            $this->sharedSerializer = SerializerBuilder::create()->build();

            return $this->sharedSerializer;
        }
    }
}
