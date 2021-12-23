<?php

namespace App\Controller\Api\Sage;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Compenent\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use App\Service\SageClickUpService;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\UserRepository;

class UserController extends AbstractController
{
	private $sageService;
    public function __contruct(){
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
    ){
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

}
