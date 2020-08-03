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
        // Если ответ существует
        if (count($response->result) !== 0) {
            // Анализировать полученные данные
            $chat_id = $response->result[0]->message->chat->id;
            $message = '';

            // Анализ полученного сообщения
            switch ($response->result[0]->message->text) {
                case '/help':
                    $message = urlencode($description);
                    break;
                case '/repeat':
                    $message = urlencode('Сейчас повторяю ' . $repeat . " раз\n" . $question);
                    break;
                default:
                    $message = urlencode($response->result[0]->message->text);
                    break;
            }
            for ($i = 0; $i < $repeat; $i++) {
                // Ответ
                $data = json_decode(file_get_contents('https://api.telegram.org/bot' . $token . '/sendMessage?chat_id='
                    . $chat_id . '&text=' . $message));
            }

            $update_id = $response->result[0]->update_id;
            // Увеличить индекс события
            $update_id++;
            echo "OK\n";
        }
    }
