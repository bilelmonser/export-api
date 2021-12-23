<?php

namespace App\Service\App;

class FileEncoder
{

    /**
     * @param string $filePath
     * 
     * @return string|null
     */
    public function Base64Encoder(string $filePath): ?string
    {
        if(null === $filePath) return null;
        return base64_encode(file_get_contents($filePath));
    }
    
}