<?php
    require_once __DIR__.'/../vendor/autoload.php';
    require_once '../config.php';

    $token    = VK_API_ACCESS_TOKEN;
    $description = 'Бот пока ничего не умеет. Только ваше эхо';
    $group_id = 197571527;
    //$user_id  =  123871732;

    /*$data = json_decode(file_get_contents('https://api.vk.com/method/messages.getLongPollServer?group_id='
        . $group_id . '&need_pts=1&lp_version=5.65&v=5.101&access_token=' . $token));*/
    $data = json_decode(file_get_contents('https://api.vk.com/method/groups.getLongPollSettings?group_id='
        . $group_id . '&v=5.101&message_new=1&access_token=' . $token));

    $data1  = json_decode(file_get_contents('https://api.vk.com/method/groups.getLongPollServer?group_id='
        . $group_id . '&enabled=1&v=5.101&access_token=' . $token));
    $key    = $data1->response->key;
    $server = $data1->response->server;
    $ts     = $data1->response->ts;

    while (true) {
        $response = json_decode(file_get_contents("{$server}?act=a_check&key={$key}&ts={$ts}&wait=25"));
        $object   = $response->updates[0]->object;

        $user_id   = $object->from_id;
        $random_id = $object->random_id;
        $peer_id   = $object->peer_id;
        $message   = '';

        if ($object->text === '/help') {
            $message = urlencode($description);
        } else {
            $message = urlencode($object->text);
        }
        var_dump($object->text);
        /*$kbd  = [
            "one_time" => false,
            "buttons" => [
                [
                    [
                        "action" => [
                            "type" => "text",
                            "payload" => '{"button": "1"}',
                            "label" => "Hello"
                        ],
                        "color" => "primary",
                    ]
                ],
            ],
        ];
        $keyboard = json_decode($kbd, JSON_UNESCAPED_UNICODE);*/
        //var_dump($keyboard);

        $answer = json_decode(file_get_contents('https://api.vk.com/method/messages.send?user_id='
                . $user_id . '&random_id=' . $random_id . '&peer_id='. $peer_id . '&message=' . $message
                . '&group_id=' . $group_id . '&v=5.101&access_token=' . $token));

        $ts     = $response->{'ts'};
        //var_dump($answer);
        /*var_dump($object);
        var_dump($user_id);*/
    }

    echo 'OK';