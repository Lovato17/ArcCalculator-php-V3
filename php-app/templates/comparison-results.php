<?php
/**
 * Visualização de Resultados Comparativos
 */

if (!isset($comparisonResults) || empty($comparisonResults)) {
    return;
}
?>

<div class="comparison-results mt-8 mb-8">
    <!-- Título da Seção -->
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-slate-900 flex items-center gap-3">
            <div class="p-2 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow-md">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-white">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                </svg>
            </div>
            Comparativo de Modelos de Licenciamento
        </h2>
    </div>
    
    <!-- Resumo Executivo -->
    <div class="executive-summary bg-gradient-to-br from-blue-600 via-blue-700 to-purple-700 rounded-xl p-8 mb-8 shadow-2xl">
        <h3 class="text-white text-xl font-bold mb-6 flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" />
            </svg>
            Resumo Executivo - Melhores Opções
        </h3>
        
        <div class="summary-cards grid grid-cols-1 md:grid-cols-3 gap-6">
            <?php 
            $periods = [12, 36, 60];
            $periodLabels = ['12 Meses (1 ano)', '36 Meses (3 anos)', '60 Meses (5 anos)'];
            
            foreach ($periods as $index => $months):
                // Encontrar o melhor modelo para este período
                $bestModel = null;
                $lowestCost = PHP_FLOAT_MAX;
                
                foreach ($comparisonResults as $result) {
                    if ($result['costs'][$months]['total'] < $lowestCost) {
                        $lowestCost = $result['costs'][$months]['total'];
                        $bestModel = $result;
                    }
                }
            ?>
            <div class="summary-card bg-white/10 backdrop-blur-md rounded-lg p-5 border border-white/20 shadow-xl hover:bg-white/15 transition-all">
                <h4 class="text-white/80 text-sm font-medium mb-3"><?= $periodLabels[$index] ?></h4>
                <div class="best-option">
                    <div class="text-xs text-white/70 mb-1">Melhor Opção:</div>
                    <div class="text-white font-bold text-lg mb-2"><?= $bestModel['name'] ?></div>
                    <div class="text-white text-2xl font-bold">
                        R$ <?= number_format($bestModel['costs'][$months]['total'], 2, ',', '.') ?>
                    </div>
                    <div class="text-xs text-white/70 mt-2">
                        Média mensal: R$ <?= number_format($bestModel['costs'][$months]['monthly_average'], 2, ',', '.') ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <!-- Tabela Detalhada -->
    <div class="detailed-comparison bg-white rounded-xl shadow-lg overflow-hidden border border-slate-200">
        <div class="p-6 bg-gradient-to-r from-slate-50 to-blue-50 border-b border-slate-200">
            <h3 class="text-lg font-semibold text-slate-900 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-blue-600">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.375 19.5h17.25m-17.25 0a1.125 1.125 0 0 1-1.125-1.125M3.375 19.5h7.5c.621 0 1.125-.504 1.125-1.125m-9.75 0V5.625m0 12.75v-1.5c0-.621.504-1.125 1.125-1.125m18.375 2.625V5.625m0 12.75c0 .621-.504 1.125-1.125 1.125m1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125m0 3.75h-7.5A1.125 1.125 0 0 1 12 18.375m9.75-12.75c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125m19.5 0v1.5c0 .621-.504 1.125-1.125 1.125M2.25 5.625v1.5c0 .621.504 1.125 1.125 1.125m0 0h17.25m-17.25 0h7.5c.621 0 1.125.504 1.125 1.125M3.375 8.25c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125m17.25-3.75h-7.5c-.621 0-1.125.504-1.125 1.125m8.625-1.125c.621 0 1.125.504 1.125 1.125v1.5c0 .621-.504 1.125-1.125 1.125m-17.25 0h7.5m-7.5 0c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125M12 10.875v-1.5m0 1.5c0 .621-.504 1.125-1.125 1.125M12 10.875c0 .621.504 1.125 1.125 1.125m-2.25 0c.621 0 1.125.504 1.125 1.125M13.125 12h7.5m-7.5 0c-.621 0-1.125.504-1.125 1.125M20.625 12c.621 0 1.125.504 1.125 1.125v1.5c0 .621-.504 1.125-1.125 1.125m-17.25 0h7.5M12 14.625v-1.5m0 1.5c0 .621-.504 1.125-1.125 1.125M12 14.625c0 .621.504 1.125 1.125 1.125m-2.25 0c.621 0 1.125.504 1.125 1.125m0 1.5v-1.5m0 0c0-.621.504-1.125 1.125-1.125m0 0h7.5" />
                </svg>
                Comparação Detalhada por Período
            </h3>
        </div>
        
        <div class="overflow-x-auto">
            <table class="comparison-table w-full">
                <thead>
                    <tr class="bg-slate-800 text-white">
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider sticky left-0 bg-slate-800 z-10">Modelo</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Tipo</th>
                        <th colspan="3" class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider bg-blue-900 border-l border-white/10">12 Meses</th>
                        <th colspan="3" class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider bg-purple-900 border-l border-white/10">36 Meses (3 Anos)</th>
                        <th colspan="3" class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider bg-indigo-900 border-l border-white/10">60 Meses (5 Anos)</th>
                    </tr>
                    <tr class="bg-slate-700 text-white/90 text-xs">
                        <th class="px-6 py-2"></th>
                        <th class="px-6 py-2"></th>
                        <th class="px-4 py-2 text-center bg-blue-800 border-l border-white/10">Total</th>
                        <th class="px-4 py-2 text-center bg-blue-800">Média Mensal</th>
                        <th class="px-4 py-2 text-center bg-blue-800">Posição</th>
                        <th class="px-4 py-2 text-center bg-purple-800 border-l border-white/10">Total</th>
                        <th class="px-4 py-2 text-center bg-purple-800">Média Mensal</th>
                        <th class="px-4 py-2 text-center bg-purple-800">Posição</th>
                        <th class="px-4 py-2 text-center bg-indigo-800 border-l border-white/10">Total</th>
                        <th class="px-4 py-2 text-center bg-indigo-800">Média Mensal</th>
                        <th class="px-4 py-2 text-center bg-indigo-800">Posição</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    <?php foreach ($comparisonResults as $modelId => $result): ?>
                    <tr class="hover:bg-slate-50 transition-colors" data-model="<?= $modelId ?>">
                        <td class="px-6 py-4 sticky left-0 bg-white z-10 border-r border-slate-200">
                            <div class="font-semibold text-slate-900 text-sm"><?= $result['name'] ?></div>
                            <div class="text-xs text-slate-500 mt-1"><?= $result['best_for'] ?></div>
                        </td>
                        <td class="px-6 py-4">
                            <?php
                            $badgeClass = 'badge-monthly';
                            if ($result['billing_type'] === 'perpetual') $badgeClass = 'badge-perpetual';
                            if ($result['billing_type'] === 'contract') $badgeClass = 'badge-contract';
                            ?>
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold <?= $badgeClass ?>">
                                <?= ucfirst($result['billing_type']) ?>
                            </span>
                        </td>
                        
                        <?php foreach ([12, 36, 60] as $months): ?>
                        <td class="px-4 py-4 text-right font-semibold text-slate-900 border-l border-slate-100">
                            R$ <?= number_format($result['costs'][$months]['total'], 2, ',', '.') ?>
                        </td>
                        <td class="px-4 py-4 text-right text-sm text-slate-600">
                            R$ <?= number_format($result['costs'][$months]['monthly_average'], 2, ',', '.') ?>
                        </td>
                        <td class="px-4 py-4 text-center">
                            <?php
                            $rank = $result['ranking'][$months];
                            $rankClass = 'rank-badge';
                            if ($rank == 1) $rankClass .= ' rank-1';
                            elseif ($rank == 2) $rankClass .= ' rank-2';
                            elseif ($rank == 3) $rankClass .= ' rank-3';
                            else $rankClass .= ' rank-other';
                            ?>
                            <span class="<?= $rankClass ?>">
                                #<?= $rank ?>
                            </span>
                        </td>
                        <?php endforeach; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Observações Importantes -->
    <div class="comparison-notes bg-amber-50 border border-amber-200 rounded-xl p-6 mt-6 shadow-sm">
        <h4 class="text-base font-semibold text-amber-900 mb-4 flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
            </svg>
            Observações Importantes
        </h4>
        <ul class="space-y-2 text-sm text-amber-900">
            <li class="flex items-start gap-2">
                <svg class="w-4 h-4 mt-0.5 text-amber-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span><strong>Modelos Mensais (Azure ARC, SPLA, CSP):</strong> Sem investimento inicial, máxima flexibilidade de cancelamento. Ideal para workloads variáveis.</span>
            </li>
            <li class="flex items-start gap-2">
                <svg class="w-4 h-4 mt-0.5 text-amber-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span><strong>Software Perpétuo:</strong> Alto investimento inicial (CAPEX), mas pode ser mais econômico em períodos longos (5+ anos). SA incluído nos cálculos.</span>
            </li>
            <li class="flex items-start gap-2">
                <svg class="w-4 h-4 mt-0.5 text-amber-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span><strong>Open Value:</strong> Pagamento parcelado em 3 anos com SA incluído. Requer mínimo de 5 licenças. Propriedade permanente.</span>
            </li>
            <li class="flex items-start gap-2">
                <svg class="w-4 h-4 mt-0.5 text-amber-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span><strong>Open Value Subscription:</strong> Assinatura de 3 anos com pagamentos anuais. Direitos expiram ao fim do contrato (sem propriedade).</span>
            </li>
            <li class="flex items-start gap-2">
                <svg class="w-4 h-4 mt-0.5 text-amber-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span><strong>SPLA:</strong> Exclusivo para Service Providers. Requer relatório mensal de uso. Licenciamento flexível baseado em consumo real.</span>
            </li>
            <li class="flex items-start gap-2">
                <svg class="w-4 h-4 mt-0.5 text-amber-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span><strong>CSP Subscription:</strong> Descontos por volume aplicados automaticamente. Ideal para empresas com parceiro Microsoft ativo.</span>
            </li>
        </ul>
    </div>
    
    <!-- Botões de Ação -->
    <div class="comparison-actions flex flex-wrap gap-3 mt-6">
        <button onclick="window.print()" class="btn-action">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z" />
            </svg>
            Imprimir
        </button>
        
        <button onclick="exportComparisonToExcel()" class="btn-action">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
            </svg>
            Exportar Excel
        </button>
        
        <button onclick="shareComparison()" class="btn-action">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M7.217 10.907a2.25 2.25 0 1 0 0 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186 9.566-5.314m-9.566 7.5 9.566 5.314m0 0a2.25 2.25 0 1 0 3.935 2.186 2.25 2.25 0 0 0-3.935-2.186Zm0-12.814a2.25 2.25 0 1 0 3.933-2.185 2.25 2.25 0 0 0-3.933 2.185Z" />
            </svg>
            Compartilhar
        </button>
    </div>
</div>

<style>
.btn-action {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.625rem 1.25rem;
    background: white;
    border: 1px solid #e2e8f0;
    color: #475569;
    font-weight: 500;
    font-size: 0.875rem;
    border-radius: 0.5rem;
    transition: all 0.2s;
    cursor: pointer;
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
}

.btn-action:hover {
    background: #f8fafc;
    border-color: #cbd5e1;
    color: #1e293b;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.rank-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.25rem 0.625rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 700;
    min-width: 2rem;
}

.rank-1 {
    background: linear-gradient(135deg, #10b981, #059669);
    color: white;
    box-shadow: 0 2px 4px rgba(16, 185, 129, 0.3);
}

.rank-2 {
    background: linear-gradient(135deg, #f59e0b, #d97706);
    color: white;
    box-shadow: 0 2px 4px rgba(245, 158, 11, 0.3);
}

.rank-3 {
    background: linear-gradient(135deg, #f97316, #ea580c);
    color: white;
    box-shadow: 0 2px 4px rgba(249, 115, 22, 0.3);
}

.rank-other {
    background: #e2e8f0;
    color: #64748b;
}

@media print {
    .comparison-actions,
    .btn-action {
        display: none !important;
    }
}
</style>
