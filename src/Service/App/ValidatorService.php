<?php

namespace App\Service\App;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;

class ValidatorService
{
    private ValidatorInterface  $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    /**
     * @param string $email
     * 
     * @return bool
     */
    public function emailValidation(string $email): bool
    {
        $emailConstraint = new Assert\Email();
        $isValid = true;

        $errors = $this->validator->validate(
            $email,
            $emailConstraint
        );

        if (0 !== count($errors)) {
            $isValid = false;
        }
        return $isValid;
    }

    public function requestFieldValidation(Request $request, array $fieldList)
    {
        $isValid = true;
        $arrayContent = json_decode($request->getContent(), true);

        foreach ($fieldList as $name) {
            if (!isset($arrayContent[$name])) {
                $isValid = false;
                break;
            }
        }
        return $isValid;
    }
}
