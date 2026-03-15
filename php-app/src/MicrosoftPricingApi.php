<?php
declare(strict_types=1);

namespace AzureMigration;

/**
 * Consulta a API pública de preços da Microsoft (prices.azure.com).
 * Usa curl_multi para disparar todas as requisições em PARALELO,
 * reduzindo o tempo de N × latência para ~1 × latência.
 * Cache de resultados na sessão para evitar re-consultas.
 */
class MicrosoftPricingApi
{
    private const API_URL   = 'https://prices.azure.com/api/retail/prices';
    private const API_VER   = '2023-01-01-preview';
    private const CACHE_KEY = 'financialPriceCache';
    private const TIMEOUT   = 8;   // segundos por requisição
    private const BATCH     = 20;  // máximo de conexões simultâneas

    public function __construct()
    {
        if (!isset($_SESSION[self::CACHE_KEY])) {
            $_SESSION[self::CACHE_KEY] = [];
        }
    }

    /**
     * Consulta a API para uma lista de specs (meterId + productId + resourceLocation + unitOfMeasure).
     *
     * Estratégia:
     *   Pass 1 — query ampla: meterId + priceType=Consumption  → obtém TODOS os itens do meterId
     *            Itera por todos os resultados e pontua cada um contra os campos do CSV.
     *            Escolhe o item com maior score (melhor match exato).
     *   Pass 2 — se Pass 1 retornou zero itens: repete sem filtro priceType (captura Reserved, etc.)
     *
     * Pontuação por item:
     *   skuId bate           → +4 (mais importante — identifica produto+SKU exato)
     *   armRegionName bate   → +3
     *   unitOfMeasure bate   → +3
     *   meterName bate       → +2
     *   priceType=Consumption → +1
     *
     * @param  array[] $specs  Cada item: ['key'=>string, 'meterId'=>string, 'productId'=>string,
     *                                     'resourceLocation'=>string, 'unitOfMeasure'=>string]
     * @return array<string, array|null>
     */
    public function getPricesBySpecs(array $specs): array
    {
        $results = [];

        // Separa cache hits dos que precisam ir à API
        $toFetch = [];
        foreach ($specs as $spec) {
            $key = $spec['key'];
            if (array_key_exists($key, $_SESSION[self::CACHE_KEY])) {
                $results[$key] = $_SESSION[self::CACHE_KEY][$key];
            } else {
                $toFetch[] = $spec;
                $results[$key] = null;
            }
        }

        if (empty($toFetch)) {
            return $results;
        }

        // Pass 1: meterId + priceType=Consumption — query ampla, todos os itens do meterId
        $fetched     = $this->multiGetSpecs($toFetch, false, true);
        $matchLevels = [];
        foreach ($fetched as $key => $items) {
            if (!empty($items)) { $matchLevels[$key] = 1; }
        }

        // Pass 2: misses → sem filtro priceType (captura Reserved, DevTest, etc.)
        $retry2 = array_values(array_filter($toFetch, fn($s) => empty($fetched[$s['key']])));
        if (!empty($retry2)) {
            foreach ($this->multiGetSpecs($retry2, false, false) as $key => $items) {
                if (!empty($items)) { $fetched[$key] = $items; $matchLevels[$key] = 2; }
            }
        }

        // Processa resultados: pontua TODOS os itens e escolhe o melhor match
        foreach ($toFetch as $spec) {
            $key   = $spec['key'];
            $level = $matchLevels[$key] ?? null;
            $items = $fetched[$key] ?? [];

            if (empty($items)) {
                $_SESSION[self::CACHE_KEY][$key] = null;
                $results[$key] = null;
                continue;
            }

            // Seleciona o item com maior pontuação de match contra os campos do CSV
            $item      = $this->scoreBestItem($items, $spec);
            $score     = $item['_score'];
            $maxScore  = $item['_maxScore'];
            unset($item['_score'], $item['_maxScore']);

            $data = [
                'unitPrice'     => (float)($item['retailPrice'] ?? $item['unitPrice'] ?? 0),
                'currencyCode'  => $item['currencyCode']  ?? 'USD',
                'unitOfMeasure' => $item['unitOfMeasure'] ?? '',
                'productName'   => $item['productName']   ?? '',
                'productId'     => $item['productId']     ?? '',
                'skuId'         => $item['skuId']         ?? '',
                'meterName'     => $item['meterName']     ?? '',
                'serviceName'   => $item['serviceName']   ?? '',
                'serviceFamily' => $item['serviceFamily'] ?? '',
                'priceType'     => $item['priceType']     ?? '',
                'armRegion'     => $item['armRegionName'] ?? '',
                'location'      => $item['location']      ?? '',
                'matchLevel'    => $level,
                'matchScore'    => $score,
                'matchMaxScore' => $maxScore,
                'filterUsed'    => $this->buildSpecFilter($spec, false, $level === 1),
            ];

            $_SESSION[self::CACHE_KEY][$key] = $data;
            $results[$key] = $data;
        }

        return $results;
    }

