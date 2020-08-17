<?php

namespace Bot\Utils;

use Psr\Log\AbstractLogger;

class Logger extends AbstractLogger
{
    /**
     * Класс предназначен для логирования
     * @param mixed $level
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    public function log($level, $message, array $context = array())
    {
        // Какой уровень логирования установлен
        $levelToNumber = $_SERVER['LOG_LEVEL'];
        switch ($level) {
            case 'emergency':
                $levelToNumber = 1;
                break;
            case 'alert':
                $levelToNumber = 2;
                break;
            case 'critical':
                $levelToNumber = 3;
                break;
            case 'error':
                $levelToNumber = 4;
                break;
            case 'warning':
                $levelToNumber = 5;
                break;
            case 'notice':
                $levelToNumber = 6;
                break;
            case 'info':
                $levelToNumber = 7;
                break;
            case 'debug':
                $levelToNumber = 8;
                break;
        }

        if ($levelToNumber <= $_SERVER['LOG_LEVEL']) {
            // Построение массива подстановки с фигурными скобками
            // вокруг значений ключей массива context.
            $replace = array();
            foreach ($context as $key => $val) {
                $replace['{' . $key . '}'] = $val;
            }

            // Подстановка значений в сообщение и возврат результата.
            echo $level . ': ' . strtr($message, $replace) . "\n";
        }
    }
}
