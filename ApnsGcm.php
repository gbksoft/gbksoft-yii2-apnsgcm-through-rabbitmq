<?php
/**
 * @author GBKSOFT <hello@gbksoft.com>
 * @link http://gbksoft.com
 */

namespace gbksoft\apnsGcm;

use Yii;
use PhpAmqpLib\Message\AMQPMessage;

class ApnsGcm extends \bryglen\apnsgcm\ApnsGcm
{
    /**
     * Queue name in RabbitMQ
     *
     * @var string
     */
    public $queueName = 'apns-gcm';

    /**
     * add to queue a push notification depending on type
     * @param $type
     * @param $tokens
     * @param $text
     * @param array $payloadData
     * @param array $args
     * @return boolean
     */
    public function addToQueue($type, $tokens, $text, $payloadData = [], $args = [])
    {
        $amqp = Yii::$app->amqp;
        $channel = $amqp->getChannel();
        $channel->queue_declare($this->queueName, false, true, false, false);

        $msg = new AMQPMessage(
            json_encode([
                'type' => $type,
                'tokens' => $tokens,
                'text' => $text,
                'payloadData' => $payloadData,
                'args' => $args,
            ]),
            [
                'content_type' => 'application/json',
                'delivery_mode' => 2
            ]
        );

        $result = $channel->basic_publish($msg, '', $this->queueName);
        $channel->close();

        return $result;
    }
}
