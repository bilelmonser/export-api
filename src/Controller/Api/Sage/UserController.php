<?php

namespace App\Controller\Api\Sage;

use App\Service\App\ToolsService;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use App\Service\Sage\ClickUpService;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\UserRepository;
use App\Service\Sage\SageClickUpOfflineService;

class UserController extends AbstractController
{

    /**
     * @Route("/sage/user/createUser", name="users_post", methods={"POST"})
     */
    public function createUser(
        EntityManagerInterface $em,
        Request $request,
        SerializerInterface $serializer,
        UserRepository $userRepository,
        ValidatorInterface $validator,
        ToolsService $toolsService,
//        UserPasswordEncoderInterface $passwordHasher
        UserPasswordHasherInterface $passwordHasher
    ) {
        // deserialize the json
        try {

            if (null === $request->getContent()){
                return $this->json(["success" => false, Response::HTTP_BAD_REQUEST]);
            }
            $data = json_decode($request->getContent(),true);
            $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(['email'=>$data['email']]);
            if($user !== null){
                return $this->json(["success" => false, "message"=> "Cette utilisateur existe déjà!"], Response::HTTP_BAD_REQUEST);
            }
            $user = new User();
            $toolsService->setterApi($user,$data);
            $hashedPassword = $passwordHasher->hashPassword(
                $user,
                $data['password']
            );
            $user->setPassword($hashedPassword);
            $user->setRoles(["ROLE_USER"]);
            $errors = $validator->validate($user);
            if (count($errors) > 0) {
                $json = $serializer->serialize($errors, 'json', array_merge([
                    'json_encode_options' => JsonResponse::DEFAULT_ENCODING_OPTIONS,
                ], []));
                return $this->json($json, Response::HTTP_BAD_REQUEST);
            }

            $em->persist($user);
            // todo voir avec Saleh et Bilel le nouveau algorithme
            //$sageMmodel =    $serializer->deserialize($request->getContent(), User::class, 'json');
            //$em->persist($sageModel);
            $em->flush();

        } catch (NotEncodableValueException $exception) {
            return $this->json(["success" => false, "message"=>$exception->getMessage()],Response::HTTP_BAD_REQUEST);
        }
        return $this->json(["success" => true], Response::HTTP_CREATED);
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
