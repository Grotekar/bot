<?php

namespace Bot\api;

require_once __DIR__ . '/../vendor/autoload.php';

// Запрос сессии
$vk     = new VkApi(
    VK_API_ACCESS_TOKEN,
    DESCRIPTION,
    QUESTION_FOR_REPEAT,
    NUMBER_OF_REPETITIONS,
    VK_GROUP_ID,
);

while (true) {
    // Если ответ существует и это обычное сообщение (не ответ на /repeat)
    $response = $vk->listenEvent();

    // Если есть это не событие по кнопке в команде /repeat
    if ($vk->typeResponse($response) === 'not_button_click') {
        // Анализ полученного сообщения
        $vk->processingResponse($response);

    // Если нажатие на кнопку
    } elseif ($vk->typeResponse($response) === 'button_click') {
        // Изменить количество повторений
        $vk->editNumberRepetitions($response);
    }
}
