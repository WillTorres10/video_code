<?php

namespace Tests\Traits;

trait TestFileFieldsAndValidations
{
    use TestGetModel;

    public function testFileFieldsAndRulesValidationAreCorrectSetted()
    {
        $class = $this->getModel();
        $rulesSetted = [];
        foreach ($class::getFilesFieldsRules() as $rule) {
            $rulesSetted[] = $rule->field;
        }
        $this->assertEqualsCanonicalizing($class::$fileFields, $rulesSetted);
    }
}
