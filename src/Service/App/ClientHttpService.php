<?php

namespace App\Service\App;

use App\Service\Treezor\Auth\AuthenticationService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class ClientHttpService
{

    /**
     * @var array
     */
    private array $formDataRequestHeaders = [
        'Authorization' => 'Bearer ',
        'Accept' => '*/*',
        'Content-Type' => 'application/x-www-form-urlencoded'
    ];

    /**
     * @var HttpClientInterface $clientHttp
     */
    private HttpClientInterface $clientHttp;

    /**
     * @var AuthenticationService $TreezorAuth
     */
    private AuthenticationService $TreezorAuth;

    /**
     * @var string $treezorDocumentUri
     */
    private string $treezorDocumentUri;

    /**
     * @param HttpClientInterface $clientHttp
     * @param AuthenticationService $TreezorAuth
     * @param $treezorDocumentUri
     */
    public function __construct(HttpClientInterface $clientHttp, AuthenticationService $TreezorAuth, $treezorDocumentUri)
    {
        $this->clientHttp = $clientHttp;
        $this->TreezorAuth = $TreezorAuth;
        $this->treezorDocumentUri = $treezorDocumentUri;
    }


    /**
     * @return string
     */
    private function getTokenInstance(): string
    {
        $activeToken = $this->TreezorAuth->getAuthToken();
        return (string)$activeToken->getAccessToken();
    }

    /**
     * @param array $body
     * @return Response|ResponseInterface
     * @throws TransportExceptionInterface
     */
    public function formDataRequest(array $body)
    {
        if (!$body)
            return new Response('Bad Request', Response::HTTP_BAD_REQUEST);

        $this->formDataRequestHeaders['Authorization'] .= $this->getTokenInstance();

        return $this->clientHttp->request(
            'POST',
            $this->treezorDocumentUri,
            [
                'headers' => $this->formDataRequestHeaders,
                'body' => $body
            ]
        );
    }


    /**
     * @param string $method
     * @param string $url
     * The Api endpoint.
     * @param array $body
     * Array of key => value .
     * @return ResponseInterface
     * @throws TransportExceptionInterface
     */
    public function rawJsonRequest(string $method, string $url, array $body): ResponseInterface
    {
        return $this->clientHttp->request(
            (string)$method,
            (string)$url,
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . (string)$this->getTokenInstance()
                ],
                'body' => $body
            ]
        );
    }
}
