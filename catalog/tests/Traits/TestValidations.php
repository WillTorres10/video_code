<?php
declare(strict_types=1);

namespace Tests\Traits;

use Illuminate\Testing\TestResponse;
use Illuminate\Support\Facades\Lang;

trait TestValidations
{
    abstract protected function model();
    abstract protected function routeStore();
    abstract protected function routeUpdate();

    protected function assertInvalidationInStoreAction(
        array $data,
        string $rule,
        array $ruleParams = []
    )
    {
        $response = $this->json('POST', $this->routeStore(), $data);
        $fields = array_keys($data);
        $this->assertInvalidationFields($response, $fields, $rule, $ruleParams);
    }
    protected function assertInvalidationInUpdateAction(
        array $data,
        string $rule,
        array $ruleParams = []
    )
    {
        $response = $this->json('PUT', $this->routeUpdate(), $data);
        $fields = array_keys($data);
        $this->assertInvalidationFields($response, $fields, $rule, $ruleParams);
    }

    protected function assertInvalidationStoreActionSpecificFields(
        array $data,
        array $fields,
        string $rule,
        array $ruleParams = []
    )
    {
        $response = $this->json('POST', $this->routeStore(), $data);
        $this->assertInvalidationFields($response, $fields, $rule, $ruleParams);
    }

    protected function assertInvalidationInUpdateActionSpecificFields(
        array $data,
        array $fields,
        string $rule,
        array $ruleParams = []
    )
    {
        $response = $this->json('PUT', $this->routeUpdate(), $data);
        $this->assertInvalidationFields($response, $fields, $rule, $ruleParams);
    }

    protected function assertInvalidationFields(
        TestResponse $response,
        array $fields,
        string $rule,
        array $ruleParams = []
    )
    {
        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors($fields);
        foreach ($fields as $field) {
            $fieldName = str_replace('_', ' ', $field);
            Lang::get("validation.{$rule}", ['attribute' => $fieldName] + $ruleParams);
        }
    }
}