    /**
     * Pontua cada item retornado pela API contra os campos do spec do CSV.
     * Retorna o item com maior score, com campos extras _score e _maxScore injetados.
     *
     * Critérios (quanto mais específico, mais peso):
     *   skuId bate           → +4
     *   armRegionName bate   → +3
     *   unitOfMeasure bate   → +3
     *   meterName bate       → +2
     *   priceType=Consumption → +1
     */
    private function scoreBestItem(array $items, array $spec): array
    {
        $csvSkuId  = $this->csvProductIdToSkuId($spec['productId'] ?? '');
        $csvRegion = $this->normalizeRegion($spec['resourceLocation'] ?? '');
        $csvUom    = strtolower(trim($spec['unitOfMeasure'] ?? ''));
        $csvMeter  = strtolower(trim($spec['meterName']    ?? ''));  // passado via spec se disponível

        $best      = null;
        $bestScore = -1;
        $maxScore  = 0;

        // Calcula máximo possível para indicar completude do match
        if ($csvSkuId  !== '') $maxScore += 4;
        if ($csvRegion !== '') $maxScore += 3;
        if ($csvUom    !== '') $maxScore += 3;
        if ($csvMeter  !== '') $maxScore += 2;
        $maxScore += 1; // priceType

        foreach ($items as $item) {
            $score = 0;

            if ($csvSkuId !== '' && ($item['skuId'] ?? '') === $csvSkuId) {
                $score += 4;
            }
            if ($csvRegion !== '' && $this->normalizeRegion($item['armRegionName'] ?? '') === $csvRegion) {
                $score += 3;
            }
            if ($csvUom !== '' && strtolower($item['unitOfMeasure'] ?? '') === $csvUom) {
                $score += 3;
            }
            if ($csvMeter !== '' && strtolower($item['meterName'] ?? '') === $csvMeter) {
                $score += 2;
            }
            if (($item['priceType'] ?? '') === 'Consumption') {
                $score += 1;
            }

            if ($score > $bestScore) {
                $bestScore = $score;
                $best      = $item;
            }
        }

        $best['_score']    = $bestScore;
        $best['_maxScore'] = $maxScore;
        return $best;
    }

    /**
     * Retrocompatível: aceita array de meterIds simples (sem campos extras).
     *
     * @param  string[] $meterIds
     * @return array<string, array|null>
     */
    public function getPricesBatch(array $meterIds): array
    {
        $specs = array_map(fn($id) => [
            'key'              => strtolower($id) . '|||',
            'meterId'          => $id,
            'productId'        => '',
            'resourceLocation' => '',
            'unitOfMeasure'    => '',
        ], $meterIds);

        $raw = $this->getPricesBySpecs($specs);

        $out = [];
        foreach ($specs as $s) {
            $out[$s['meterId']] = $raw[$s['key']] ?? null;
        }
        return $out;
    }

    /**
     * Dispara requisições HTTP simultâneas para uma lista de specs.
     *
     * @param  array[]  $specs
     * @param  bool     $withExtras      Inclui productId, armRegionName, unitOfMeasure no filtro
     * @param  bool     $withConsumption Inclui priceType eq 'Consumption'
     * @return array<string, array>      Indexado pela key do spec
     */
    private function multiGetSpecs(array $specs, bool $withExtras, bool $withConsumption): array
    {
        if (!function_exists('curl_multi_init')) {
            $out = [];
            foreach ($specs as $spec) {
                $out[$spec['key']] = $this->singleGetSpec($spec, $withExtras, $withConsumption);
            }
            return $out;
        }

        $out     = array_fill_keys(array_column($specs, 'key'), []);
        $batches = array_chunk($specs, self::BATCH);

        foreach ($batches as $batch) {
            $mh      = curl_multi_init();
            $handles = [];

            foreach ($batch as $spec) {
                $filter = $this->buildSpecFilter($spec, $withExtras, $withConsumption);
                $url    = self::API_URL . '?api-version=' . self::API_VER
                        . '&$filter=' . rawurlencode($filter);

                $ch = curl_init($url);
                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT        => self::TIMEOUT,
                    CURLOPT_CONNECTTIMEOUT => 5,
                    CURLOPT_HTTPHEADER     => ['Accept: application/json'],
                    CURLOPT_SSL_VERIFYPEER => true,
                    CURLOPT_USERAGENT      => 'TdSynnex-ArcCalculator/1.0',
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_ENCODING       => 'gzip',
                ]);
                curl_multi_add_handle($mh, $ch);
                $handles[$spec['key']] = $ch;
            }

