<?php

namespace App\Models\DTOs;

use \Spatie\DataTransferObject\DataTransferObject;

class FileRulesValidation extends DataTransferObject
{
    public string $field;

    public string $typeValidation;

    public int $maxKilobytes;

    public bool $required;

    public function getArrayValidationRules()
    {
        $rules = [];
        $rules[] = $this->required ? "required" : "nullable";
        $rules[] = "file";
        $rules[] = $this->typeValidation;
        $rules[] = "max:{$this->maxKilobytes}";
        return $rules;
    }
}
