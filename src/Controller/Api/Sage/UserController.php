<?php

namespace App\Controller\Api\Sage;

use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use App\Service\Sage\ClickUpService;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\UserRepository;
use App\Service\Sage\SageClickUpOfflineService;

class UserController extends AbstractController
{
    /**
     * @var
     */
    private $sageService;

    public function __contruct()
    {
    }
    /**
     * @Route("/sage/user/createUser", name="users_post", methods={"POST"})
     */
    public function createUser(
        EntityManagerInterface $em,
        Request $request,
        SerializerInterface $serializer,
        UserRepository $userRepository,
        ValidatorInterface $validator
    ) {
        // deserialize the json
        try {
            $user = $serializer->deserialize($request->getContent(), User::class, 'json');
            $user->setRoles(["ROLE_USER"]);
            $sageMmodel =    $serializer->deserialize($request->getContent(), User::class, 'json');
        } catch (NotEncodableValueException $exception) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, 'Invalid Json');
        }

        $errors = $validator->validate($user);

        if (count($errors) > 0) {
            /*
             * Uses a __toString method on the $errors variable which is a
             * ConstraintViolationList object. This gives us a nice string
             * for debugging.
             */
            $json = $serializer->serialize($errors, 'json', array_merge([
                'json_encode_options' => JsonResponse::DEFAULT_ENCODING_OPTIONS,
            ], []));

            return new JsonResponse($json, Response::HTTP_BAD_REQUEST, [], true);
        }

        $em->persist($user);
        $em->persist($sageModel);
        $em->flush();

        return new Response(null, Response::HTTP_CREATED);
    }

    /**
     * @Route("/api/sage/accountancy/check", name="sage_accountancy_practices_check", methods={"POST"})
     */
    public function checkAccountancyPractices(Request $request, SageClickUpOfflineService $sageServiceOffline): JsonResponse
    {
        try {
            $data =  $request->getContent();
            if(null === $data){
                return $this->json(["error"=>"pas de contenu à traité"],Response::HTTP_BAD_REQUEST);
            }
            $params = json_decode($data,true);
            if(!count($params)){
                return $this->json(["error"=>"pas de contenu à traité"],Response::HTTP_BAD_REQUEST);
            }
            $resp = $sageServiceOffline->checkAccountingPractices($params);
            return $this->json($resp["content"]);
        } catch (Exception $e) {
            return $this->json(["error"=>$e->getMessage()],Response::HTTP_BAD_REQUEST);
        }
    }
}
