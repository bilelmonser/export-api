<?php

namespace App\Controller;

use App\Entity\Csb\Constantes\AppConstantes;
use App\Entity\Csb\UserCsb;
use App\Service\App\ToolsService;
use App\Service\App\ValidatorService;
use App\Service\Csb\Auth\CsbAuthService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/auth-user/add", name="api_auth-user_add", methods={"POST"})
 * This route is protected, only FACNOTE teams can access , we don't need to personalize UI message !
 */
class CreateAuthUserController extends AbstractController
{
    /**
     * @param Request $request
     * @param ValidatorService $validatorService
     * @param CsbAuthService $serviceAuth
     * @return Response
     */
    public function __invoke(
        Request $request,
        ValidatorService $validatorService,
        ToolsService $tools,
        CsbAuthService $serviceAuth): Response
    {
        if (null == $request->getContent()) {
            return $this->json('Bad request.', Response::HTTP_BAD_REQUEST);
        }
        if (!$validatorService->requestFieldValidation($request, ['email', 'roles', 'password'])) {
            return $this->json('Bad request .', Response::HTTP_BAD_REQUEST);
        }

        /** @var EntityManagerInterface $em */
        $em = $this->getDoctrine()->getManager();
        $arrayContent = json_decode($request->getContent(), true);

        if (!$serviceAuth->checkEmailUniqueness($arrayContent['email'], $em)) {
            return $this->json('Email already in use.', Response::HTTP_NOT_ACCEPTABLE);
        }

        $userTypeId = 0;

        // if not assigned, the user is a FACNOTE teams
        if (isset($arrayContent['userTypeId'])) {
            $userTypeId = abs((int)$arrayContent['userTypeId']);

            if ($userTypeId !== 0 && !(in_array($userTypeId, AppConstantes::TYPE_DOSSIER))) {
                return $this->json('User type not supported.', Response::HTTP_BAD_REQUEST);
            }
        }

        //TODO : validation UserCsb and AuthUser entity's before insert

        [$errors, $authUser] = $serviceAuth->setAuthUser($arrayContent['email'], $arrayContent['password'], $userTypeId);
        if ($errors->count()) {
            return $this->json(["errors" => $tools->errorsFormat($errors)], Response::HTTP_BAD_REQUEST);
        }

        try {
            $em->persist($authUser);
            $em->flush();
        } catch (\Exception $e) {
            return $this->json('An error occurred, please try again later.', Response::HTTP_NOT_ACCEPTABLE);
        }

        if ($userTypeId !== 0) {
            $user = new UserCsb();
            $user->setAuthUser($authUser)
                ->setEmail($authUser->getEmail())
                ->setUserTypeId($userTypeId);

            try {
                $em->persist($user);
                $em->flush();
            } catch (\Exception $e) {
                return $this->json('An error occurred, please try again later.', Response::HTTP_NOT_ACCEPTABLE);
            }
        }
        return $this->json('User created successfully.', Response::HTTP_CREATED);
    }
}
