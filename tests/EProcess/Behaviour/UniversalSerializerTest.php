<?php

namespace Tests\EProcess\Behaviour;

use EProcess\Behaviour\UniversalSerializer;
use Examples\Simple\Model\Transaction;

class UniversalSerializerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function should_serialize_array()
    {
        $this->assertData(['abcde' => 'dbce']);
    }

    /**
     * @test
     */
    public function should_serialize_scalar()
    {
        $this->assertData('asdasd');
    }

    /**
     * @test
     */
    public function should_serialize_integer()
    {
        $this->assertData(5123123);
    }

    /**
     * @test
     */
    public function should_serialize_object()
    {
        $this->assertData(new Transaction('EUR', 1235));
    }

    /**
     * @test
     */
    public function should_serialize_all()
    {
        $this->assertData([
            'abcde', // scalar
            [1 => 'ok'], // array,
            [2 => ['ok', new Transaction('USD', 555)]] // object
        ]);
    }

    private function assertData($data)
    {
        $serializer = new SomeSerializer();

        $serialized = $serializer->serialize($data);
        $unserialized = $serializer->unserialize($serialized);

        $this->assertEquals($data, $unserialized);
    }
}

class SomeSerializer
{
    use UniversalSerializer;
}

