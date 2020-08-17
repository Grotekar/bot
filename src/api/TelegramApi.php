<?php

namespace Bot\api;

use Bot\Utils\Logger;
use Dotenv\Dotenv;
use Psr\Log\LoggerInterface;

/**
 * Описывает методы для использования Telegram Api
 */
class TelegramApi
{
    private string $accessToken;
    private string $description;
    private string $question;
    private int $repetitions;
    private int $updateId = -1;
    private LoggerInterface $logger;

    /**
     * TelegramApi constructor.
     */
    public function __construct()
    {
        $dotenv            = Dotenv::createImmutable(__DIR__ . '/../..');
        $dotenv->load();
        $this->accessToken = $_SERVER['TELEGRAM_API_ACCESS_TOKEN'];
        $this->description = $_SERVER['DESCRIPTION'];
        $this->question    = $_SERVER['QUESTION_FOR_REPEAT'];
        $this->repetitions = $_SERVER['NUMBER_OF_REPETITIONS'];
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
                  . '/getUpdates?offset=' . $this->updateId . '&timeout=25'));
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
        if (
            array_key_exists('message', $response->result[0])
            && array_key_exists('text', $response->result[0]->message)
        ) {
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
     * @return string
     */
    public function processingResponse(object $response): string
    {
        // Для получения данных для тестирования раскомментируйте строку
        //file_put_contents('tests\dataForTestTelegram.txt', json_encode($response));
        // Разбор полученного ответа
        $requestParams = [
            'chat_id' => $response->result[0]->message->chat->id,
        ];

        // Анализ полученного сообщения
        switch ($response->result[0]->message->text) {
            case '/help':
                $this->sendMessageHelp($requestParams);
                $word = 'help';
                break;
            case '/repeat':
                $this->sendMessageRepeat($requestParams);
                $word = 'repeat';
                break;
            default:
                $requestParams['text'] = $response->result[0]->message->text;
                $this->sendMessageDefault($requestParams, $this->repetitions);
                $word = 'another';
                break;
        }

        $this->updateId = $response->result[0]->update_id;
        // Увеличить индекс события
        $this->updateId++;
        return $word;
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
        $params['text'] = $this->description;
        $getParams      = http_build_query($params);

        $answer = json_decode(file_get_contents(
            'https://api.telegram.org/bot' . $this->accessToken
            . '/sendMessage?' . $getParams
        ));

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
        $params['text'] = 'Сейчас повторяю '
                        . $this->repetitions . " раз\n" . $this->question;
        $get_params     = http_build_query($params);
        $keyboard       = json_encode([
            "inline_keyboard" => [
                [
                    [
                        "text" => "1",
                        "callback_data" => "button1"
                    ],
                    [
                        "text" => "2",
                        "callback_data" => "button2"
                    ],
                    [
                        "text" => "3",
                        "callback_data" => "button3"
                    ],
                    [
                        "text" => "4",
                        "callback_data" => "button4"
                    ],
                    [
                        "text" => "5",
                        "callback_data" => "button5"
                    ]
                ]
            ]
        ]);

        $answer = json_decode(file_get_contents(
            'https://api.telegram.org/bot' . $this->accessToken
            . '/sendMessage?' . $get_params . '&reply_markup=' . $keyboard
        ));

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
     *
     * @param object $response
     *
     * @return void
     */
    public function editNumberRepetitions(object $response): void
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
     *
     * @return void
     */
    public function sendMessageCallback($callbackId): void
    {
        file_get_contents('https://api.telegram.org/bot' . $this->accessToken
            . '/answerCallbackQuery?callback_query_id=' . $callbackId);
    }

    /**
     * Отправка сообщения.
     *
     * @param array $response
     *
     * @return void
     */
    public function sendMessageSpecial($response)
    {
        $params = [
            'chat_id' => $response->result[0]->message->chat->id,
            'text'    => 'Невозможно выполнить'
        ];
        $getParams = http_build_query($params);
        $answer = json_decode(file_get_contents(
            'https://api.telegram.org/bot' . $this->accessToken
            . '/sendMessage?' . $getParams
        ));

        if (is_null($answer) === true) {
            $this->logger->error('Неверная реакция на присланное сообщение!');
        } else {
            $this->logger->info('Сообщение отправлено успешно.');
        }

        $this->updateId = $response->result[0]->update_id;
        // Увеличить индекс события
        $this->updateId++;
    }
}
