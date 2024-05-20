<?php

namespace App\ArgumentResolver;

use App\Utils\Utils;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Serializer\SerializerInterface;

class BodyValueResolver implements ValueResolverInterface
{
    public function __construct(private SerializerInterface $serializer)
    {
    }

    /**
     * @inheritDoc
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if (!$this->supports($request, $argument)) return [];
        $type = $argument->getType();
        $format = $request->getContentTypeFormat() ?? 'json';

        //read request body
        $content = $request->getContent();
        $data = $this->serializer->deserialize($content, $type, $format);

        yield $data;
    }

    /**
     * @inheritDoc
     */
    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        $attrs = $argument->getAttributes(Body::class);
        return count($attrs) > 0;
    }
}