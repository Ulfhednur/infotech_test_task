<?php
namespace app\services;

use app\models\Book;
use Yii;
use yii\helpers\Json;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Client as GuzzleHttpClient;

/**
 * Class SmsPilotService
 * @package app\services
 */
abstract class SmsPilotService
{
    private const API_URL = 'https://smspilot.ru/api2.php';

    /**
     * Отправка СМС подписчикам.
     * Предусмотрено избежание отправки повторных СМС, если книга написана в соавторстве и
     * подписчик подписан на нескольких из авторов
     * @param Book $book
     */
    public static function notifySubscribers(Book $book): void
    {
        if (Yii::$app->params['enableSms']) {
            $title = $book->getDescription()->one()->title;
            $isSendPhones = [];
            foreach ($book->getAuthors()->each() as $author) {
                $phones = array_diff($author->getSubscriptions()->select(['phone_num'])->column(), $isSendPhones);
                $isSendPhones = array_unique(array_merge($isSendPhones, $phones));
                if (!empty($phones)) {
                    $text = "У автора {$author->fio} вышла новая книга {$title}";
                    $requestBody = [
                        'apikey' => Yii::$app->params['SmsPilotApiKey'],
                        'from' => 'Knizshnyi',
                        'send' => []
                    ];
                    $i = 1;
                    foreach ($phones as $phone) {
                        $requestBody['send'][] = [
                            'id' => $i,
                            'to' => $phone,
                            'text' => $text
                        ];
                        $i++;
                    }
                    $client = new GuzzleHttpClient();
                    try {
                        $response = $client->request(
                            'POST',
                            self::API_URL,
                            [
                                'json' => $requestBody,
                                'headers' => [
                                    'accept' => 'application/json',
                                    'content-type' => 'application/json; charset=utf-8'
                                ],
                            ]
                        );
                        $responseBody = Json::decode($response->getBody());
                        if (is_array($responseBody) && !empty($responseBody['error'])) {
                            Yii::error(
                                [
                                    'action' => 'sms-send',
                                    'request' => $requestBody,
                                    'response' => $responseBody
                                ]
                            );
                        }
                    } catch (BadResponseException $e) {
                        $response = $e->getResponse();
                        $responseBodyAsString = $response->getBody()->getContents();
                        Yii::error(
                            [
                                'action' => 'sms-send',
                                'request' => $requestBody,
                                'response' => $responseBodyAsString
                            ]
                        );
                    } catch (GuzzleException $e) {
                        Yii::error(
                            [
                                'action' => 'sms-send',
                                'request' => $requestBody,
                                'response' => $e->getMessage()
                            ]
                        );
                    }
                }
            }
        }
    }

    /**
     * Проверяет, обслуживается ли номер
     * @param int $phonenum
     * @return bool
     */
    public static function checkPhoneNum(int $phoneNum): bool
    {
        if (Yii::$app->params['enableSms']) {
            $requestBody = [
                'apikey' => Yii::$app->params['SmsPilotApiKey'],
                'from' => 'Knizshnyi',
                'send' => [
                    'to' => $phoneNum
                ]
            ];
            $client = new GuzzleHttpClient();
            try {
                $response = $client->request(
                    'POST',
                    self::API_URL,
                    [
                        'json' => $requestBody,
                        'headers' => [
                            'accept' => 'application/json',
                            'content-type' => 'application/json; charset=utf-8'
                        ],
                    ]
                );
                $responseBody = Json::decode($response->getBody());
                if (is_array($responseBody) && !empty($responseBody['error'])) {
                    return false;
                }
                return true;
            } catch (BadResponseException $e) {
                $response = $e->getResponse();
                $responseBodyAsString = $response->getBody()->getContents();
                Yii::error(
                    [
                        'action' => 'sms-send',
                        'request' => $requestBody,
                        'response' => $responseBodyAsString
                    ]
                );
            } catch (GuzzleException $e) {
                Yii::error(
                    [
                        'action' => 'sms-send',
                        'request' => $requestBody,
                        'response' => $e->getMessage()
                    ]
                );
            }
            return false;
        }
        return true;
    }
}