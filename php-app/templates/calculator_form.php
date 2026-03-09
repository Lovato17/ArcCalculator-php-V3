<div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
    <!-- Coluna Esquerda: Inputs e Configurações -->
    <div class="lg:col-span-7 space-y-6">
        <form method="POST" action="index.php" id="calculator-form">
            <input type="hidden" name="calculate" value="1">
            <input type="hidden" name="tab_id" value="<?php echo $activeTab; ?>">
            <!-- Painel Principal de Configuração -->
            <div class="panel-modern p-8 rounded-xl">
                <div class="flex items-center gap-3 mb-6 pb-4 border-b border-slate-100">
                    <div class="p-2 bg-blue-50 rounded-lg text-tdsynnex-blue">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 15.75V18m-7.5-6.75h.008v.008H8.25v-.008Zm0 2.25h.008v.008H8.25V13.5Zm0 2.25h.008v.008H8.25v-.008Zm0 2.25h.008v.008H8.25V18Zm2.498-6.75h.007v.008h-.007v-.008Zm0 2.25h.007v.008h-.007V13.5Zm0 2.25h.007v.008h-.007v-.008Zm0 2.25h.007v.008h-.007V18Zm2.504-6.75h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V13.5Zm0 2.25h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V18Zm2.498-6.75h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V13.5ZM8.25 6h7.5a2.25 2.25 0 0 1 2.25 2.25v12a2.25 2.25 0 0 1-2.25 2.25H8.25A2.25 2.25 0 0 1 6 20.25V8.25A2.25 2.25 0 0 1 8.25 6Z" />
                        </svg>
                    </div>
                    <h2 class="text-lg font-bold text-slate-800">Parâmetros da Estimativa</h2>
                </div>

                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">
                            Capacidade (vCores)
                        </label>
                        <input
                            type="number"
                            name="vCores"
                            id="vCores"
                            min="4"
                            value="<?php echo htmlspecialchars($currentEstimate['vCores']); ?>"
                            class="input-tech"
                            onchange="autoCalculate()"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">
                            Tempo de Uso
                        </label>
                        <div class="grid grid-cols-2 gap-3">
                            <select id="timeUnit" class="input-tech" onchange="updateTimeUnit(); autoCalculate();">
                                <option value="hour" <?php echo (!isset($currentEstimate['timeUnit']) || $currentEstimate['timeUnit'] === 'hour') ? 'selected' : ''; ?>>Por Hora</option>
                                <option value="day" <?php echo (isset($currentEstimate['timeUnit']) && $currentEstimate['timeUnit'] === 'day') ? 'selected' : ''; ?>>Por Dia</option>
                                <option value="month" <?php echo (isset($currentEstimate['timeUnit']) && $currentEstimate['timeUnit'] === 'month') ? 'selected' : ''; ?>>Por Mês</option>
                            </select>
                            
                            <div id="timeValueContainer">
                                <input
                                    type="number"
                                    id="timeValue"
                                    min="1"
                                    value="<?php echo isset($currentEstimate['timeValue']) ? htmlspecialchars($currentEstimate['timeValue']) : 1; ?>"
                                    class="input-tech"
                                    oninput="updateHoursPerMonth(); autoCalculate();"
                                    onchange="updateHoursPerMonth(); autoCalculate();"
                                />
                            </div>
                            
                            <!-- Campo oculto que será enviado no formulário -->
                            <input type="hidden" name="hoursPerMonth" id="hoursPerMonth" value="<?php echo htmlspecialchars($currentEstimate['hoursPerMonth']); ?>">
                            <input type="hidden" name="timeUnit" id="timeUnitHidden" value="<?php echo isset($currentEstimate['timeUnit']) ? htmlspecialchars($currentEstimate['timeUnit']) : 'hour'; ?>">
                            <input type="hidden" name="timeValue" id="timeValueHidden" value="<?php echo isset($currentEstimate['timeValue']) ? htmlspecialchars($currentEstimate['timeValue']) : 730; ?>">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">
                            Edição SQL Server
                        </label>
                        <select name="edition" id="edition" class="input-tech" onchange="autoCalculate()">
                            <option value="Standard" <?php echo $currentEstimate['edition'] === 'Standard' ? 'selected' : ''; ?>>Standard</option>
                            <option value="Enterprise" <?php echo $currentEstimate['edition'] === 'Enterprise' ? 'selected' : ''; ?>>Enterprise</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">
                            Moeda de Visualização
                        </label>
                        <select name="currency" id="currency" class="input-tech" onchange="autoCalculate()">
                            <option value="USD" <?php echo $currentEstimate['currency'] === 'USD' ? 'selected' : ''; ?>>Dólar (USD)</option>
                            <option value="BRL" <?php echo $currentEstimate['currency'] === 'BRL' ? 'selected' : ''; ?>>Real (BRL)</option>
                        </select>
                    </div>
                </div>

                <!-- Tipo de Faturamento -->
                <div class="mt-6 pt-6 border-t border-slate-100">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">
                            Tipo de Faturamento
                        </label>
                        <select name="billingType" id="billingType" class="input-tech" onchange="updateBillingFields(); autoCalculate();">
                            <option value="" <?php echo (!isset($currentEstimate['billingType']) || $currentEstimate['billingType'] === '') ? 'selected' : ''; ?>>Selecione o tipo de faturamento</option>
                            <option value="client" <?php echo (isset($currentEstimate['billingType']) && $currentEstimate['billingType'] === 'client') ? 'selected' : ''; ?>>Faturamento Cliente</option>
                            <option value="resale" <?php echo (isset($currentEstimate['billingType']) && $currentEstimate['billingType'] === 'resale') ? 'selected' : ''; ?>>Faturamento Revenda</option>
                        </select>
                    </div>
                </div>

                <!-- Impostos e Margem -->
                <div id="taxMarkupContainer" class="mt-6 pt-6 border-t border-slate-100 grid md:grid-cols-2 gap-6" style="display: <?php echo (isset($currentEstimate['billingType']) && $currentEstimate['billingType'] !== '') ? 'grid' : 'none'; ?>;">
                    <div id="taxContainer">
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">
                            Impostos (%)
                        </label>
                        <input
                            type="number"
                            name="tax"
                            id="tax"
                            min="0"
                            step="0.1"
                            value="<?php echo htmlspecialchars($currentEstimate['tax']); ?>"
                            class="input-tech"
                            onchange="autoCalculate()"
                        />
                    </div>
                    <div id="markupContainer">
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">
                            Margem de Lucro (%)
                        </label>
                        <input
                            type="number"
                            name="markup"
                            id="markup"
                            min="0"
                            step="0.1"
                            value="<?php echo htmlspecialchars($currentEstimate['markup']); ?>"
                            class="input-tech"
                            onchange="autoCalculate()"
                        />
                    </div>
                </div>
                
                <div class="mt-6 flex justify-end">
                    <button type="submit" class="btn-tech-primary">Calcular</button>
                </div>
            </div>
            
            <!-- Seletor de Comparação de Modelos -->
            <?php include 'comparison-selector.php'; ?>
        </form>
        
        <script>
        // Inicializar ao carregar a página
        document.addEventListener('DOMContentLoaded', function() {
            updateTimeUnit();
            updateBillingFields();
            // Não chama autoCalculate aqui para evitar sobrescrever valores da sessão
        });
        
        function updateBillingFields() {
            const billingType = document.getElementById('billingType').value;
            const taxMarkupContainer = document.getElementById('taxMarkupContainer');
            const markupContainer = document.getElementById('markupContainer');
            
            if (billingType === '') {
                // Nenhum tipo selecionado: oculta tudo
                taxMarkupContainer.style.display = 'none';
            } else if (billingType === 'client') {
                // Faturamento Cliente: mostra impostos e margem
                taxMarkupContainer.style.display = 'grid';
                markupContainer.style.display = 'block';
            } else if (billingType === 'resale') {
                // Faturamento Revenda: mostra apenas impostos
                taxMarkupContainer.style.display = 'grid';
                markupContainer.style.display = 'none';
                document.getElementById('markup').value = 0;
            }
        }
        
        function updateTimeUnit() {
            const unit = document.getElementById('timeUnit').value;
            const timeValue = document.getElementById('timeValue');
            const timeValueContainer = document.getElementById('timeValueContainer');
            
            // Atualizar campo oculto
            document.getElementById('timeUnitHidden').value = unit;
            
            timeValueContainer.style.display = 'block';
            if (unit === 'hour') {
                timeValue.placeholder = 'Horas';
                timeValue.max = 744;
            } else if (unit === 'day') {
                timeValue.placeholder = 'Dias';
                timeValue.max = 31;
            } else if (unit === 'month') {
                timeValue.placeholder = 'Meses';
                timeValue.max = 120; // até 10 anos
            }
            updateHoursPerMonth();
            autoCalculate(); // Chama cálculo imediatamente após mudança de unidade
        }
        
        function updateHoursPerMonth() {
            const unit = document.getElementById('timeUnit').value;
            const value = parseInt(document.getElementById('timeValue').value) || 1;
            let hours;
            
            // Atualizar campo oculto
            document.getElementById('timeValueHidden').value = value;
            
            if (unit === 'hour') {
                // Hora: valor direto (max 744h em um mês)
                hours = Math.min(value, 744);
            } else if (unit === 'day') {
                // Dia: valor * 24 horas (max 31 dias)
                hours = Math.min(value * 24, 744);
            } else if (unit === 'month') {
                // Mês: 730 horas por mês (usaremos value como quantidade de meses)
                hours = 730;
            }
            
            document.getElementById('hoursPerMonth').value = hours;
        }
        
        let calculationTimeout;
        function autoCalculate() {
            // Garantir que hoursPerMonth esteja atualizado antes de enviar
            updateHoursPerMonth();

            // Atualização instantânea sem debounce
            const formData = new FormData();
            formData.append('calculate', '1');
            formData.append('tab_id', <?php echo $activeTab; ?>);
            formData.append('vCores', document.getElementById('vCores').value);
            
            // Pegar o valor atualizado diretamente do campo (que acabou de ser atualizado)
            formData.append('hoursPerMonth', document.getElementById('hoursPerMonth').value);
            
            formData.append('edition', document.getElementById('edition').value);
            formData.append('currency', document.getElementById('currency').value);
            formData.append('billingType', document.getElementById('billingType').value);
            formData.append('tax', document.getElementById('tax').value);
            formData.append('markup', document.getElementById('markup').value);
            
            // Calcular meses com base no timeUnit
            const unit = document.getElementById('timeUnit').value;
            const timeValue = parseInt(document.getElementById('timeValue').value) || 1;
            const months = unit === 'month' ? timeValue : 1;
            formData.append('months', months);

            // Adicionar campos de comparação de modelos se estiver habilitado
            const enableComp = document.getElementById('enableComparison');
            if (enableComp && enableComp.checked) {
                formData.append('enable_comparison', 'on');
                const checkboxes = document.querySelectorAll('input[name="models[]"]:checked');
                checkboxes.forEach((chk) => {
                    formData.append('models[]', chk.value);
                });
            }
            
            fetch('index.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(html => {
                // Extrai apenas a seção de resultados do HTML retornado
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newResults = doc.getElementById('results-container');
                
                if (newResults) {
                    document.getElementById('results-container').innerHTML = newResults.innerHTML;
                    
                    // Atualizar dados para PDF
                    const jsonDiv = newResults.querySelector('#calculator-data-json');
                    if (jsonDiv) {
                        try {
                            window.calculatorData = JSON.parse(jsonDiv.textContent);
                        } catch (e) {
                            console.error('Erro ao atualizar dados do PDF', e);
                        }
                    }
                    
                    // Atualizar comparisonData para PDF
                    const compDiv = newResults.querySelector('#comparison-data-json');
                    if (compDiv) {
                        try {
                            window.comparisonData = JSON.parse(compDiv.textContent);
                        } catch (e) {
                            console.error('Erro ao atualizar comparisonData', e);
                            window.comparisonData = [];
                        }
                    } else {
                        window.comparisonData = [];
                    }
                }
            })
            .catch(error => console.error('Erro ao calcular:', error));
        }
        </script>
    </div>

    <!-- Coluna Direita: Resultados -->
    <div class="lg:col-span-5 space-y-6" id="results-container">
        <!-- Dados JSON para PDF -->
        <div id="calculator-data-json" style="display:none;">
            <?php echo json_encode(['estimate' => $currentEstimate, 'result' => $result]); ?>
        </div>
        
        <!-- Dados de Comparação para PDF -->
        <div id="comparison-data-json" style="display:none;">
            <?php echo (isset($comparisonResults) && !empty($comparisonResults)) ? json_encode(array_values($comparisonResults)) : '[]'; ?>
        </div>
        
        <script>
            // Inicializar comparisonData se existir no carregamento da página
            const compData = document.getElementById('comparison-data-json');
            if (compData && compData.textContent) {
                try {
                    window.comparisonData = JSON.parse(compData.textContent);
                } catch(e) {
                    console.error('Erro ao inicializar comparisonData', e);
                    window.comparisonData = [];
                }
            } else {
                window.comparisonData = [];
            }
        </script>

        <!-- Card de Economia -->
        <div class="panel-modern p-6 bg-gradient-to-br from-tdsynnex-teal to-tdsynnex-teal-dark text-white border-none relative overflow-hidden">
            <div class="relative z-10">
                <?php if (isset($comparisonResults) && !empty($comparisonResults)): 
                    // Encontrar a melhor opção na comparação
                    $months = isset($currentEstimate['months']) ? $currentEstimate['months'] : 1;
                    $periodKey = ($months <= 12) ? 12 : (($months <= 36) ? 36 : 60);
                    
                    $bestOption = null;
                    $worstOption = null;
                    $lowestCost = PHP_FLOAT_MAX;
                    $highestCost = 0;
                    
                    foreach ($comparisonResults as $model) {
                        $cost = $model['costs'][$periodKey]['total'];
                        if ($cost < $lowestCost) {
                            $lowestCost = $cost;
                            $bestOption = $model;
                        }
                        if ($cost > $highestCost) {
                            $highestCost = $cost;
                            $worstOption = $model;
                        }
                    }
                    
                    $savings = $highestCost - $lowestCost;
                    $savingsPercentage = ($highestCost > 0) ? (($savings / $highestCost) * 100) : 0;
                    $currency = $currentEstimate['currency'] === 'BRL' ? 'R$' : '$';
                ?>
                    <h3 class="text-tdsynnex-blue-light font-medium mb-1">
                        Economia Máxima (<?php echo $months; ?> <?php echo $months > 1 ? 'meses' : 'mês'; ?>)
                    </h3>
                    <div class="text-4xl font-bold mb-2">
                        <?php echo $currency; ?> <?php echo number_format($savings, 2, ',', '.'); ?>
                    </div>
                    <div class="space-y-1">
                        <div class="inline-flex items-center gap-1 bg-white/10 px-2 py-1 rounded text-sm">
                            <span>Economia de <?php echo number_format($savingsPercentage, 1); ?>%</span>
                        </div>
                        <div class="text-sm text-white/90 mt-2">
                            <div class="flex items-center gap-1">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span><strong><?php echo $bestOption['name']; ?></strong> vs <?php echo $worstOption['name']; ?></span>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <h3 class="text-tdsynnex-blue-light font-medium mb-1">
                        Economia Total (<?php echo isset($currentEstimate['months']) ? $currentEstimate['months'] : 1; ?> <?php echo (isset($currentEstimate['months']) && $currentEstimate['months'] > 1) ? 'meses' : 'mês'; ?>)
                    </h3>
                    <div class="text-4xl font-bold mb-2" id="annual-savings">
                        <?php 
                        $months = isset($currentEstimate['months']) ? $currentEstimate['months'] : 1;
                        $totalSavings = $result['savings']['annual'] / 12 * $months;
                        echo $currentEstimate['currency'] === 'BRL' ? 'R$' : '$'; ?> 
                        <?php echo number_format($totalSavings, 2, ',', '.'); ?>
                    </div>
                    <div class="inline-flex items-center gap-1 bg-white/10 px-2 py-1 rounded text-sm">
                        <span id="savings-percentage">Economia de <?php echo number_format($result['savings']['percentage'], 1); ?>%</span>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Detalhamento -->
        <?php if (isset($comparisonResults) && !empty($comparisonResults)): ?>
        <div class="panel-modern p-6">
            <h3 class="font-bold text-slate-800 mb-4">Comparativo de Modelos (<?php echo isset($currentEstimate['months']) ? $currentEstimate['months'] : 1; ?> <?php echo (isset($currentEstimate['months']) && $currentEstimate['months'] > 1) ? 'meses' : 'mês'; ?>)</h3>
            
            <!-- Resultados da Comparação -->
            <?php 
            $months = isset($currentEstimate['months']) ? $currentEstimate['months'] : 1;
            $currency = $currentEstimate['currency'] === 'BRL' ? 'R$' : '$';
            
            // Ordenar por ranking no período atual
            $sortedResults = $comparisonResults;
            usort($sortedResults, function($a, $b) use ($months) {
                $periodKey = ($months <= 12) ? 12 : (($months <= 36) ? 36 : 60);
                return $a['ranking'][$periodKey] - $b['ranking'][$periodKey];
            });
            
            foreach ($sortedResults as $index => $model): 
                $periodKey = ($months <= 12) ? 12 : (($months <= 36) ? 36 : 60);
                $cost = $model['costs'][$periodKey];
                $rank = $model['ranking'][$periodKey];
                
                // Definir cores por ranking
                $bgColor = 'bg-slate-50';
                $borderColor = 'border-slate-200';
                $textColor = 'text-slate-600';
                
                if ($rank == 1) {
                    $bgColor = 'bg-gradient-to-br from-green-50 to-emerald-50';
                    $borderColor = 'border-green-300';
                    $textColor = 'text-green-700';
                } elseif ($rank == 2) {
                    $bgColor = 'bg-gradient-to-br from-blue-50 to-cyan-50';
                    $borderColor = 'border-blue-300';
                    $textColor = 'text-blue-700';
                } elseif ($rank == 3) {
                    $bgColor = 'bg-gradient-to-br from-orange-50 to-amber-50';
                    $borderColor = 'border-orange-300';
                    $textColor = 'text-orange-700';
                }
                
                $isFirst = ($index === 0);
            ?>
            <div class="<?php echo $isFirst ? 'mb-6' : 'mb-4'; ?> p-4 <?php echo $bgColor; ?> rounded-lg border-2 <?php echo $borderColor; ?> relative">
                <!-- Badge de Ranking -->
                <?php if ($rank <= 3): ?>
                <div class="absolute -top-2 -right-2 w-8 h-8 rounded-full flex items-center justify-center font-bold text-white text-sm shadow-lg
                    <?php echo $rank == 1 ? 'bg-gradient-to-br from-green-500 to-green-600' : 
                               ($rank == 2 ? 'bg-gradient-to-br from-blue-500 to-blue-600' : 
                               'bg-gradient-to-br from-orange-500 to-orange-600'); ?>">
                    #<?php echo $rank; ?>
                </div>
                <?php endif; ?>
                
                <div class="flex justify-between items-center mb-2">
                    <div class="flex items-center gap-2">
                        <span class="font-bold <?php echo $textColor; ?> text-sm"><?php echo $model['name']; ?></span>
                        <span class="text-xs px-2 py-0.5 rounded-full 
                            <?php echo $model['billing_type'] == 'monthly' ? 'bg-blue-100 text-blue-700' : 
                                       ($model['billing_type'] == 'perpetual' ? 'bg-purple-100 text-purple-700' : 
                                       'bg-orange-100 text-orange-700'); ?>">
                            <?php echo $model['billing_type'] == 'monthly' ? 'Mensal' : 
                                          ($model['billing_type'] == 'perpetual' ? 'Perpétuo' : 'Contrato'); ?>
                        </span>
                    </div>
                    <span class="text-xl font-bold text-slate-900">
                        <?php echo $currency; ?> <?php echo number_format($cost['total'], 2, ',', '.'); ?>
                    </span>
                </div>
                
                <div class="text-xs text-slate-600 space-y-1">
                    <div class="flex justify-between">
                        <span>Média Mensal:</span>
                        <span class="font-medium"><?php echo $currency; ?> <?php echo number_format($cost['monthly_average'], 2, ',', '.'); ?></span>
                    </div>
                    <?php if (!empty($model['best_for'])): ?>
                    <div class="mt-2 pt-2 border-t border-slate-200">
                        <span class="text-xs italic text-slate-500 flex items-start gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3.5 h-3.5 text-blue-500 mt-0.5 flex-shrink-0">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-7-4a1 1 0 1 1-2 0 1 1 0 0 1 2 0ZM9 9a.75.75 0 0 0 0 1.5h.253a.25.25 0 0 1 .244.304l-.459 2.066A1.75 1.75 0 0 0 10.747 15H11a.75.75 0 0 0 0-1.5h-.253a.25.25 0 0 1-.244-.304l.459-2.066A1.75 1.75 0 0 0 9.253 9H9Z" clip-rule="evenodd" />
                            </svg>
                            <?php echo $model['best_for']; ?>
                        </span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
            
            <!-- Observação sobre comparação -->
            <div class="mt-4 p-3 bg-amber-50 border border-amber-200 rounded-lg">
                <p class="text-xs text-amber-800">
                    <strong>📊 Comparação ativa:</strong> Os valores acima consideram <?php echo count($comparisonResults); ?> modelos de licenciamento para o período selecionado. Rankings ajustados automaticamente.
                </p>
            </div>
        </div>
        <?php endif; ?>
        
        <button id="generate-pdf" class="w-full btn-tech-secondary flex items-center justify-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
            </svg>
            Baixar Relatório PDF
        </button>
    </div>
</div>
