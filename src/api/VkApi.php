<?php

namespace Bot\api;

use Bot\Utils\Logger;
use Dotenv\Dotenv;
use Psr\Log\LoggerInterface;

/**
 * Описывает методы для использования к VK Api
 */

class VkApi
{
    private string $accessToken;
    private float $apiVersion = 5.101;
    private string $description;
    private string $question;
    private int $repetitions;
    private string $server;
    private string $key;
    private int $ts;
    private int $groupId;
    private LoggerInterface $logger;

    /**
     * VkApi constructor.
     */
    public function __construct()
    {
        $dotenv            = Dotenv::createImmutable(__DIR__ . '/../..');
        $dotenv->load();
        $this->accessToken = $_SERVER['VK_API_ACCESS_TOKEN'];
        $this->description = $_SERVER['DESCRIPTION'];
        $this->question    = $_SERVER['QUESTION_FOR_REPEAT'];
        $this->repetitions = $_SERVER['NUMBER_OF_REPETITIONS'];
        $this->groupId     = $_SERVER['VK_GROUP_ID'];
        $this->logger      = new Logger();
        $this->ts          = $this->getSessionByGetLongPollServer($this->groupId);
    }

    /**
     * Получение данных сессии.
     *
     * @param int  $groupId
     *
     * @return int
     */
    private function getSessionByGetLongPollServer(int $groupId): int
    {
        $query = json_decode(file_get_contents(
            'https://api.vk.com/method/groups.getLongPollServer?group_id='
            . $groupId . '&enabled=1&v=5.101&access_token='
            . $this->accessToken . '&wait=25'
        ));

        if (array_key_exists('error', $query) === true) {
            $this->logger->critical(
                'Проблема с group_id. Сообщение от Api: {error_msg}.',
                ['error_msg' => $query->error->error_msg]
            );
            die();
        } else {
            $this->key    = $query->response->key;
            $this->server = $query->response->server;
            $result       = $query->response->ts;
            $this->logger->info('Успешное подключение к VKApi');
        }
        return $result;
    }

    /**
     * Прослучшивание канала на наличие событий.
     *
     * @return mixed
     */
    public function listenEvent()
    {
        do {
            $response = json_decode(file_get_contents(
                "{$this->server}?act=a_check&key={$this->key}&ts={$this->ts}"
            ));
        } while (count($response->updates) === 0);

        $this->logger->info('Получено сообщение');
        return $response;
    }

    /**
     * Пролучение типа запроса (была нажата кнопка или нет)
     * при его наличии.
     *
     * @param mixed $response
     *
     * @return string
     */
    public function typeResponse($response): string
    {
        if (array_key_exists('payload', $response->updates[0]->object) === false) {
            return 'not_button_click';
        } elseif (array_key_exists('payload', $response->updates[0]->object) === true) {
            return 'button_click';
        } else {
            return 'unknown';
        }
    }

