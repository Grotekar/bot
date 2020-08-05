<?php

    namespace Bot\api;

    require_once __DIR__ . '/../vendor/autoload.php';

    $telegram    = new TelegramApi();
    $update_id   = -1;
    $repeat      = $telegram->getNumberRepetition();

    // Ожидание сообщения
while (true) {
    // Получить возможное сообщение
    $response = $telegram->listenEvent($update_id);

    // Если ответ существует и это обычное сообщение (не ответ на /repeat)
    if ($telegram->typeResponse($response) === 'not_button_click') {
        // Анализировать полученные данные
        $request_params = [
            'chat_id' => $response->result[0]->message->chat->id,
        ];
        $keyboard = '';

        // Анализ полученного сообщения
        switch ($response->result[0]->message->text) {
            case '/help':
                $request_params['text'] = $telegram->getDescription();
                break;
            case '/repeat':
                $request_params['text'] = 'Сейчас повторяю ' . $repeat . " раз\n" . $telegram->getQuestion();
                $keyboard = json_encode(array(
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
                break;
            default:
                $request_params['text'] = $response->result[0]->message->text;
                break;
        }

        for ($i = 0; $i < $repeat; $i++) {
            // Ответ
            // Если клавиатура есть
            if ($keyboard === '') {
                $telegram->sendMessage($request_params);
            } else { // достаточно спросить один раз
                $telegram->sendMessage($request_params, $keyboard);
                break;
            }
        }

        $update_id = $response->result[0]->update_id;
        // Увеличить индекс события
        $update_id++;
        echo "OK\n";

        // Иначе, если ответ на /repeat
    } elseif ($telegram->typeResponse($response) === 'button_click') {
        $button = $response->result[0]->callback_query->data;
        $repeat = (int) substr($button, -1);
        $request_params = [
            'chat_id' => $response->result[0]->callback_query->message->chat->id,
            'text'    => 'Количество повторений изменено на ' . $repeat,
        ];
        // Отправка сообщения
        $telegram->sendMessage($request_params);

        // Отметка, что запрос выполнен
        $callback_query_id = $response->result[0]->callback_query->id;
        $telegram->sendMessageCallback($callback_query_id);

        $update_id = $response->result[0]->update_id;
        // Увеличить индекс события
        $update_id++;
        echo "OK\n";
    }
}
