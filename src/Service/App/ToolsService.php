<?php

namespace App\Service\App;

use DateTime;
use Psr\Log\LoggerInterface;
use ReflectionClass;
use ReflectionException;
use Symfony\Component\Filesystem\Filesystem;

class ToolsService
{
    /** @param LoggerInterface $logger */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function convertStrToDate($str)
    {
        return DateTime::createFromFormat("Y-m-d", $str);
    }

    public function setterApi(&$object, array $data)
    {
        try {
            $schema = $this->getClassPropertiesType(get_class($object));

            foreach ($data as $index => $value) {
                $setter = "set" . $this->toCamelCase($index);
                if (method_exists($object, $setter)) {
// TODO a developpé plus
                    if (isset($schema[$index])) {
                        switch ($schema[$index]) {
                            case "int":
                                $object->{$setter}(intval($value));
                                break;
                            case "float":
                                $object->{$setter}(floatval($value));
                                break;
                            case "DateTime":
                                $object->{$setter}($this->convertStrToDate($value));
                                break;
                            default:
                                $object->{$setter}($value);
                        }
                    } else {
                        $object->{$setter}($value);
                    }

                } else {
                    $this->logger->warning("Start");
                    $this->logger->warning("Entity : " . get_class($object));
                    $this->logger->warning("index : " . $index);
                    $this->logger->warning("setter : " . $setter);
                    $this->logger->warning("End");
                }
            }
        } catch (ReflectionException $e) {
            $this->logger->error("Start");
            $this->logger->error("Exception in : " . get_class($this));
            $this->logger->error("Error message : " . $e->getMessage());
            $this->logger->error("End");
        }
    }

    /**
     * @param $string
     * @return string
     */
    private function toCamelCase($string)
    {
        $string = str_replace('-', ' ', $string);
        $string = str_replace('_', ' ', $string);
        $string = ucwords(strtolower($string));
        $string = str_replace(' ', '', $string);

        return $string;
    }

    /**
     * @param $errors
     * @return array
     */
    public function errorsFormat($errors): array
    {
        $errorList = array();
        foreach ($errors as $error) {
            $errorList[$error->getPropertyPath()] = $error->getMessage();
        }
        return $errorList;
    }

    /**
     * @return array
     * @throws ReflectionException
     */
    public function getClassPropertiesType(string $className): array
    {
        $reflection = new ReflectionClass($className);
        $properties = $reflection->getProperties();
        $classDescription = array();
        foreach ($properties as $property) {
            $propertyType = $property->getType();
            if ($propertyType) {
                $classDescription [$property->getName()] = $propertyType->getName();
            }
        }
        return $classDescription;
    }

    /**
     * @param Filesystem $fsObject
     * @param string $dirName
     * @return string
     */
    private function makeADirectory(Filesystem $fsObject, string $dirName): string
    {

        $old = umask(0);
        $fsObject->mkdir($dirName, 0777);
        $fsObject->chown($dirName, "www-data");
        $fsObject->chgrp($dirName, "www-data");
        umask($old);

        return $dirName;
    }

}