<?php 
namespace App\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class FileUploader
{
    private $targetDirectory;
    private $slugger;
    private $validExtension=["pdf","jpg","tif"];
    private $maxFileSize="25000000";

    public function __construct($targetDirectory, SluggerInterface $slugger)
    {
        $this->targetDirectory = $targetDirectory;
        $this->slugger = $slugger;
    }

    public function upload(UploadedFile $file)
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $fileName = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();
        $validateFile=$this->validateFile($file);
        if($validateFile === false){
            return false;
        }
        try {
            $file->move($this->getTargetDirectory(), $fileName);
        } catch (FileException $e) {
        }
        return $fileName;
    }

    public function getTargetDirectory()
    {
        return $this->targetDirectory;
    }

    public function validateFile(UploadedFile $file){
        $errorFile=[];
        if(!in_array($file->getClientOriginalExtension(),$this->validExtension)){
            $errorFile["errorExtension"]="Extension n'est pas valide";
        }        
        if($file->getSize() > $this->maxFileSize){
            $errorFile["errorSize"]="La taille de fichier dépasse le maximum";
        }
        $response["status"]=true;
        if(!empty($errorFile)){
            $response["status"]=false;
            $response["message"]=$$response["status"]=false;;
        }
    }
}