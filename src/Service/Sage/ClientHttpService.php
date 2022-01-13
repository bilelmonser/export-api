<?php

namespace App\Service\Sage;

use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;
use App\Service\SageClickUpService;

class ClientHttpService
{
    private $client;
    /**
     * ClientHttpService constructor.
     *
     */
    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }
    /**
     * Execute Api  function
     *
     * @param string $url
     * @param string $method
     * @param array $params
     * @param string $token
     * @param integer $typeContent
     * @return void
     */
    public function execute($url, $method, $params, $token, $typeContent = 1)
    {
        $tmp_dir = ini_get('upload_tmp_dir') ? ini_get('upload_tmp_dir') : sys_get_temp_dir();
        $paramsBody = [];
        $result = [];
        switch ($typeContent) {
            case 1:
                $paramsBody["json"] = $params;
                break;
            case 2:
                if ((isset($params["attachment"])) && !empty($params["attachment"])) {
                    $params['attachment'] = DataPart::fromPath($params["attachment"]);
                }
                $formData = new FormDataPart($params);
                $paramsBody["headers"] = $formData->getPreparedHeaders()->toArray();
                $paramsBody["body"] = $formData->bodyToIterable();

                break;
            default:
                $paramsBody = [];
        };

        if (!empty($token)) {
            $paramsBody["auth_bearer"] = $token;
        }

        $response = $this->client->request(
            $method,
            $url,
            $paramsBody

        );
        $statusCode = $response->getStatusCode();
        if ($statusCode == 200 || $statusCode == 201 ||  $statusCode == 204) {
            $content = $response->getContent();
            $result["content"] = $content;
            $result["status"] = $statusCode;
        } else {
            $result["content"] = "error";
            $result["status"] = $statusCode;
        }
        return $result;
    }
}
