<?php

namespace PHPUnit\Framework;

use Bot\api\TelegramApi;

class TelegramTest extends TestCase
{
    protected TelegramApi $telegram;

    public function setUp(): void
    {
        $this->telegram = new TelegramApi();
    }

    public function testCommandHelp(): void
    {
        $response = $this->preparationData('/help');
        $response = json_decode($response);

        $this->assertSame('help', $this->telegram->processingResponse($response));
    }

    public function testCommandRepeat(): void
    {
        $response = $this->preparationData('/repeat');
        $response = json_decode($response);

        $this->assertSame('repeat', $this->telegram->processingResponse($response));
    }

    public function testMessageDefault(): void
    {
        $response = $this->preparationData('Test message');
        $response = json_decode($response);

        $this->assertSame('another', $this->telegram->processingResponse($response));
    }

    public function preparationData($text)
    {
        return preg_replace("/text\":\"1\"/", 'text":"' . $text . '"', DATA_FOR_TESTING_TELEGRAM);
    }
}
