<?php

    namespace Bot\api;

    require_once __DIR__ . '/../vendor/autoload.php';

    $group_id    = 197571527;

    // Запрос сессии
    $vk     = new VkApi();
    $data   = $vk->getSessionByGetLongPollServer($group_id);
    $key    = $data['key'];
    $server = $data['server'];
    $ts     = $data['ts'];
    $repeat = $vk->getNumberRepetition();

while (true) {
    // Если ответ существует и это обычное сообщение (не ответ на /repeat)
    $response = $vk->listenEvent($server, $key, $ts);
    // Если есть элементы
    if ($vk->typeResponse($response) === 'not_button_click') {
        // Разбор полученного ответа
        $object = $response->updates[0]->object;
        $request_params = [
            'user_id' => $object->from_id,
            'random_id' => $object->random_id,
            'peer_id' => $object->peer_id,
            'group_id' => $group_id,
        ];
        $keyboard = '';

        // Анализ полученного сообщения
        switch ($object->text) {
            case '/help':
                $request_params['message'] = $vk->getDescription();
                break;
            case '/repeat':
                $request_params['message'] = 'Сейчас повторяю ' . $repeat . " раз\n" . $vk->getQuestion();
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
                $request_params['message'] = $object->text;
                break;
        }

        // Вывод сообщения $repeat раз
        for ($i = 0; $i < $repeat; $i++) {
            // Отправка сообщения
            if ($keyboard === '') {
                $vk->sendMessage($request_params);
            } else { // достаточно спросить один раз
                $vk->sendMessage($request_params, $keyboard);
                break;
            }
        }

        // Обновление номера последнего события
        $ts = $response->{'ts'};
        echo "OK\n";
    } elseif ($vk->typeResponse($response) === 'button_click') {
        $button = json_decode($response->updates[0]->object->payload);
        $repeat = $button->button;
        $request_params['message'] = 'Количество повторений изменено на ' . $repeat;
        // Отправка сообщения
        $vk->sendMessage($request_params);

        // Обновление номера последнего события
        $ts = $response->{'ts'};
        echo "OK\n";
    }
}
