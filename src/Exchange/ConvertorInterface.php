<?php declare(strict_types = 1);

namespace App\Exchange;

use App\Request\ExchangeModel;

interface ConvertorInterface
{
    public function convert(ExchangeModel $model, array $rates): array;
}
