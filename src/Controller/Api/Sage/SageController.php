<?php

namespace App\Controller\Api\Sage;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use App\Service\SageClickUpService;

class SageController extends AbstractController
{
	private $sageService;
    public function __construct(SageClickUpService $sageService){
		$this->sageService=$sageService;        
	}
	public function getSageService(){
		return $this->sageService;
	}
}
