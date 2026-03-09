<?php
/**
 * Seletor de Modelos para Comparação
 */
?>

<div class="comparison-section bg-gradient-to-br from-slate-50 to-blue-50/30 rounded-xl p-6 mb-6 border border-slate-200/50 shadow-sm">
    <!-- Toggle para ativar comparação -->
    <div class="comparison-toggle mb-6">
        <label class="flex items-center gap-3 cursor-pointer group">
            <input type="checkbox" id="enableComparison" name="enable_comparison" 
                   onchange="autoCalculate()"
                   class="w-5 h-5 rounded border-slate-300 text-blue-600 focus:ring-2 focus:ring-blue-500 cursor-pointer transition-all">
            <div class="flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-blue-600">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                </svg>
                <span class="text-base font-semibold text-slate-800 group-hover:text-blue-700 transition-colors">
                    Comparar Modelos de Licenciamento
                </span>
            </div>
        </label>
        <p class="text-sm text-slate-600 ml-8 mt-1">
            Compare custos entre Azure ARC, SPLA, CSP, Software Perpétuo e outros modelos
        </p>
    </div>
    
    <!-- Opções de Comparação -->
    <div id="comparisonOptions" style="display: none;" class="animate-fadeIn">
        <div class="bg-white rounded-lg p-5 border border-slate-200 shadow-sm">
            <h3 class="text-sm font-semibold text-slate-700 mb-4 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-blue-600">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                Selecione os modelos para comparar:
            </h3>
            
            <div class="model-selection grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                
                <!-- Modelos Mensais (OPEX) -->
                <div class="model-group bg-gradient-to-br from-blue-50 to-white p-4 rounded-lg border border-blue-200">
                    <h4 class="text-xs font-bold text-blue-900 uppercase tracking-wide mb-3 flex items-center gap-2">
                        <span class="w-2 h-2 bg-blue-500 rounded-full"></span>
                        Modelos Mensais (OPEX)
                    </h4>
                    
                    <label class="flex items-center gap-3 p-2.5 rounded-md hover:bg-blue-100/50 cursor-pointer transition-all group mb-2">
                        <input type="checkbox" name="models[]" value="azure_arc" checked 
                               onchange="autoCalculate()"
                               class="w-4 h-4 rounded border-blue-300 text-blue-600 focus:ring-2 focus:ring-blue-500 cursor-pointer">
                        <div class="flex-1">
                            <span class="text-sm font-medium text-slate-800 group-hover:text-blue-700">Azure ARC</span>
                            <span class="block text-xs text-slate-500 mt-0.5">Mensal • Flexível</span>
                        </div>
                        <span class="badge-monthly">Mensal</span>
                    </label>
                    
                    <label class="flex items-center gap-3 p-2.5 rounded-md hover:bg-blue-100/50 cursor-pointer transition-all group mb-2">
                        <input type="checkbox" name="models[]" value="spla" 
                               onchange="autoCalculate()"
                               class="w-4 h-4 rounded border-blue-300 text-blue-600 focus:ring-2 focus:ring-blue-500 cursor-pointer">
                        <div class="flex-1">
                            <span class="text-sm font-medium text-slate-800 group-hover:text-blue-700">SPLA</span>
                            <span class="block text-xs text-slate-500 mt-0.5">Mensal • Service Provider</span>
                        </div>
                        <span class="badge-monthly">Mensal</span>
                    </label>
                    
                    <label class="flex items-center gap-3 p-2.5 rounded-md hover:bg-blue-100/50 cursor-pointer transition-all group">
                        <input type="checkbox" name="models[]" value="csp_subscription" 
                               onchange="autoCalculate()"
                               class="w-4 h-4 rounded border-blue-300 text-blue-600 focus:ring-2 focus:ring-blue-500 cursor-pointer">
                        <div class="flex-1">
                            <span class="text-sm font-medium text-slate-800 group-hover:text-blue-700">CSP Subscription</span>
                            <span class="block text-xs text-slate-500 mt-0.5">Mensal • Desconto Volume</span>
                        </div>
                        <span class="badge-monthly">Mensal</span>
                    </label>
                </div>
                
                <!-- Modelo Perpétuo (CAPEX) -->
                <div class="model-group bg-gradient-to-br from-purple-50 to-white p-4 rounded-lg border border-purple-200">
                    <h4 class="text-xs font-bold text-purple-900 uppercase tracking-wide mb-3 flex items-center gap-2">
                        <span class="w-2 h-2 bg-purple-500 rounded-full"></span>
                        Licença Perpétua (CAPEX)
                    </h4>
                    
                    <label class="flex items-center gap-3 p-2.5 rounded-md hover:bg-purple-100/50 cursor-pointer transition-all group">
                        <input type="checkbox" name="models[]" value="perpetual" 
                               onchange="autoCalculate()"
                               class="w-4 h-4 rounded border-purple-300 text-purple-600 focus:ring-2 focus:ring-purple-500 cursor-pointer">
                        <div class="flex-1">
                            <span class="text-sm font-medium text-slate-800 group-hover:text-purple-700">Software Perpétuo</span>
                            <span class="block text-xs text-slate-500 mt-0.5">Pagamento Único + SA Anual</span>
                        </div>
                        <span class="badge-perpetual">Perpétuo</span>
                    </label>
                    
                    <div class="mt-3 p-2.5 bg-purple-50/50 rounded text-xs text-purple-800 border border-purple-200/50">
                        <strong>Nota:</strong> Cálculo inclui Software Assurance (SA) de 25% ao ano
                    </div>
                </div>
                
                <!-- Contratos de Volume -->
                <div class="model-group bg-gradient-to-br from-orange-50 to-white p-4 rounded-lg border border-orange-200">
                    <h4 class="text-xs font-bold text-orange-900 uppercase tracking-wide mb-3 flex items-center gap-2">
                        <span class="w-2 h-2 bg-orange-500 rounded-full"></span>
                        Contratos de Volume
                    </h4>
                    
                    <label class="flex items-center gap-3 p-2.5 rounded-md hover:bg-orange-100/50 cursor-pointer transition-all group mb-2">
                        <input type="checkbox" name="models[]" value="open_value" 
                               onchange="autoCalculate()"
                               class="w-4 h-4 rounded border-orange-300 text-orange-600 focus:ring-2 focus:ring-orange-500 cursor-pointer">
                        <div class="flex-1">
                            <span class="text-sm font-medium text-slate-800 group-hover:text-orange-700">Open Value</span>
                            <span class="block text-xs text-slate-500 mt-0.5">3 Anos • Parcelado</span>
                        </div>
                        <span class="badge-contract">3 Anos</span>
                    </label>
                    
                    <label class="flex items-center gap-3 p-2.5 rounded-md hover:bg-orange-100/50 cursor-pointer transition-all group">
                        <input type="checkbox" name="models[]" value="open_value_subscription" 
                               onchange="autoCalculate()"
                               class="w-4 h-4 rounded border-orange-300 text-orange-600 focus:ring-2 focus:ring-orange-500 cursor-pointer">
                        <div class="flex-1">
                            <span class="text-sm font-medium text-slate-800 group-hover:text-orange-700">Open Value Subscription</span>
                            <span class="block text-xs text-slate-500 mt-0.5">3 Anos • Assinatura</span>
                        </div>
                        <span class="badge-contract">3 Anos</span>
                    </label>
                    
                    <div class="mt-3 p-2.5 bg-orange-50/50 rounded text-xs text-orange-800 border border-orange-200/50">
                        <strong>Nota:</strong> Requer mínimo de 5 licenças
                    </div>
                </div>
                
            </div>
            
            <!-- Botões de Ação -->
            <div class="comparison-actions flex flex-wrap gap-3 mt-6 pt-5 border-t border-slate-200">
                <button type="button" id="selectAll" 
                        class="px-4 py-2 text-sm font-medium text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg transition-all flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                    Selecionar Todos
                </button>
                
                <button type="button" id="selectNone" 
                        class="px-4 py-2 text-sm font-medium text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg transition-all flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                    Limpar Seleção
                </button>
                
                <div class="flex-1"></div>
                
                <button type="button" id="compareModels" 
                        class="px-6 py-2 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-all shadow-md hover:shadow-lg flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                    </svg>
                    Comparar Selecionados
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.badge-monthly,
.badge-perpetual,
.badge-contract {
    display: inline-block;
    padding: 2px 8px;
    font-size: 10px;
    font-weight: 600;
    border-radius: 12px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.badge-monthly {
    background: #dbeafe;
    color: #1e40af;
}

.badge-perpetual {
    background: #e9d5ff;
    color: #6b21a8;
}

.badge-contract {
    background: #fed7aa;
    color: #9a3412;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fadeIn {
    animation: fadeIn 0.3s ease-out;
}
</style>
