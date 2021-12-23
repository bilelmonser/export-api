<?php

namespace App\Service\Csb;

use App\Entity\Csb\Constantes\AppConstantes;
use App\Entity\Csb\Document\Document;
use App\Entity\Csb\UserCsb;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;

class RetrieveDataByRequestService
{
    /**
     * @param array $arrayBody
     * @param EntityManagerInterface $em
     * @return array|UserCsb
     */
    public function getUserCsbByRequestedId(array $arrayBody,EntityManagerInterface $em)
    {
        if (!isset($arrayBody['userId']) || !is_int($arrayBody['userId']))
            return ['message' => 'You must specify the user id.', 'httpCode' => Response::HTTP_BAD_REQUEST];
        $userId = $arrayBody['userId'];

        /**
         * @var UserCsb|null @userCsb
         */
        $userCsb = $em->getRepository(UserCsb::class)->find($userId);
        if (null === $userCsb)
            return ['message' => 'User not found.', 'httpCode' => Response::HTTP_NOT_FOUND];

        return $userCsb;
    }

    /**
     * @param array $arrayBody
     * @param EntityManagerInterface $em
     * @return array
     */
    public function getDocumentByRequestedId(array $arrayBody,EntityManagerInterface $em): array
    {
        $errors = null;
        $document = null;

        if (!isset($arrayBody['documentId']) || !is_int($arrayBody['documentId'])) {
            $errors = [
                'message' => 'You must specify the document id.',
                'httpCode' => Response::HTTP_BAD_REQUEST
            ];
            return [$errors, $document];
        }
        $documentId = $arrayBody['documentId'];

        $userId = $arrayBody['userId'];
        $document = $em->getRepository(Document::class)
            ->findOneBy(
                array(
                    'id' => $documentId,
                    'userCsb' => $userId,
                    'fileStatus' => AppConstantes::FILE_STATUS['NONE'],
                    'treezorStatusValidation' => AppConstantes::FILE_STATUS['NONE']
                )
            );

        if (null === $document) {
            $errors = [
                'message' => 'Document not found.',
                'httpCode' => Response::HTTP_NOT_FOUND
            ];
            return [$errors, $document];
        }

        $document = $document;

        return [$errors, $document];
    }
}
