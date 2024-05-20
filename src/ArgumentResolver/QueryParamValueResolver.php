<?php

namespace App\ArgumentResolver;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class QueryParamValueResolver implements ValueResolverInterface
{
    public function __construct()
    {
    }

    /**
     * @inheritDoc
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if (!$this->supports($request, $argument)) return [];
        $argumentName = $argument->getName();
        $type = $argument->getType();
        $nullable = $argument->isNullable();

        //read name property from QueryParam
        $attr = $argument->getAttributes(QueryParam::class)[0];// `QueryParam` is not repeatable
        //if name property is not set in `QueryParam`, use the argument name instead.
        $name = $attr->getName() ?? $argumentName;
        $required = $attr->isRequired() ?? false;

        //fetch query name from request
        $value = $request->query->get($name);

        //if default value is set and query param value is not set, use default value instead.
        if (!$value && $argument->hasDefaultValue()) {
            $value = $argument->getDefaultValue();
        }

        if ($required && !$value) {
            throw new \InvalidArgumentException("Request query parameter '" . $name . "' is required, but not set.");
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

    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        $attrs = $argument->getAttributes(QueryParam::class);
        return count($attrs) > 0;
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }
}