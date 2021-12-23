<?php

namespace App\Service\Treezor\Auth;

use App\Entity\Treezor\Auth\Token;
use App\Service\App\SerializeService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AuthenticationService
{
    /** @var HttpClientInterface $clientHttp */
    private $clientHttp;

    /** @var SerializeService $serializer */
    private $serializer;

    /** @var string */
    private string $clientId;

    /** @var string */
    private string $clientSecret;

    /** @var string */
    private string $treezorAuthUri;

    public function __construct(HttpClientInterface $clientHttp, SerializeService $serializer, $treezorClientId, $treezorClientSecret, $treezorAuthUri)
    {
        $this->clientHttp       = $clientHttp;
        $this->serializer       = $serializer;
        $this->clientId         = $treezorClientId;
        $this->clientSecret     = $treezorClientSecret;
        $this->treezorAuthUri   = $treezorAuthUri;
    }

    /**
     * @return Token
     */
    public function getAuthToken(): Token
    {

        try {
            $response = $this->clientHttp->request(
                'POST',
                $this->treezorAuthUri,
                [
                    'body' => [
                        "grant_type"    => "client_credentials",
                        "client_id"     => $this->clientId,
                        "client_secret" => $this->clientSecret,
                    ]
                ]
            );
        } catch (\Exception $exception) {
            $data = ['exceptionError' => $exception->getMessage()];
            return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
        }

        return $this->serializer->DeserializeContent($response->getContent(), Token::class);

    }
}