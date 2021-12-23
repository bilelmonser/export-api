<?php

namespace App\Service\Csb\Document;

use App\Entity\Csb\Constantes\AppConstantes;
use App\Entity\Csb\Document\Document;
use App\Entity\Csb\UserCsb;
use App\Entity\Treezor\Constantes\TreezorConstantes;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

class DocumentService
{

    /**
     * @param Request $request
     * @param UserCsb $userCsb
     * @return bool
     */
    public function submittedRequestValidation(Request $request, UserCsb $userCsb): bool
    {
        $isValid = true;

        $uploadedFile = $request->files->get('kybFile', null);
        if ($uploadedFile === null) {
            $isValid = false;
        }

        $levelDoc = abs((int)$request->request->get('levelDoc', 2));
        if (!(in_array($levelDoc, AppConstantes::FILE_LEVEL, true))) {
            $isValid = false;
        }

        $documentTypeId = abs((int)$request->request->get('documentTypeId', 0));

        if ($userCsb->getUserTypeId() == TreezorConstantes::USER_TYPE_ID['KYB']) {
            if (!(in_array($documentTypeId, TreezorConstantes::MANDATORY_DOCUMENT_TYPE_KYB, true))) {
                $isValid = false;
            }
        } else {
            if ($levelDoc == AppConstantes::FILE_LEVEL['MANDATORY']) {
                if (!(in_array($documentTypeId, TreezorConstantes::MANDATORY_DOCUMENT_TYPE_KYC, true))) {
                    $isValid = false;
                }
            } else {
                if (!(in_array($documentTypeId, TreezorConstantes::COMPLEMENT_DOCUMENT_TYPE_KYC, true))) {
                    $isValid = false;
                }
            }
        }
        return $isValid;
    }


    /**
     * @param Request $request
     * @param UserCsb $userCsb
     * @return bool
     */
    public function submittedUpdateRequestValidation(Request $request, UserCsb $userCsb): bool
    {
        $isValid = $this->submittedRequestValidation($request, $userCsb);

        $levelDoc = abs((int)$request->request->get('levelDoc', 2));
        if (!(in_array($levelDoc, AppConstantes::FILE_LEVEL, true)))
            $isValid = false;

        $canceled = abs((int)$request->request->get('canceled', 2));
        if (!(in_array($canceled, AppConstantes::FILE_UPDATE_STATUS, true)))
            $isValid = false;

        return $isValid;
    }


    /**
     * @param $mediaObject
     *
     * @return bool
     */
    public function checkFileExtension($mediaObject): bool
    {
        $fileExtension = strtoupper(pathinfo($mediaObject->getKybFile()->getClientOriginalName(), PATHINFO_EXTENSION));

        if (!in_array($fileExtension, AppConstantes::ACCEPTED_EXTENSIONS)) {
            return false;
        }
        return true;
    }


    /**
     * @param Request $request
     * @param File $uploadedFile
     * @param UserCsb $user
     *
     * @return Document object
     */
    public function makeEntity(Request $request, File $uploadedFile, UserCsb $user): Document
    {
        $object = new Document();

        $object->setLevelDoc((int)$request->request->get('levelDoc', null))
            ->setDocumentTypeId((int)$request->request->get('documentTypeId', null))
            ->setKybFile($uploadedFile)
            ->setFileSize($uploadedFile->getSize())
            ->setFileStatus(AppConstantes::FILE_STATUS['NONE'])
            ->setUserId($user->getId())
            ->setUserCsb($user)
            ->setTreezorStatusValidation(AppConstantes::FILE_STATUS['NONE']);

        return $object;
    }


    /**
     * @param Request $request
     * @param $uploadedFile
     * @param Document $document
     * @param UserCsb $user
     * @param int $canceled
     *
     * @return void
     */
    public function updateEntity(Request $request, $uploadedFile, Document $document, UserCsb $user, int $canceled): void
    {
        $document->setLevelDoc((int)$request->request->get('levelDoc'))
            ->setDocumentTypeId((int)$request->request->get('documentTypeId'))
            ->setKybFile($uploadedFile)
            ->setFileSize($uploadedFile->getSize())
            ->setUserId($user->getId())
            ->setUserCsb($user)
            ->setUpdatedAt(new \DateTime('now'));

        $canceled === 0
            ? $document->setFileStatus(AppConstantes::FILE_STATUS['NONE'])
            : $document->setFileStatus(AppConstantes::FILE_STATUS['CANCELED']);
    }

    /**
     * @param Document $document
     * @return bool
     */
    public function userAbleToEdit(Document $document): bool
    {
        return $document->getFileStatus() === AppConstantes::FILE_STATUS['NONE'];
    }
}
