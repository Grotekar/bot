<?php
    require_once __DIR__.'/../vendor/autoload.php';
    require_once __DIR__.'/../config.php';

    $token       = VK_API_ACCESS_TOKEN;
    $repeat      = NUMBER_OF_REPETITIONS;
    $question    = QUESTION_FOR_REPEAT;
    $description = DESCRIPTION;
    $group_id    = 197571527;

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
        if (count($response->updates) !== 0 && array_key_exists('payload', $response->updates[0]->object) === false) {
            //print_r($response->updates[0]);
            // Разбор полученного ответа
            $object = $response->updates[0]->object;
            $user_id = $object->from_id;
            $random_id = $object->random_id;
            $peer_id = $object->peer_id;
            $message = '';
            $keyboard = '';

            // Анализ полученного сообщения
            switch ($object->text) {
                case '/help':
                    $message = urlencode($description);
                    break;
                case '/repeat':
                    $message = urlencode('Сейчас повторяю ' . $repeat . " раз\n" . $question);
                    $keyboard = json_encode(array(
                        "one_time" => true,
                        "buttons" => array(
                            array(
                                array(
                                    "action" => array(
                                        "type" => "text",
                                        "payload" => '{"button":"1"}',
                                        "label" => "1"
                                    )
                                ),
                                array(
                                    "action" => array(
                                        "type" => "text",
                                        "payload" => '{"button":"2"}',
                                        "label" => "2"
                                    )
                                ),
                                array(
                                    "action" => array(
                                        "type" => "text",
                                        "payload" => '{"button":"3"}',
                                        "label" => "3"
                                    )
                                ),
                                array(
                                    "action" => array(
                                        "type" => "text",
                                        "payload" => '{"button":"4"}',
                                        "label" => "4"
                                    )
                                ),
                                array(
                                    "action" => array(
                                        "type" => "text",
                                        "payload" => '{"button":"5"}',
                                        "label" => "5"
                                    )
                                ),
                            )
                        ),
                        'inline' => false
                    ));
                    break;
                default:
                    $message = urlencode($object->text);
                    break;
            }


            // Вывод сообщения $repeat раз
            for ($i = 0; $i < $repeat; $i++) {
                $answer = json_decode(file_get_contents('https://api.vk.com/method/messages.send?user_id='
                    . $user_id . '&random_id=' . $random_id . '&peer_id=' . $peer_id . '&message=' . $message
                    . '&group_id=' . $group_id . '&keyboard=' . $keyboard . '&v=5.101&access_token=' . $token));
                //print_r($answer);
            }

            // Обновление номера последнего события
            $ts = $response->{'ts'};
            echo "OK\n";

        } elseif (count($response->updates) !== 0
            && array_key_exists('payload', $response->updates[0]->object) === true) {
            $button = json_decode($response->updates[0]->object->payload);
            //print_r($button->button);
            /*$chat_id = $response->result[0]->callback_query->message->chat->id;*/
            $repeat = $button->button;
            $message = urlencode('Количество повторений изменено на ' . $repeat);
            $answer = json_decode(file_get_contents('https://api.vk.com/method/messages.send?user_id='
                . $user_id . '&random_id=' . $random_id . '&peer_id=' . $peer_id . '&message=' . $message
                . '&group_id=' . $group_id . '&v=5.101&access_token=' . $token));

            // Обновление номера последнего события
            $ts = $response->{'ts'};
            echo "OK\n";
        }
    }
