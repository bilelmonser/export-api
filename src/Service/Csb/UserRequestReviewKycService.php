<?php

namespace App\Service\Csb;

use App\Entity\Csb\Constantes\AppConstantes;
use App\Entity\Csb\Document\DetailsRequestReview;
use App\Entity\Csb\Document\Document;
use App\Entity\Csb\Document\RequestReview;
use App\Entity\Csb\UserCsb;
use App\Entity\Treezor\Constantes\TreezorConstantes;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserRequestReviewKycService
{

    /**
     * @param UserCsb $userCsb
     * @param ValidatorInterface $validator
     * @return object
     */
    public function userStrictValidation(UserCsb $userCsb, ValidatorInterface $validator): object
    {
        if (TreezorConstantes::USER_TYPE_ID['KYC'] == $userCsb->getUserTypeId()) {
            return $validator->validate($userCsb, null, ['Default', 'kyc:validation:mandatory']);
        } else {
            return $validator->validate($userCsb, null, ['Default', 'validation:mandatory', 'validation:complement']);
        }
    }

    /**
     * @param UserCsb $userCsb
     * @param Document $document
     * @param EntityManagerInterface $em
     * @return bool
     */
    public function createRequestReviewDocument(UserCsb $userCsb, Document $document, EntityManagerInterface $em): bool
    {
        $review = new RequestReview();

        $review->setUserCsb($userCsb)
            ->setTypeReview(AppConstantes::REVIEW_TYPE['KYB'])
            ->setStatus(AppConstantes::REVIEW_STATUS['NONE'])
            ->setCreatedAt(new \DateTimeImmutable());

        $detailReview = new DetailsRequestReview();
        $detailReview->setRequestReview($review)
            ->setStatusDocument(AppConstantes::FILE_STATUS['NONE']);

        $document->setFileStatus(AppConstantes::FILE_STATUS['PENDING']);
        try {
            $em->persist($review);
            $em->persist($detailReview);
            $em->persist($document);
            $em->flush();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
