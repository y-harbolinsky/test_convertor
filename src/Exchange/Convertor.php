<?php declare(strict_types = 1);

namespace App\Exchange;

use App\Exception\ApiBadRequestException;
use App\Manager\RatesManager;
use App\Request\ExchangeModel;

class Convertor implements ConvertorInterface
{
    public function convert(ExchangeModel $model, array $rates): array
    {
        $baseCurrency = $model->getBaseCurrency();
        $targetCurrency = $model->getTargetCurrency();
        $baseAmount = $model->getAmount();

        $targetAmount = $this->calculateAmount($baseCurrency, $targetCurrency, $baseAmount);

        if (!$targetAmount) {
            throw new ApiBadRequestException('Impossible to convert ' . $baseCurrency . ' to ' . $targetCurrency . '.');
        }

        return [
            'baseCurrency' => $baseCurrency,
            'baseAmount' => $baseAmount,
            'targetCurrency' => $targetCurrency,
            'targetAmount' => $targetAmount,
        ];
    }

    private function calculateAmount(string $baseCurrency, string $targetCurrency, float $baseAmount): ?float
    {
        $targetAmount = null;

        if (isset($rates[$baseCurrency][$targetCurrency])) {
            $targetAmount = $baseAmount * $rates[$baseCurrency][$targetCurrency];
        }

        if (isset($rates[$targetCurrency][$baseCurrency])) {
            $targetAmount = $baseAmount / $rates[$targetCurrency][$baseCurrency];
        }

        if (
        !isset($rates[$baseCurrency]) && !isset($rates[$targetCurrency])
        && isset($rates[RatesManager::ECB_SOURCE_CURRENCY][$baseCurrency])
        && isset($rates[RatesManager::ECB_SOURCE_CURRENCY][$targetCurrency])
        ) {
            $amountInEur = $baseAmount * $rates[RatesManager::ECB_SOURCE_CURRENCY][$baseCurrency];
            $targetAmount = $amountInEur / $rates[RatesManager::ECB_SOURCE_CURRENCY][$targetCurrency];
        }

        if (
        !isset($rates[$baseCurrency]) && !isset($rates[$targetCurrency])
            && isset($rates[RatesManager::CBR_SOURCE_CURRENCY][$baseCurrency])
            && isset($rates[RatesManager::CBR_SOURCE_CURRENCY][$targetCurrency])
        ) {
            $amountInRub = $baseAmount * $rates[RatesManager::CBR_SOURCE_CURRENCY][$baseCurrency];
            $targetAmount = $amountInRub / $rates[RatesManager::CBR_SOURCE_CURRENCY][$targetCurrency];
        }

        if ($baseCurrency === $targetCurrency) {
            $targetAmount = $baseAmount;
        }
        // Check EUR -> UAH
        //         if (
        //             !$targetAmount &&
        //             (isset($rates[$baseCurrency]) || !isset($rates[$baseCurrency][$targetCurrency]))
        //         ) {
        //             if (isset($rates[RatesManager::ECB_SOURCE_CURRENCY][$targetCurrency])) {
        //                 $amountInEur = $baseAmount * $rates[RatesManager::ECB_SOURCE_CURRENCY][$baseCurrency];
        //             }
        //
        //             if (isset($rates[RatesManager::CBR_SOURCE_CURRENCY][$targetCurrency])) {
        //                 $amountInRub = $baseAmount * $rates[RatesManager::CBR_SOURCE_CURRENCY][$baseCurrency];
        //                 $targetAmount = $amountInRub / $rates[RatesManager::CBR_SOURCE_CURRENCY][$targetCurrency];
        //                 dd($amountInRub);
        //             }
        //         }

        return $targetAmount;
    }
}
