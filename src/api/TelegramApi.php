<?php

namespace Bot\api;

require_once __DIR__ . '/../../config.php';

class TelegramApi
{
    protected $accessToken = TELEGRAM_API_ACCESS_TOKEN;

    public function getDescription()
    {
        return DESCRIPTION;
    }

    public function getQuestion()
    {
        return QUESTION_FOR_REPEAT;
    }

    public function getNumberRepetition()
    {
        return NUMBER_OF_REPETITIONS;
    }

    public function listenEvent($update_id)
    {
        $response = json_decode(file_get_contents('https://api.telegram.org/bot' . $this->accessToken
                  . '/getUpdates?offset=' . $update_id));
        return $response;
    }

    public function typeResponse($response): string
    {
        if (count($response->result) !== 0 && array_key_exists('message', $response->result[0])) {
            return 'not_button_click';
        } elseif (count($response->result) !== 0 && array_key_exists('callback_query', $response->result[0])) {
            return 'button_click';
        } else {
            return 'unknown';
        }
    }

    public function sendMessage($params, $keyboard = null)
    {
        $get_params = http_build_query($params);
        $answer     = json_decode(file_get_contents('https://api.telegram.org/bot' . $this->accessToken
            . '/sendMessage?' . $get_params . '&reply_markup=' . $keyboard));
    }

    public function sendMessageCallback($callback_id)
    {
        $answer     = json_decode(file_get_contents('https://api.telegram.org/bot' . $this->accessToken
            . '/answerCallbackQuery?callback_query_id=' . $callback_id));
    }
}