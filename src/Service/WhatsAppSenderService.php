<?php

namespace App\Service;

use App\Entity\Message;
use Netflie\WhatsAppCloudApi\WhatsAppCloudApi;

class WhatsAppSenderService
{

    private WhatsAppCloudApi $api;

    public function __construct(string $fromPhoneNumberId, string $accessToken)
    {
        $this->api = new WhatsAppCloudApi([
            'from_phone_number_id' => $fromPhoneNumberId,
            'access_token' => $accessToken,
        ]);
    }

    public function send(Message $message)
    {
        $response = $this->api->sendTextMessage(
            $message->getPhone(),
            $message->getMessage()
        );

        $message->setStatus(
            $response->isError() ?
                Message::STATUS_ERROR : Message::STATUS_SUCCESS
        );
    }
}
