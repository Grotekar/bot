# Бот для Вконтакте и Телеграм

Бот использует для работы API Вконтакте и API Телеграм.

Бот работает в эхо-режиме (введенное пользователем 
текстовое сообщение возвращается столько раз, 
сколько указано в файле `.env`.

Реализации ботов работают независимо друг от друга.

## 1. Требования
* Версия PHP не ниже 7.4;
* Версия API Вконтакте не ниже 5.101.

## 2. Подготовка к использованию
### 1. Бот Вконтакте
1. Следовать рекомендациям по созданию сообщества в 
[официальной документации](https://vk.com/dev/bots_docs) Вконтакте.

2. В сообществе `Управление > Настройки > Работа с API > Long Poll API`
 включить Long Poll API и проверить чтобы версия соответствовала 
 требованиям. 

3. В сообществе `Управление > Сообщения > Настройки для ботов > 
Возможности ботов > Включены`.

>***Примечание***: проверьте наличие **токена** и прав доступа 
**"Управление сообществом"** и **"Сообщения сообщества"**.

### 2. Бот Телеграм
Следовать рекомендациям по созданию бота в 
[официальной документации](https://core.telegram.org/bots#6-botfather) 
Телеграм.

## 3. Установка
Клонируйте этот репозиторий на вашу локальную машину:

    git clone https://github.com/Grotekar/bot.git

Установить пакетную зависимость при помощи composer:

    composer install

На основе файла [.env.example](https://github.com/Grotekar/bot//blob/master.env.example)
cоздать файл `.env`. Внести необходимые изменения в созданный файл,
том числе добавить:
* токен в переменную **VK_API_ACCESS_TOKEN**;
* токен в переменную **TELEGRAM_API_ACCESS_TOKEN**.

## 4. Функциональные возможности
- Пользователь может отправить команду /help и увидеть текст, 
описывающий бота;

- Пользователь может отправить команду /repeat и в ответ бот 
отправит сообщение о том, какое сейчас выбрано значение
повторов и вопрос, сколько раз повторять сообщение в дальнейшем.  

    К вопросу будут прилагаться кнопки для выбора ответа 
    (кнопки с цифрами от 1 до 5). После выбора пользователем, 
    все ответы бота будут дублироваться указанное количество раз.

- Кастомизация выполняется через файл `.env`, который
содержит определения для:  
    - токена Вконтакте;
    - токена Телеграм;
    - идентификаотра группы Вконтакте;
    - вопроса по команде /repeat;
    - сообщения, отправляемое в ответ на /help;
    - начального количества повторов на каждый ответ,
    - уровня логирования;
    - создавать ли данные для тестирования.

## 5. Тестирование
Для тестирования соответствующего бота необходимо:

1. Корректно заполнить файл `.env`:
    * для Вконтакте: указать верный токен и идентификатор группы;
    * для Телеграм: указать верный токен.
2. Получить данные запроса для тестирования.
    1. В файле конфигурации `.env` в переменной **CREATE_DATA_FOR_TEST**
        установить значение ***true***.
    2. 
        * Для Вконтакте:
        
            Запустить файл [src/vk.php](https://github.com/Grotekar/bot/blob/master/src/vk.php)
            и отправить боту сообщение с тестом "1" (без кавычек).
            
            В папке **tests** появится файл `dataForTestVk.txt` с данными для запроса. 

            Его содержимое необходимо вставить в переменную **DATA_FOR_TESTING_VK**
            файла `dataForTesting.txt`
        
        * Для Телеграм:
        
            Запустить файл [src/telegram.php](https://github.com/Grotekar/bot/blob/master/src/telegram.php)
            и отправить боту сообщение с тестом "1" (без кавычек).
            
            В папке **tests** появится файл `dataForTestTelegram.txt` с данными для запроса. 

            Его содержимое необходимо вставить в переменную **DATA_FOR_TESTING_TELEGRAM**
            файла `dataForTesting.txt`.

    3. После этого значение переменной **CREATE_DATA_FOR_TEST** вернуть в значение ***false***.

    4. Теперь для тестирования нужно запустить соответствующий
        файл ([tests\PHPUnit\Framework\VkTest.php](https://github.com/Grotekar/bot/blob/master/tests/PHPUnit/Framework/VkTest.php)
        либо [tests\PHPUnit\Framework\TelegramTest.php](https://github.com/Grotekar/bot/blob/master/tests/PHPUnit/Framework/TelegramTest.php)).

        > **Примечание:** Будте готовы к тому, что бот отправит 
        сообщения тому, кто  отправлял боту сообщение с текстом 1.

## 6. Описание файлов
### [src/vk.php](https://github.com/Grotekar/bot/blob/master/src/vk.php)
Запускает бот для Вконтакте.

### [src/telegram.php](https://github.com/Grotekar/bot/blob/master/src/telegram.php)
Запускает бот для Телеграм.

### [src/api/VkApi.php](https://github.com/Grotekar/bot/blob/master/src/api/VkApi.php)
Класс, описывающий работу с API Вконтакте.

### [src/api/TelegramApi.php](https://github.com/Grotekar/bot/blob/master/src/api/TelegramApi.php)
Класс, описывающий работу с API Телеграм.

### [src/Utils/Logger.php](https://github.com/Grotekar/bot/blob/master/src/Utils/Logger.php)
Класс, содержащий реализацию логирования.

### [.env.example](https://github.com/Grotekar/bot/blob/master/.env.example)
Шаблон для конфигурационного файла.

### [tests\PHPUnit\Framework\VkTest.php](https://github.com/Grotekar/bot/blob/master/tests/PHPUnit/Framework/VkTest.php)
Тестирование правильного распознования команд **/help**, 
**/repeat** и обычного текстового сообщения.

### [tests\PHPUnit\Framework\TelegramTest.php](https://github.com/Grotekar/bot/blob/master/tests/PHPUnit/Framework/TelegramTest.php)
Тестирование правильного распознования команд **/help**, 
**/repeat** и обычного текстового сообщения.

### [tests\dataForTest.php](https://github.com/Grotekar/bot/blob/master/tests/dataForTest.php)
Файл шаблонов для тестирования ботов.