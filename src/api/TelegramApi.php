<?php

namespace Bot\api;

use Bot\Utils\Logger;
use Psr\Log\LoggerInterface;

/**
 * Описывает методы для использования Telegram Api
 *
 */
class TelegramApi
{
    private string $accessToken;
    private string $description;
    private string $question;
    private int $repetitions;
    private int $updateId;
    private LoggerInterface $logger;

    /**
     * TelegramApi constructor.
     * @param string $token
     * @param string $description
     * @param string $questionForRepeat
     * @param int $numberOfRepetitions
     * @param int $updateId
     */
    public function __construct(
        string $token,
        string $description,
        string $questionForRepeat,
        int $numberOfRepetitions,
        int $updateId
    ) {
        $this->accessToken = $token;
        $this->description = $description;
        $this->question    = $questionForRepeat;
        $this->repetitions = $numberOfRepetitions;
        $this->updateId    = $updateId;
        $this->logger      = new Logger();
    }

    /**
     * Прослучшивание канала на наличие событий.
     *
     * @return mixed
     */
    public function listenEvent()
    {
        do {
            $response = json_decode(file_get_contents('https://api.telegram.org/bot' . $this->accessToken
                  . '/getUpdates?offset=' . $this->updateId));
        } while (count($response->result) === 0);

        $this->logger->info('Получено сообщение');
        return $response;
    }

    /**
     * Пролучение типа запроса (была нажата кнопка или нет)
     * при его наличии.
     *
     * @param $response
     * @return string
     */
    public function typeResponse($response): string
    {
        if (array_key_exists('message', $response->result[0])) {
            return 'not_button_click';
        } elseif (array_key_exists('callback_query', $response->result[0])) {
            return 'button_click';
        } else {
            return 'unknown';
        }
    }

    /**
     * Обработка полученного сообщения от пользователя.
     *
     * @param object $response
     *
     * @return void
     */
    public function processingResponse(object $response): void
    {
        // Разбор полученного ответа
        $requestParams = [
            'chat_id' => $response->result[0]->message->chat->id,
        ];

        // Анализ полученного сообщения
        switch ($response->result[0]->message->text) {
            case '/help':
                $this->sendMessageHelp($requestParams);
                break;
            case '/repeat':
                $this->sendMessageRepeat($requestParams);
                break;
            default:
                $requestParams['text'] = $response->result[0]->message->text;
                $this->sendMessageDefault($requestParams, $this->repetitions);
                break;
        }

        $this->updateId = $response->result[0]->update_id;
        // Увеличить индекс события
        $this->updateId++;
    }

    /**
     * Отправка сообщения в ответ на /help.
     *
     * @param array $params
     *
     * @return void
     */
    public function sendMessageHelp(array $params)
    {
        $params['text']         = $this->description;
        $getParams             = http_build_query($params);

        $answer = json_decode(file_get_contents('https://api.telegram.org/bot' . $this->accessToken
            . '/sendMessage?' . $getParams));

        if (is_null($answer) === true) {
            $this->logger->error('Неверная реакция на /help!');
        } else {
            $this->logger->info('Сообщение отправлено успешно в ответ на /help.');
        }
    }

    /**
     * Отправка сообщения в ответ на /repeat.
     *
     * @param array $params
     *
     * @return void
     */
    public function sendMessageRepeat(array $params)
    {
        $params['text'] = 'Сейчас повторяю ' . $this->repetitions . " раз\n" . QUESTION_FOR_REPEAT;
        $get_params     = http_build_query($params);
        $keyboard       = json_encode(array(
            "inline_keyboard" => array(
                array(
                    array(
                        "text" => "1",
                        "callback_data" => "button1"
                    ),
                    array(
                        "text" => "2",
                        "callback_data" => "button2"
                    ),
                    array(
                        "text" => "3",
                        "callback_data" => "button3"
                    ),
                    array(
                        "text" => "4",
                        "callback_data" => "button4"
                    ),
                    array(
                        "text" => "5",
                        "callback_data" => "button5"
                    )
                )
            )
        ));

        $answer = json_decode(file_get_contents('https://api.telegram.org/bot' . $this->accessToken
            . '/sendMessage?' . $get_params . '&reply_markup=' . $keyboard));

        if (is_null($answer) === true) {
            $this->logger->error('Неверная реакция на /repeat!');
        } else {
            $this->logger->info(
                'Сообщение отправлено успешно в ответ на /repeat.'
            );
        }
    }

    /**
     * Изменение числа повторений
     * @param object $response
     */
    public function editNumberRepetitions(object $response)
    {
        // Получить номер кнопки
        $button = $response->result[0]->callback_query->data;
        $this->repetitions = (int) substr($button, -1);
        $requestParams = [
            'chat_id'   => $response->result[0]->callback_query->message->chat->id,
            'text'      => 'Количество повторений изменено на ' . $this->repetitions,
        ];
        // Отправка сообщения
        $this->sendMessageDefault($requestParams);

        // Отметка, что запрос выполнен
        $callbackQueryId = $response->result[0]->callback_query->id;
        $this->sendMessageCallback($callbackQueryId);

        $this->updateId = $response->result[0]->update_id;
        // Увеличить индекс события
        $this->updateId++;
    }

    /**
     * Отправка сообщения.
     *
     * @param array $params
     * @param int   $repeat
     *
     * @return void
     */
    public function sendMessageDefault($params, $repeat = 1)
    {
        $getParams = http_build_query($params);
        $answer    = '';

        // Вывод сообщения $repeat раз
        for ($i = 0; $i < $repeat; $i++) {
            $answer = json_decode(file_get_contents(
                'https://api.telegram.org/bot' . $this->accessToken
                . '/sendMessage?' . $getParams
            ));
        }

        if (is_null($answer) === true) {
            $this->logger->error('Неверная реакция на присланное сообщение!');
        } else {
            $this->logger->info('Сообщение отправлено успешно.');
        }
    }

    /**
     * Отметка, что запрос выполнен
     *
     * @param $callbackId
     */
    public function sendMessageCallback($callbackId)
    {
        file_get_contents('https://api.telegram.org/bot' . $this->accessToken
            . '/answerCallbackQuery?callback_query_id=' . $callbackId);
    }
}
