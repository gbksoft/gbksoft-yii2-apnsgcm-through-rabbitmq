<?php

namespace gbksoft\apnsGcm\console;

use Yii;
use webtoucher\amqp\controllers\AmqpConsoleController as Controller;

class ApnsGcmController extends Controller
{
    public function actionRun()
    {
        $queueName = Yii::$app->apnsGcm->queueName;
        $amqp = Yii::$app->amqp;
        $channel = $amqp->getChannel();
        $channel->queue_declare($queueName, false, true, false, false);
        $message = $channel->basic_get($queueName);
        if (empty($message) || !isset($message->body)) {
            return Controller::EXIT_CODE_NORMAL;
        }

        $body = json_decode($message->body);
        if (!$body) {
            $channel->basic_ack($message->delivery_info['delivery_tag']);
            Yii::error('Has no body. json: ' . json_last_error_msg(), 'ApnsGcm');
            return Controller::EXIT_CODE_ERROR;
        }

        // Log data
        Yii::info($message->body, 'ApnsGcm');

        /* @var $apnsGcm \bryglen\apnsgcm\ApnsGcm */
        Yii::$app->apnsGcm->sendMulti(
            $body->type,
            (array)$body->tokens,
            $body->text,
            $body->payloadData,
            $body->args
        );

        if (!Yii::$app->apnsGcm->success) {
            Yii::error('Error send push: ' . var_export(Yii::$app->apnsGcm->error, true), 'ApnsGcm');
            return Controller::EXIT_CODE_ERROR;
        }

        $channel->basic_ack($message->delivery_info['delivery_tag']);
        $channel->close();

        return Controller::EXIT_CODE_NORMAL;
    }
}