    /**
     * Обработка полученного сообщения от пользователя.
     *
     * @param $response
     *
     * @return string
     */
    public function processingResponse($response): string
    {
        // Для получения данных для тестирования раскомментируйте строку
        //file_put_contents('tests\dataForTestVk.txt', json_encode($response));
        // Разбор полученного ответа
        $object = $response->updates[0]->object;
        $requestParams = [
            'user_id'      => $object->from_id,
            'random_id'    => $object->random_id,
            'peer_id'      => $object->peer_id,
            'group_id'     => $this->groupId,
            'v'            => $this->apiVersion,
            'access_token' => $this->accessToken,
        ];

        // Анализ полученного сообщения
        switch ($object->text) {
            case '/help':
                $this->sendMessageHelp($requestParams);
                $word = 'help';
                break;
            case '/repeat':
                $this->sendMessageRepeat($requestParams);
                $word = 'repeat';
                break;
            default:
                if ($object->text !== '') {
                    $requestParams['message'] = $object->text;
                } else {
                    $requestParams['message'] = 'Невозможно выполнить';
                }
                $this->sendMessageDefault($requestParams, $this->repetitions);
                $word = 'another';
                break;
        }

        // Обновление номера последнего события
        $this->ts = $response->ts;
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
        $params['message'] = $this->description;
        $getParams         = http_build_query($params);

        $answer = json_decode(file_get_contents('https://api.vk.com/method/messages.send?'
            . $getParams));

        if (array_key_exists('error', $answer) === true) {
            $this->logger->error(
                'Неверная реакция на /help! Сообщение от Api: {error_msg}.',
                ['error_msg' => $answer->error->error_msg]
            );
        } else {
            $this->logger->info(
                'Сообщение отправлено успешно в ответ на /help.'
                . 'Идентификатор сообщения {id}.',
                ['id' => $answer->response]
            );
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
        $params['message'] = 'Сейчас повторяю '
                           . $this->repetitions . " раз\n" . $this->question;
        $getParams         = http_build_query($params);
        $keyboard          = json_encode([
            "one_time" => true,
            "buttons"  => [
                [
                    [
                        "action" => [
                            "type" => "text",
                            "payload" => '{"button":"1"}',
                            "label" => "1"
                        ]
                    ],
                    [
                        "action" => [
                            "type" => "text",
                            "payload" => '{"button":"2"}',
                            "label" => "2"
                        ]
                    ],
                    [
                        "action" => [
                            "type" => "text",
                            "payload" => '{"button":"3"}',
                            "label" => "3"
                        ]
                    ],
                    [
                        "action" => [
                            "type" => "text",
                            "payload" => '{"button":"4"}',
                            "label" => "4"
                        ]
                    ],
                    [
                        "action" => [
                            "type" => "text",
                            "payload" => '{"button":"5"}',
                            "label" => "5"
                        ]
                    ],
                ]
            ],
            'inline'   => false
        ]);

        $answer = json_decode(file_get_contents('https://api.vk.com/method/messages.send?'
            . $getParams . '&keyboard=' . $keyboard));

        if (array_key_exists('error', $answer) === true) {
            $this->logger->error(
                'Неверная реакция на /repeat! Сообщение от Api: {error_msg}.',
                ['error_msg' => $answer->error->error_msg]
            );
        } else {
            $this->logger->info(
                'Сообщение отправлено успешно в ответ на /repeat.'
                . 'Идентификатор сообщения {id}.',
                ['id' => $answer->response]
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
        $button            = json_decode($response->updates[0]->object->payload);
        $this->repetitions = $button->button;
        $requestParams    = [
            'user_id'      => $response->updates[0]->object->from_id,
            'random_id'    => $response->updates[0]->object->random_id,
            'peer_id'      => $response->updates[0]->object->peer_id,
            'group_id'     => $this->groupId,
            'v'            => $this->apiVersion,
            'access_token' => $this->accessToken,
        ];
        $requestParams['message'] = 'Количество повторений изменено на '
            . $this->repetitions;

        // Отправка сообщения
        $this->sendMessageDefault($requestParams);
        $this->ts = $response->ts;
    }

    /**
     * Отправка сообщения.
     *
     * @param array $params
     * @param int   $repeat
     *
     * @return void
     */
    public function sendMessageDefault(array $params, int $repeat = 1)
    {
        $getParams = http_build_query($params);
        $answer    = '';

        // Вывод сообщения $repeat раз
        for ($i = 0; $i < $repeat; $i++) {
            $answer = json_decode(file_get_contents(
                'https://api.vk.com/method/messages.send?'
                . $getParams
            ));
        }

        if (array_key_exists('error', $answer) === true) {
            $this->logger->error(
                'Неверная реакция на присланное сообщение! '
                . 'Сообщение от Api: {error_msg}.',
                ['error_msg' => $answer->error->error_msg]
            );
        } else {
            $this->logger->info(
                'Сообщение отправлено успешно. Идентификатор сообщения {id}.',
                ['id' => $answer->response]
            );
        }
    }
}
