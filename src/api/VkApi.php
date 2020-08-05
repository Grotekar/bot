<?php

namespace Bot\api;

require_once __DIR__ . '/../../config.php';

class VkApi
{
    protected string $accessToken = VK_API_ACCESS_TOKEN;
    protected float $apiVersion = 5.101;

    public function getSessionByGetLongPollServer(int $group_id)
    {
        $query            = json_decode(file_get_contents(
            'https://api.vk.com/method/groups.getLongPollServer?group_id='
            . $group_id . '&enabled=1&v=5.101&access_token=' . $this->accessToken
        ));
        $result['key']    = $query->response->key;
        $result['server'] = $query->response->server;
        $result['ts']     = $query->response->ts;
        return $result;
    }

    public function getDescription()
    {
        return DESCRIPTION;
    }

    public function getQuestion()
    {
        return QUESTION_FOR_REPEAT;
    }

    public function getNumberRepetition()
    {
        return NUMBER_OF_REPETITIONS;
    }

    public function listenEvent($server, $key, $ts)
    {
        $response = json_decode(file_get_contents("{$server}?act=a_check&key={$key}&ts={$ts}"));
        return $response;
    }

    public function typeResponse($response): string
    {

        if (
            count($response->updates) !== 0 && array_key_exists('payload', $response->updates[0]->object) === false
        ) {
            return 'not_button_click';
        } elseif (
            count($response->updates) !== 0 && array_key_exists('payload', $response->updates[0]->object) === true
        ) {
            return 'button_click';
        } else {
            return 'unknown';
        }
    }

    public function sendMessage($params, $keyboard = null)
    {
        $params['v']            = $this->apiVersion;
        $params['access_token'] = $this->accessToken;
        $get_params             = http_build_query($params);

        $answer = json_decode(file_get_contents('https://api.vk.com/method/messages.send?'
            . $get_params . '&keyboard=' . $keyboard));
    }
}
