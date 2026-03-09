<?php
/**
 * Teste do Sistema de Comparação de Licenciamento
 * Execute este arquivo para verificar se tudo está funcionando
 */

// Configurar para exibir todos os erros
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🧪 Teste do Sistema de Comparação de Licenciamento</h1>";
echo "<hr>";

// Teste 1: Carregar classes
echo "<h2>1. Testando carregamento de classes...</h2>";
try {
    require_once '../src/models/LicensingModel.php';
    echo "✅ LicensingModel.php carregado<br>";
    
    require_once '../src/services/LicensingComparator.php';
    echo "✅ LicensingComparator.php carregado<br>";
} catch (Exception $e) {
    echo "❌ Erro ao carregar classes: " . $e->getMessage() . "<br>";
    exit;
}

// Teste 2: Carregar configuração
echo "<h2>2. Testando configuração de modelos...</h2>";
try {
    $config = require '../src/config/licensing-models.php';
    echo "✅ Configuração carregada<br>";
    echo "📊 Total de modelos configurados: " . count($config) . "<br>";
    
    foreach ($config as $modelId => $modelConfig) {
        echo "  - " . $modelConfig['name'] . " (" . $modelConfig['billing_type'] . ")<br>";
    }
} catch (Exception $e) {
    echo "❌ Erro ao carregar configuração: " . $e->getMessage() . "<br>";
    exit;
}

// Teste 3: Instanciar LicensingModel
echo "<h2>3. Testando criação de modelo...</h2>";
try {
    $azureArcConfig = $config['azure_arc'];
    $azureModel = new LicensingModel($azureArcConfig);
    echo "✅ Modelo Azure ARC criado<br>";
    echo "  - Nome: " . $azureModel->getName() . "<br>";
    echo "  - Tipo: " . $azureModel->getBillingType() . "<br>";
} catch (Exception $e) {
    echo "❌ Erro ao criar modelo: " . $e->getMessage() . "<br>";
    exit;
}

// Teste 4: Calcular custos
echo "<h2>4. Testando cálculo de custos...</h2>";
try {
    $cores = 16;
    $months = 36;
    $exchangeRate = 5.39;
    
    $costs = $azureModel->calculateCost($cores, $months, $exchangeRate);
    
    echo "✅ Cálculo realizado para Azure ARC<br>";
    echo "  - Cores: $cores<br>";
    echo "  - Período: $months meses<br>";
    echo "  - Taxa câmbio: $exchangeRate<br>";
    echo "  - <strong>Custo Total: R$ " . number_format($costs['total'], 2, ',', '.') . "</strong><br>";
    echo "  - Média Mensal: R$ " . number_format($costs['monthly_average'], 2, ',', '.') . "<br>";
} catch (Exception $e) {
    echo "❌ Erro ao calcular custos: " . $e->getMessage() . "<br>";
    exit;
}

// Teste 5: Criar comparador
echo "<h2>5. Testando criação do comparador...</h2>";
try {
    $comparator = new LicensingComparator();
    echo "✅ LicensingComparator criado<br>";
    
    $models = $comparator->getAvailableModels();
    echo "📊 Modelos disponíveis: " . count($models) . "<br>";
} catch (Exception $e) {
    echo "❌ Erro ao criar comparador: " . $e->getMessage() . "<br>";
    exit;
}

// Teste 6: Comparar modelos
echo "<h2>6. Testando comparação entre modelos...</h2>";
try {
    $selectedModels = ['azure_arc', 'spla', 'csp_subscription', 'perpetual'];
    
    $results = $comparator->compare($selectedModels, $cores, $exchangeRate);
    
    echo "✅ Comparação realizada com sucesso<br>";
    echo "📊 Modelos comparados: " . count($results) . "<br><br>";
    
    echo "<h3>Resultados para 36 meses:</h3>";
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
    echo "<tr style='background: #f0f0f0;'>";
    echo "<th>Modelo</th>";
    echo "<th>Tipo</th>";
    echo "<th>Custo Total</th>";
    echo "<th>Média Mensal</th>";
    echo "<th>Ranking</th>";
    echo "</tr>";
    
    foreach ($results as $result) {
        $cost36 = $result['costs'][36];
        echo "<tr>";
        echo "<td><strong>" . $result['name'] . "</strong></td>";
        echo "<td>" . ucfirst($result['billing_type']) . "</td>";
        echo "<td>R$ " . number_format($cost36['total'], 2, ',', '.') . "</td>";
        echo "<td>R$ " . number_format($cost36['monthly_average'], 2, ',', '.') . "</td>";
        echo "<td style='text-align: center;'>#" . $result['ranking'][36] . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
} catch (Exception $e) {
    echo "❌ Erro ao comparar modelos: " . $e->getMessage() . "<br>";
    echo "Stack trace: <pre>" . $e->getTraceAsString() . "</pre>";
    exit;
}

// Teste 7: Gerar relatório
echo "<h2>7. Testando geração de relatório...</h2>";
try {
    $report = $comparator->generateReport($results);
    
    echo "✅ Relatório gerado<br>";
    echo "<h3>Resumo por Período:</h3>";
    echo "<ul>";
    
    foreach ($report['summary'] as $period => $summary) {
        echo "<li>";
        echo "<strong>" . $summary['period_label'] . ":</strong> ";
        echo $summary['best_model'] . " - ";
        echo "R$ " . number_format($summary['best_cost'], 2, ',', '.');
        echo "</li>";
    }
    
    echo "</ul>";
    
    echo "<h3>Recomendações:</h3>";
    echo "<ul>";
    
    foreach ($report['recommendations'] as $key => $rec) {
        echo "<li>";
        echo "<strong>" . $rec['period'] . ":</strong> ";
        echo $rec['model'] . " - " . $rec['reason'];
        echo "</li>";
    }
    
    echo "</ul>";
    
} catch (Exception $e) {
    echo "❌ Erro ao gerar relatório: " . $e->getMessage() . "<br>";
    exit;
}

// Teste 8: Calcular economia
echo "<h2>8. Testando cálculo de economia...</h2>";
try {
    $savings = $comparator->calculateSavings($results, 'spla', 36);
    
    echo "✅ Economia calculada (base: SPLA)<br>";
    echo "<h3>Economia comparada ao SPLA (36 meses):</h3>";
    echo "<ul>";
    
    foreach ($savings as $modelId => $saving) {
        $prefix = $saving['cheaper'] ? '💰' : '⚠️';
        $text = $saving['cheaper'] ? 'Economia de' : 'Custo adicional de';
        
        echo "<li>";
        echo "$prefix <strong>" . $saving['model'] . ":</strong> ";
        echo "$text R$ " . number_format(abs($saving['difference']), 2, ',', '.') . " ";
        echo "(" . abs($saving['percentage']) . "%)";
        echo "</li>";
    }
    
    echo "</ul>";
    
} catch (Exception $e) {
    echo "❌ Erro ao calcular economia: " . $e->getMessage() . "<br>";
    exit;
}

// Resultado final
echo "<hr>";
echo "<h2 style='color: green;'>✅ TODOS OS TESTES PASSARAM COM SUCESSO!</h2>";
echo "<p>O sistema de comparação de licenciamento está funcionando corretamente.</p>";
echo "<p><a href='index.php'>← Voltar para a Calculadora</a></p>";
