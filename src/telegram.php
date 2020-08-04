<?php
    require_once __DIR__.'/../vendor/autoload.php';
    require_once __DIR__.'/../config.php';

    $token = TEKEGRAM_API_ACCESS_TOKEN;
    $repeat = NUMBER_OF_REPETITIONS;
    $question = QUESTION_FOR_REPEAT;
    $description = DESCRIPTION;
    $update_id = -1;

    // Ожидание сообщения
while (true) {
    // Получить возможное сообщение
    $response = json_decode(file_get_contents('https://api.telegram.org/bot' . $token . '/getUpdates?offset='
             . $update_id));
    // Если ответ существует и это обычное сообщение (не ответ на /repeat
    if (count($response->result) !== 0 && array_key_exists('message', $response->result[0])) {
        // Анализировать полученные данные
        $chat_id = $response->result[0]->message->chat->id;
        $message = '';
        $keyboard = '';

        // Анализ полученного сообщения
        switch ($response->result[0]->message->text) {
            case '/help':
                $message = urlencode($description);
                break;
            case '/repeat':
                $message = urlencode('Сейчас повторяю ' . $repeat . " раз\n" . $question);

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
                $message = urlencode($response->result[0]->message->text);
                break;
        }

        for ($i = 0; $i < $repeat; $i++) {
            // Ответ
            // Если клавиатура есть
            if ($keyboard === '') {
                $data = json_decode(file_get_contents('https://api.telegram.org/bot' . $token
                    . '/sendMessage?chat_id=' . $chat_id . '&text=' . $message));
            } else { // достаточно спросить один раз
                $data = json_decode(file_get_contents('https://api.telegram.org/bot' . $token
                      . '/sendMessage?chat_id=' . $chat_id . '&text=' . $message . '&reply_markup=' . $keyboard));
                break;
            }
        }

        $update_id = $response->result[0]->update_id;
        // Увеличить индекс события
        $update_id++;
        echo "OK\n";

        // Иначе, если ответ на /repeat
    } elseif (count($response->result) !== 0 && array_key_exists('callback_query', $response->result[0])) {
        $button = $response->result[0]->callback_query->data;
        $chat_id = $response->result[0]->callback_query->message->chat->id;
        $repeat = (int) substr($button, -1);
        $message = 'Количество повторений изменено на ' . $repeat;
        $data = json_decode(file_get_contents('https://api.telegram.org/bot' . $token
            . '/sendMessage?chat_id=' . $chat_id . '&text=' . $message));

        $callback_query_id = $response->result[0]->callback_query->id;

        $data1 = json_decode(file_get_contents('https://api.telegram.org/bot' . $token
            . '/answerCallbackQuery?callback_query_id=' . $callback_query_id));
        print_r($response);

        $update_id = $response->result[0]->update_id;
        $update_id++;
        echo "OK\n";
    }
}
