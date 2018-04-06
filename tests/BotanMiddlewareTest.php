<?php

namespace Tests;

use BotMan\BotMan\Http\Curl;
use PHPUnit_Framework_TestCase;
use BotMan\Middleware\Botan\Botan;
use BotMan\BotMan\Messages\Incoming\IncomingMessage;
use BotMan\Middleware\Botan\Exceptions\BotanException;

class BotanMiddlewareTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @throws BotanException
     */
    public function it_track_test_handler()
    {
        $botan = new Botan('demo', new Curl());
        $message = new IncomingMessage('Hello', 'sender_id', 'recipient_id');
        $botan->track($message, 'test_handler');
    }

    /**
     * @test
     * @expectedException \BotMan\Middleware\Botan\Exceptions\BotanException
     * @expectedExceptionMessage Botan token is empty
     */
    public function it_return_empty_token_exception()
    {
        new Botan('', new Curl());
    }
}