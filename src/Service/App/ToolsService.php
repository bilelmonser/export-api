<?php

namespace App\Service\App;

use DateTime;

class ToolsService
{
    //TODO : to check
    /**
     * @param $object
     * @param array $data
     * @return mixed
     */
    public function setterApi(&$object, array $data)
    {
        foreach ($data as $index => $value) {
            $setter = "set" . ucwords($index);
            if (method_exists($object, $setter)) {
                $object->{$setter}($value);
            }else{
                //TODO: log files
            }
        }
    }

    /**
     * @param $errors
     * @return array
     */
    public function errorsFormat ( $errors) {
        $errorList = array();
        foreach ($errors as $error) {
            $errorList[$error->getPropertyPath()] = $error->getMessage();
        }
        return $errorList;
    }

    /**
     * @return string
     */
    public function getNowAsString(): string
    {
        $date = new DateTime('now');
        return $date->format('Y-m-d H:i:s');
    }
}