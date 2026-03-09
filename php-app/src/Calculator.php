<?php

class Calculator {
    private $prices;

    public function __construct($prices = []) {
        $defaults = [
            'arcStandard'  => 0.539,  // R$ 0,539/vCore/hora  ($0,10 × 5,39)
            'arcEnterprise'=> 2.021,  // R$ 2,021/vCore/hora  ($0,375 × 5,39)
            'splaStandard' => 1131.90, // R$ 1.131,90/pack/mês ($210,00 × 5,39)
            'splaEnterprise' => 4611.08, // R$ 4.611,08/pack/mês ($855,47 × 5,39)
            'exchangeRate' => 5.39
        ];
        $this->prices = array_merge($defaults, $prices);
    }

    public function calculate($estimate) {
        $isStandard = $estimate['edition'] === 'Standard';
        $arcPricePerVCoreHour = $isStandard ? $this->prices['arcStandard'] : $this->prices['arcEnterprise'];
        $splaBasePrice = $isStandard ? $this->prices['splaStandard'] : $this->prices['splaEnterprise'];
        
        // Azure ARC PAYG
        $arcBaseCost = $estimate['vCores'] * $arcPricePerVCoreHour * $estimate['hoursPerMonth'];
        $arcTaxAmount = ($arcBaseCost * $estimate['tax']) / 100;
        $arcWithTax = $arcBaseCost + $arcTaxAmount;
        $arcFinalCost = $arcWithTax * (1 + $estimate['markup'] / 100);
        
        // SPLA (packs de 2 cores)
        $packs = ceil($estimate['vCores'] / 2);
        $splaCost = $packs * $splaBasePrice;
        
        // Conversão de moeda (preços internos em BRL; dividir para obter USD)
        $rate = $estimate['currency'] === 'USD' ? 1 / $this->prices['exchangeRate'] : 1;
        
        return [
            'arc' => [
                'baseCostUSD' => $arcBaseCost,
                'baseCost' => $arcBaseCost * $rate,
                'taxAmount' => $arcTaxAmount * $rate,
                'finalMonthly' => $arcFinalCost * $rate,
                'finalAnnual' => $arcFinalCost * 12 * $rate,
                'hourly' => ($arcFinalCost / $estimate['hoursPerMonth']) * $rate
            ],
            'spla' => [
                'monthly' => $splaCost * $rate,
                'annual' => $splaCost * 12 * $rate,
                'packs' => $packs
            ],
            'savings' => [
                'annual' => ($splaCost * 12 - $arcFinalCost * 12) * $rate,
                'percentage' => $splaCost > 0 ? (($splaCost * 12 - $arcFinalCost * 12) / ($splaCost * 12)) * 100 : 0
            ]
        ];
    }
}
