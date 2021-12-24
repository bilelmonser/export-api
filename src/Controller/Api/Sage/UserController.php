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
use App\Service\SageClickUpOfflineService;

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

    /**
     * @Route("/api/sage/accountancy/check", name="sage_accountancy_practices_check")
     */
    public function checkAccountancyPractices(Request $request,SageClickUpOfflineService $sageServiceOffline)
    {
        $params=[];
        $error=false;
        if(($request->request->get('accountancyPractices')!==null) && !empty($request->request->get('accountancyPractices'))){
            $params["accountancyPractices"]=$request->request->get('accountancyPractices');
        }else{
            $error=true;
        }
        if(($request->request->get('appId')!==null) && !empty($request->request->get('appId'))){
            $params["appId"]=$request->request->get('appId');
        }else{
            $error=true;
        }
        if(($request->request->get('clientId')!==null) && !empty($request->request->get('clientId'))){
            $params["clientId"]=$request->request->get('clientId');
        }else{
            $error=true;
        }
        if(($request->request->get('clientSecret')!==null) && !empty($request->request->get('clientSecret'))){
            $params["clientSecret"]=$request->request->get('clientSecret');
        }else{
            $error=true;
        }
        $response = new Response();
        $resp="";
        if($error){
            $response->setStatusCode(400);  
            $resp="";
        }else{
            $response->setStatusCode(200);  
            $resp=$sageServiceOffline->checkAccountingPractices($params);
            $resp=$resp["content"];
        }
        $response->setContent($resp);        
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }	
}
