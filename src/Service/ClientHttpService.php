<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;

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
    public function execute($url,$method,$params,$token,$typeContent=1)
    {
        $tmp_dir = ini_get('upload_tmp_dir') ? ini_get('upload_tmp_dir') : sys_get_temp_dir();
        $paramsBody=[];
        $result=[];
        switch ($typeContent) {
            case 1:
                $paramsBody["json"]=$params; 
            break;
            case 2:
                if((isset($params["attachment"])) && !empty($params["attachment"])){
                    $params['attachment'] = DataPart::fromPath($params["attachment"]);
                    
                }
                $formData = new FormDataPart($params);
                $paramsBody["headers"]=$formData->getPreparedHeaders()->toArray(); 
                $paramsBody["body"]=$formData->bodyToIterable();
                
            break;
            default:
                $paramsBody=[];
        };

        if(!empty($token)){
            $paramsBody["auth_bearer"]=$token;
        }
        try {
            $response = $this->client->request(
                $method,
                $url,
                $paramsBody
                
            );
        }
        catch(Exception $e) {
            $result["content"]="error !";
            $result["status"]=500;
            return $result;
        }
        
        $statusCode = $response->getStatusCode();
        $content = $response->getContent();
        $statusOk=['200','201','202','204','205'];
        $statusNotFound=['401','402','403','404','415'];
        if(!empty($content)){
            $contentType = $response->getHeaders()['content-type'][0];
        }else{
            $content=null;
        }
        if($statusCode === 400){
            $result["content"]="error 400";
            $result["status"]=$statusCode;
            return $result; 
        }
        $result["content"]=$content;
        $result["status"]=$statusCode;
        return $result;        
    }

}