<?php
    require_once __DIR__.'/../vendor/autoload.php';
    require_once __DIR__.'/../config.php';

    $token       = VK_API_ACCESS_TOKEN;
    $repeat      = NUMBER_OF_REPETITIONS;
    $question    = QUESTION_FOR_REPEAT;
    $description = DESCRIPTION;
    $group_id    = 197571527;

    /*$data = json_decode(file_get_contents('https://api.vk.com/method/groups.getLongPollSettings?group_id='
        . $group_id . '&v=5.101&message_new=1&access_token=' . $token));*/

    // Запрос сессии
    $data  = json_decode(file_get_contents('https://api.vk.com/method/groups.getLongPollServer?group_id='
        . $group_id . '&enabled=1&v=5.101&access_token=' . $token));
    $key    = $data->response->key;
    $server = $data->response->server;
    $ts     = $data->response->ts;

    while (true) {
        // Прослушивание и получение событий
        $response = json_decode(file_get_contents("{$server}?act=a_check&key={$key}&ts={$ts}"));
        // Если есть элементы
        if (count($response->updates) !== 0) {
            // Разбор полученного ответа
            $object = $response->updates[0]->object;
            $user_id = $object->from_id;
            $random_id = $object->random_id;
            $peer_id = $object->peer_id;
            $message = '';

            // Анализ полученного сообщения
            switch ($object->text) {
                case '/help':
                    $message = urlencode($description);
                    break;
                case '/repeat':
                    $message = urlencode('Сейчас повторяю ' . $repeat . " раз\n" . $question);
                    break;
                default:
                    $message = urlencode($object->text);
                    break;
            }
            //var_dump($object->text);
            /*$kbd  = [
                "one_time" => false,
                "buttons" => [
                    [
                        [
                            "action" => [
                                "type" => "text",
                                "payload" => '{"button": "1"}',
                                "label" => "Hello"
                            ],
                            "color" => "primary",
                        ]
                    ],
                ],
            ];
            $keyboard = json_decode($kbd, JSON_UNESCAPED_UNICODE);*/
            //var_dump($keyboard);

            // Вывод сообщения $repeat раз
            for ($i = 0; $i < $repeat; $i++) {
                $answer = json_decode(file_get_contents('https://api.vk.com/method/messages.send?user_id='
                    . $user_id . '&random_id=' . $random_id . '&peer_id=' . $peer_id . '&message=' . $message
                    . '&group_id=' . $group_id . '&v=5.101&access_token=' . $token));
            }

            // Обновление номера последнего события
            $ts = $response->{'ts'};
            echo "OK\n";
        }
    }
