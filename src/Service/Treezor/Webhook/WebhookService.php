<?php

namespace App\Service\Treezor\Webhook;

use App\Entity\Treezor\Webhook\Webhook;
use Doctrine\ORM\EntityManagerInterface;

class WebhookService
{
    private string $webhookSecret;

    public function __construct($webhookSecret)
    {
        $this->webhookSecret = $webhookSecret;
    }

    /**
     * @param $objectPayload
     * @param $objectPayloadSignature
     * @return bool
     */
    public function verifySignature($objectPayload, $objectPayloadSignature): bool
    {
        //TODO : to update !!
        return true;

        $payload_local_signature = base64_encode(hash_hmac(
            'sha256',
            json_encode(
                $objectPayload,
                JSON_UNESCAPED_UNICODE
            ),
            $this->webhookSecret,
            true
        ));

        if (strcmp(
                $payload_local_signature,
                $objectPayloadSignature
            ) === 0) {
            return true;
        } else {
            // TODO: log errors
            return false;
        }
    }

    public function handleWebhook(array $webhook, EntityManagerInterface $em): bool
    {
        if (!$this->persistWebhook($webhook, $em)) {
            return false;
        }

        $whPieces = explode('.', $webhook['webhook']);
        $whStr = $whPieces[0];
        $whAction = $whPieces[1];
        $objectPayload = $webhook['object_payload'];//['users'];

        $status = true;

        switch ($whStr) {
            case 'user':
                $status = $this->handleUserWebhook($objectPayload, $em);
                break;

            case 'document':
//                $status = $this->handleDocumentWebhook($whStr,$whAction,$objectPayload);
                break;

            case 'wallet':
//                $status = $this->handleWalletWebhook($whStr,$whAction,$objectPayload);
                break;
        }

        return $status;
    }

    private function persistWebhook(array $webhook, EntityManagerInterface $em): bool
    {
        $newWebhook = new Webhook();

        $newWebhook->setWebhookId((int)$webhook['webhook_id'])
            ->setWebhook($webhook['webhook'])
            ->setObject($webhook['object'])
            ->setObjectId((int)$webhook['object_id'])
            ->setObjectPayload($webhook['object_payload']['users'])
            ->setObjectPayloadSignature($webhook['object_payload_signature']);
dd($newWebhook);
        try {
            $em->persist($newWebhook);
            $em->flush();
            return true;
        } catch (\Exception $e) {
            // TODO: log errors
            return false;
        }
    }

    private function handleUserWebhook(array $payload, EntityManagerInterface $em): bool
    {
       $arrayUsers = json_decode($payload,true);
       dd($arrayUsers);
    }
}