            $running = null;
            do {
                curl_multi_exec($mh, $running);
                if ($running) { curl_multi_select($mh, 0.5); }
            } while ($running > 0);

            foreach ($handles as $key => $ch) {
                $body = curl_multi_getcontent($ch);
                $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_multi_remove_handle($mh, $ch);
                curl_close($ch);

                if ($code === 200 && $body) {
                    $decoded = json_decode($body, true);
                    if (is_array($decoded) && !empty($decoded['Items'])) {
                        $out[$key] = $decoded['Items'];
                    }
                }
            }

            curl_multi_close($mh);
        }

        return $out;
    }

    /** Fallback sequencial caso curl_multi não esteja disponível. */
    private function singleGetSpec(array $spec, bool $withExtras, bool $withConsumption): array
    {
        if (!function_exists('curl_init')) {
            return [];
        }
        $filter = $this->buildSpecFilter($spec, $withExtras, $withConsumption);
        $url    = self::API_URL . '?api-version=' . self::API_VER
                . '&$filter=' . rawurlencode($filter);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => self::TIMEOUT,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_HTTPHEADER     => ['Accept: application/json'],
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_USERAGENT      => 'TdSynnex-ArcCalculator/1.0',
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_ENCODING       => 'gzip',
        ]);
        $body = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($code !== 200 || !$body) { return []; }
        $decoded = json_decode($body, true);
        return (is_array($decoded) && isset($decoded['Items'])) ? $decoded['Items'] : [];
    }

    /**
     * Constrói o filtro OData para uma spec.
     * Pass 1 ($withExtras=true,  $withConsumption=true):  meterId + skuId + armRegionName + unitOfMeasure + priceType=Consumption
     * Pass 2 ($withExtras=false, $withConsumption=true):  meterId + priceType=Consumption
     * Pass 3 ($withExtras=false, $withConsumption=false): meterId
     *
     * O productId do CSV vem como concatenação (ex: "DZH318Z0BXWC0006").
     * A API espera skuId no formato "DZH318Z0BXWC/0006" (últimos 4 chars = sku part).
     */
    private function buildSpecFilter(array $spec, bool $withExtras, bool $withConsumption): string
    {
        $parts = ["meterId eq '{$spec['meterId']}'"];

        if ($withExtras) {
            // Converte productId do CSV para formato skuId da API (XXXXXXXXXX/YYYY)
            $skuId = $this->csvProductIdToSkuId($spec['productId'] ?? '');
            if ($skuId !== '') {
                $parts[] = "skuId eq '{$skuId}'";
            }
            $region = $this->normalizeRegion($spec['resourceLocation']);
            if ($region !== '') {
                $parts[] = "armRegionName eq '{$region}'";
            }
            if (!empty($spec['unitOfMeasure'])) {
                $parts[] = "unitOfMeasure eq '{$spec['unitOfMeasure']}'";
            }
        }

        if ($withConsumption) {
            $parts[] = "priceType eq 'Consumption'";
        }

        return implode(' and ', $parts);
    }

    /**
     * Converte o productId do formato CSV (concatenado: "DZH318Z0BXWC0006")
     * para o formato skuId da API ("DZH318Z0BXWC/0006" — últimos 4 chars = SKU part).
     * Se já tiver "/" ou for curto demais, retorna como está.
     */
    private function csvProductIdToSkuId(string $csvId): string
    {
        if ($csvId === '') {
            return '';
        }
        if (str_contains($csvId, '/')) {
            return $csvId; // já está no formato da API
        }
        if (strlen($csvId) >= 5) {
            return substr($csvId, 0, -4) . '/' . substr($csvId, -4);
        }
        return $csvId;
    }

    /**
     * Normaliza nome de região Azure para o formato armRegionName.
     * "East US" → "eastus", "Brazil South" → "brazilsouth"
     */
    private function normalizeRegion(string $loc): string
    {
        return strtolower(str_replace([' ', '-', '_'], '', $loc));
    }

    public function getCacheCount(): int
    {
        return count($_SESSION[self::CACHE_KEY] ?? []);
    }

    public function clearCache(): void
    {
        $_SESSION[self::CACHE_KEY] = [];
    }
}
