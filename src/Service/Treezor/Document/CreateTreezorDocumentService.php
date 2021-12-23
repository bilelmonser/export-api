<?php

namespace App\Service\Treezor\Document;

use App\Entity\Csb\Document\Document;
use App\Entity\Csb\UserCsb;
use App\Entity\Treezor\Constantes\TreezorConstantes;
use App\Entity\Treezor\Document\TreezorDocument;
use App\Service\App\ClientHttpService;
use App\Service\App\SerializeService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class CreateTreezorDocumentService
{

    /**
     * @param string $uploadDirPath
     * @param UserCsb $userCsb
     * @param Document $document
     * @param ClientHttpService $clientHttp
     * 
     * @return array
     */
    public function createTreezorDocument(string $uploadDirPath, UserCsb $userCsb, Document $document, ClientHttpService $clientHttp): array
    {
        $hasError = false;

        $path = $_SERVER["DOCUMENT_ROOT"] . $uploadDirPath . DIRECTORY_SEPARATOR . $userCsb->getId() . DIRECTORY_SEPARATOR . $document->getFileName();
        $documentBase64 = base64_encode(file_get_contents($path));

        $body = [
            'userId'              => (string)$userCsb->getTreezorId(),
            'documentTypeId'      => (string)TreezorConstantes::MANDATORY_DOCUMENT_TYPE_KYB['COMPANY REGISTRATION'],
            'name'                => (string)$document->getFileName(),
            'fileContentBase64'   => (string)$documentBase64
        ];

        $response = $clientHttp->formDataRequest($body);

        if ($response->getStatusCode(false) !== 200) {
            $hasError = true;
        }

        return [$hasError, $response];
    }

    /**
     * @param ResponseInterface $data
     * 
     * @return array
     */
    public function handleReceivingData(ResponseInterface $data, SerializeService $normalizer): array
    {
        $arrayObjectDocument = [];
        $jsonString = $data->getContent();
        $decodedData = json_decode($jsonString);

        foreach ($decodedData as $data) {
            foreach ($data as $dat) {
                $arrayObjectDocument[] = $normalizer->DenormalizeContent($dat, TreezorDocument::class);
            }
        }
        return $arrayObjectDocument;
    }
}
