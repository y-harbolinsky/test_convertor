<?php declare(strict_types = 1);

namespace App\ParamConverter;

use App\Request\ExchangeModel;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;

class ExchangeModelConverter implements ParamConverterInterface
{
    public function apply(Request $request, ParamConverter $configuration): bool
    {
        $model = new ExchangeModel(
            $request->get('base'),
            $request->get('target'),
            (float)$request->get('amount')
        );

        $request->attributes->set($configuration->getName(), $model);

        return true;
    }

    public function supports(ParamConverter $configuration): bool
    {
        return ExchangeModel::class === $configuration->getClass();
    }
}
