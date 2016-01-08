<?php
/**
 * @author GBKSOFT <hello@gbksoft.com>
 * @link http://gbksoft.com
 */

namespace gbksoft\apnsGcm;

class Gcm extends \bryglen\apnsgcm\Gcm
{
    /**
     * @inherit
     */
    public function sendMulti($tokens, $text, $payloadData = [], $args = [])
    {
        $tokens = is_array($tokens) ? $tokens : [$tokens];
        // check if its dry run or not
        if ($this->dryRun === true) {
            $this->log($tokens, $text, $payloadData, $args);
            $this->success = true;
            return null;
        }

        $message = new \PHP_GCM\Message();
        foreach ($args as $method => $value) {
            $value = is_array($value) ? $value : [$value];
            call_user_func_array([$message, $method], $value);
        }
        // set a custom payload data
        $payloadData['message'] = $text;
        foreach ($payloadData as $key => $value) {
            $message->addData($key, $value);
        }
        try {
            // send a message
            $result = $this->getClient()->sendMulti($message, $tokens, $this->retryTimes);
            
            if (is_array($result->getResults())) {
                
                /** @var \PHP_GCM\Result $phpGcmResult */
                foreach ($result->getResults() as $phpGcmResult) {
                    if ($phpGcmResult->getErrorCode()) {
                        $this->errors[] = $phpGcmResult->getErrorCode();
                    }
                }
                
            }
            
            $this->success = $result->getSuccess();
        } catch (\InvalidArgumentException $e) {
            $this->errors[] = $e->getMessage();
            // $deviceRegistrationId was null
        } catch (\PHP_GCM\InvalidRequestException $e) {
            if ($e->getMessage()) {
                $this->errors[] = $e->getMessage();
            } else {
                $this->errors[] = sprintf("Received error code %s from GCM Service", $e->getCode());
            }
            // server returned HTTP code other than 200 or 503
        } catch (\Exception $e) {
            $this->errors[] = $e->getMessage();
            // message could not be sent
        }

        return $message;
    }
}
