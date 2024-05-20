<?php

namespace App\ArgumentResolver;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use App\Utils\Utils;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use App\Entity\Session;
use Doctrine\ORM\EntityManagerInterface;

class FetchEntityValueResolver implements ValueResolverInterface
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    /**
     * @inheritDoc
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if (!$this->supports($argument)) return [];
        $type = $argument->getType();
        /** @var FetchEntity $attr */
        $attr = $argument->getAttributes()[0];

        $repository = $this->em->getRepository($type);

        $criteria = $attr->fetchBy;

        foreach($criteria as $dbKey => $paramKey){
            $value = $request->get($paramKey) ?? null;
            if($value === null){
                $value = json_decode($request->getContent(), 1)[$paramKey] ?? null;
            }
            if(is_null($value)){
                throw new \Error(sprintf('Could not get required key of %s', $paramKey));
            }
            $criteria[$dbKey] = $value;
        }
        yield $repository->findOneBy($criteria);
    }

    public function supports(ArgumentMetadata $argument): bool
    {
        $attrs = $argument->getAttributes(FetchEntity::class);
        return count($attrs) > 0;
    }
}