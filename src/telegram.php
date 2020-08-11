<?php

namespace Bot\api;

require_once __DIR__ . '/../vendor/autoload.php';

$telegram    = new TelegramApi(
    TELEGRAM_API_ACCESS_TOKEN,
    DESCRIPTION,
    QUESTION_FOR_REPEAT,
    NUMBER_OF_REPETITIONS,
    -1
);

// Ожидание сообщения
while (true) {
    // Получить возможное сообщение
    $response = $telegram->listenEvent();

    // Если ответ существует и это обычное сообщение (не ответ на /repeat)
    if ($telegram->typeResponse($response) === 'not_button_click') {
        // Анализ полученного сообщения
        $telegram->processingResponse($response);

    // Иначе, если ответ на /repeat
    } elseif ($telegram->typeResponse($response) === 'button_click') {
        // Изменить количество повторений
        $telegram->editNumberRepetitions($response);
    } elseif ($telegram->typeResponse($response) === 'unknown') {
        $telegram->sendMessageSpecial($response);
    }
}
