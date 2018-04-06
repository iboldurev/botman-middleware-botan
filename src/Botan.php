<?php

namespace BotMan\Middleware\Botan;

use BotMan\BotMan\BotMan;
use BotMan\BotMan\Interfaces\HttpInterface;
use BotMan\BotMan\Interfaces\MiddlewareInterface;
use BotMan\BotMan\Messages\Incoming\IncomingMessage;
use BotMan\Middleware\Botan\Exceptions\BotanException;

class Botan implements MiddlewareInterface {

    /**
     * Yandex AppMetrica application key
     * @var string
     */
    protected $token;

    /**
     * Botan.io API URL
     * @var string
     */
    protected $apiUrl = 'https://api.botan.io/track?token={token}&uid={recipient}&name={handler}';

    /** @var HttpInterface */
    protected $http;

    /**
     * Botan constructor.
     * @param string $token
     * @param HttpInterface $http
     * @throws BotanException
     */
    public function __construct($token, HttpInterface $http)
    {
        if (empty($token) || !is_string($token)) {
            throw new BotanException('Botan token is empty');
        }

        $this->token = $token;
        $this->http = $http;
    }

    /**
     * @param IncomingMessage $message
     * @param string $handler
     * @throws BotanException
     */
    public function track(IncomingMessage $message, string $handler)
    {
        $result = $this->request(str_replace(
            ['{token}', '{recipient}', '{handler}'],
            [$this->token, $message->getRecipient(), $handler],
            $this->apiUrl
        ));

        if (isset($result->status) && $result->status !== 'accepted') {
            throw new BotanException('Error Processing Request');
        }
    }

    protected function request($url)
    {
        $response = $this->http->post($url, [], [], [
            'Content-Type: application/json; charset=utf-8',
        ], true);

        return json_decode($response->getContent());
    }

    /**
     * Handle a captured message.
     *
     * @param IncomingMessage $message
     * @param callable $next
     * @param BotMan $bot
     *
     * @return mixed
     */
    public function captured(IncomingMessage $message, $next, BotMan $bot)
    {
        return $next($message);
    }

    /**
     * Handle a message that was successfully heard, but not processed yet.
     *
     * @param IncomingMessage $message
     * @param callable $next
     * @param BotMan $bot
     *
     * @return mixed
     */
    public function heard(IncomingMessage $message, $next, BotMan $bot)
    {
        return $next($message);
    }

    /**
     * @param IncomingMessage $message
     * @param string $pattern
     * @param bool $regexMatched Indicator if the regular expression was matched too
     * @return bool
     */
    public function matching(IncomingMessage $message, $pattern, $regexMatched)
    {
        return true;
    }

    /**
     * Handle an incoming message.
     *
     * @param IncomingMessage $message
     * @param callable $next
     * @param BotMan $bot
     *
     * @return mixed
     */
    public function received(IncomingMessage $message, $next, BotMan $bot)
    {
        $this->track($message, 'received');

        return $next($message);
    }

    /**
     * Handle an outgoing message payload before/after it
     * hits the message service.
     *
     * @param mixed $payload
     * @param callable $next
     * @param BotMan $bot
     *
     * @return mixed
     */
    public function sending($payload, $next, BotMan $bot)
    {
        return $next($payload);
    }
}
