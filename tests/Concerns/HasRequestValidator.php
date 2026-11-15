<?php

declare(strict_types=1);

namespace Tests\Concerns;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Routing\Route;
use Illuminate\Translation\ArrayLoader;
use Illuminate\Translation\Translator;
use Illuminate\Validation\Factory;

trait HasRequestValidator
{
    protected function makeRequestValidator(FormRequest $formRequest, array $data): Validator
    {
        $factory = new Factory(new Translator(new ArrayLoader(), 'en'));

        return $factory->make(
            $data,
            $formRequest->rules(),
            $formRequest->messages(),
            $formRequest->attributes()
        );
    }

    protected function bindRequestContext(FormRequest $formRequest, array $routeParameters = []): FormRequest
    {
        $route = new Route('GET', '/', []);
        $route->bind($formRequest);

        foreach ($routeParameters as $name => $value) {
            $route->setParameter($name, $value);
        }

        $formRequest->setRouteResolver(fn() => $route);

        return $formRequest;
    }
}
