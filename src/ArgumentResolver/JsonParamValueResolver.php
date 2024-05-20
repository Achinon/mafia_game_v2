<?php

namespace App\ArgumentResolver;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use App\Utils\Utils;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class JsonParamValueResolver implements ValueResolverInterface
{
    public function __construct()
    {
    }

    /**
     * @inheritDoc
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if (!$this->supports($argument)) return [];
        $paramToGet = $argument->getName();
        $type = $argument->getType();
        $nullable = $argument->isNullable();

        $data = $request->getContent();
        $JSON = json_decode($data, 1);

        $value = null;
        if(isset($JSON[$paramToGet])){
            $value = $JSON[$paramToGet];
        }

        if (!$value && $argument->hasDefaultValue()) {
            $value = $argument->getDefaultValue();
        }

        if (!$nullable && !$value) {
            throw new BadRequestHttpException("Request query parameter '" . $paramToGet . "' is required, but not set.");
        }

        //must return  a `yield` clause
        yield match ($type) {
            'int' => $value ? (int)$value : 0,
            'float' => $value ? (float)$value : .0,
            'bool' => (bool)$value,
            'string' => $value ? (string)$value : ($nullable ? null : ''),
            null => null
        };
    }

    public function supports(ArgumentMetadata $argument): bool
    {
        $attrs = $argument->getAttributes(JsonParam::class);
        return count($attrs) > 0;
    }
}