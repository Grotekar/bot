<?php

namespace PHPUnit\Framework;

use Bot\api\VkApi;

class VkTest extends TestCase
{
    protected VkApi $vk;

    /**
     * Начальные данные
     */
    public function setUp(): void
    {
        $this->vk = new VkApi(
            VK_API_ACCESS_TOKEN,
            DESCRIPTION,
            QUESTION_FOR_REPEAT,
            NUMBER_OF_REPETITIONS,
            197571527
        );
    }

    /**
     * Тестирование, обрабатывается ли /help
     */
    public function testCommandHelp(): void
    {
        $response = $this->preparationData('/help');
        $response = json_decode($response);

        $this->assertSame('help', $this->vk->processingResponse($response));
    }

    /**
     * Тестирование, обрабатывается ли /repeat
     */
    public function testCommandRepeat(): void
    {
        $response = $this->preparationData('/repeat');
        $response = json_decode($response);

        $this->assertSame('repeat', $this->vk->processingResponse($response));
    }

    /**
     * Тестирование обычного сообщения
     */
    public function testMessageDefault(): void
    {
        $response = $this->preparationData('Test message');
        $response = json_decode($response);

        $this->assertSame('another', $this->vk->processingResponse($response));
    }

    /**
     * Преобразование к формату присланного сообщению
     *
     * @param $text
     * @return string|string[]
     */
    public function preparationData($text)
    {
        return preg_replace("/text\":\"1\"/", 'text":"' . $text . '"', DATA_FOR_TESTING_VK);
    }
}
