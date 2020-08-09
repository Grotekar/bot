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
