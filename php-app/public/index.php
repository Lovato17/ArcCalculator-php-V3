<?php
// filepath: public/index.php
session_start();
require_once '../src/Calculator.php';
require_once '../src/services/LicensingComparator.php';

// Lê preços do skus.json
$jsonFile = __DIR__ . '/skus.json';
$prices = [];
if (file_exists($jsonFile)) {
    $prices = json_decode(file_get_contents($jsonFile), true) ?: [];
}
// Sincroniza
$_SESSION['prices'] = $prices;

// Inicializa estimativas na sessão se não existir
if (!isset($_SESSION['estimates'])) {
    $_SESSION['estimates'] = [
        1 => [
            'id' => 1,
            'name' => 'Estimativa 1',
            'vCores' => 4,
            'hoursPerMonth' => 730,
            'timeUnit' => 'hour',
            'timeValue' => 730,
            'edition' => 'Standard',
            'currency' => 'USD',
            'billingType' => '',
            'tax' => 0,
            'markup' => 0,
            'months' => 1
        ]
    ];
    $_SESSION['activeTab'] = 1;
}

$activeTab = $_SESSION['activeTab'] ?? 1;

// Processar ações
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Salvar configurações de preços
    if (isset($_POST['save_settings'])) {
        $_SESSION['prices'] = [
            'arcStandard'         => filter_input(INPUT_POST, 'arcStandard', FILTER_VALIDATE_FLOAT) ?: 0.539,
            'arcEnterprise'       => filter_input(INPUT_POST, 'arcEnterprise', FILTER_VALIDATE_FLOAT) ?: 2.021,
            'splaStandard'        => filter_input(INPUT_POST, 'splaStandard', FILTER_VALIDATE_FLOAT) ?: 1131.90,
            'splaEnterprise'      => filter_input(INPUT_POST, 'splaEnterprise', FILTER_VALIDATE_FLOAT) ?: 4611.08,
            'csp1yStandard'       => filter_input(INPUT_POST, 'csp1yStandard', FILTER_VALIDATE_FLOAT) ?: 15226.33,
            'csp1yEnterprise'     => filter_input(INPUT_POST, 'csp1yEnterprise', FILTER_VALIDATE_FLOAT) ?: 58377.01,
            'csp3yStandard'       => filter_input(INPUT_POST, 'csp3yStandard', FILTER_VALIDATE_FLOAT) ?: 38245.50,
            'csp3yEnterprise'     => filter_input(INPUT_POST, 'csp3yEnterprise', FILTER_VALIDATE_FLOAT) ?: 146564.40,
            'perpetualStandard'   => filter_input(INPUT_POST, 'perpetualStandard', FILTER_VALIDATE_FLOAT) ?: 32240.30,
            'perpetualEnterprise' => filter_input(INPUT_POST, 'perpetualEnterprise', FILTER_VALIDATE_FLOAT) ?: 123602.72,
            'saPct'               => filter_input(INPUT_POST, 'saPct', FILTER_VALIDATE_FLOAT) ?: 25,
            'ovsStandard'         => filter_input(INPUT_POST, 'ovsStandard', FILTER_VALIDATE_FLOAT) ?: 12651.28,
            'ovsEnterprise'       => filter_input(INPUT_POST, 'ovsEnterprise', FILTER_VALIDATE_FLOAT) ?: 48511.95,
            'exchangeRate'        => filter_input(INPUT_POST, 'exchangeRate', FILTER_VALIDATE_FLOAT) ?: 5.39,
        ];
    }
    // Adicionar nova estimativa
    elseif (isset($_POST['add_estimate'])) {
        $newId = max(array_keys($_SESSION['estimates'])) + 1;
        $_SESSION['estimates'][$newId] = [
            'id' => $newId,
            'name' => 'Estimativa ' . $newId,
            'vCores' => 4,
            'hoursPerMonth' => 730,
            'timeUnit' => 'hour',
            'timeValue' => 730,
            'edition' => 'Standard',
            'currency' => 'USD',
            'billingType' => '',
            'tax' => 0,
            'markup' => 0,
            'months' => 1
        ];
        $_SESSION['activeTab'] = $newId;
        $activeTab = $newId;
    }
    // Remover estimativa
    elseif (isset($_POST['remove_estimate'])) {
        $removeId = (int)$_POST['remove_estimate'];
        if (count($_SESSION['estimates']) > 1 && isset($_SESSION['estimates'][$removeId])) {
            unset($_SESSION['estimates'][$removeId]);
            // Se removeu a aba ativa, muda para a primeira disponível
            if ($activeTab === $removeId) {
                $_SESSION['activeTab'] = array_key_first($_SESSION['estimates']);
                $activeTab = $_SESSION['activeTab'];
            }
        }
    }
    // Renomear estimativa
    elseif (isset($_POST['rename_estimate'])) {
        $renameId = (int)$_POST['rename_estimate'];
        $newName = filter_input(INPUT_POST, 'new_name', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        if (isset($_SESSION['estimates'][$renameId]) && !empty($newName)) {
            $_SESSION['estimates'][$renameId]['name'] = $newName;
        }
    }
    // Calcular estimativa
    elseif (isset($_POST['calculate'])) {
        $tabId = (int)$_POST['tab_id'];
        $billingType = filter_input(INPUT_POST, 'billingType', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        if ($billingType === null || $billingType === false) {
            $billingType = '';
        }
        $markup = filter_input(INPUT_POST, 'markup', FILTER_VALIDATE_FLOAT) ?: 0;
        
        // Se for faturamento revenda, zera a margem de lucro
        if ($billingType === 'resale') {
            $markup = 0;
        }
        
        $_SESSION['estimates'][$tabId] = [
            'id' => $tabId,
            'name' => $_SESSION['estimates'][$tabId]['name'],
            'vCores' => filter_input(INPUT_POST, 'vCores', FILTER_VALIDATE_INT) ?: 4,
            'hoursPerMonth' => filter_input(INPUT_POST, 'hoursPerMonth', FILTER_VALIDATE_INT) ?: 730,
            'timeUnit' => filter_input(INPUT_POST, 'timeUnit', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?: 'month',
            'timeValue' => filter_input(INPUT_POST, 'timeValue', FILTER_VALIDATE_INT) ?: 1,
            'edition' => filter_input(INPUT_POST, 'edition', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?: 'Standard',
            'currency' => filter_input(INPUT_POST, 'currency', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?: 'USD',
            'billingType' => $billingType,
            'tax' => filter_input(INPUT_POST, 'tax', FILTER_VALIDATE_FLOAT) ?: 0,
            'markup' => $markup,
            'months' => filter_input(INPUT_POST, 'months', FILTER_VALIDATE_INT) ?: 1
        ];
        $_SESSION['activeTab'] = $tabId;
        $activeTab = $tabId;
    }
}

// Trocar aba ativa via GET
if (isset($_GET['tab'])) {
    $tabId = (int)$_GET['tab'];
    if (isset($_SESSION['estimates'][$tabId])) {
        $_SESSION['activeTab'] = $tabId;
        $activeTab = $tabId;
    }
}

// Calcular resultados para a aba ativa
$currentEstimate = $_SESSION['estimates'][$activeTab];
$calculator = new Calculator($_SESSION['prices']);
$result = $calculator->calculate($currentEstimate);

// Processar comparação de modelos se habilitada
$comparisonResults = null;
if (isset($_POST['enable_comparison']) && isset($_POST['models']) && is_array($_POST['models']) && count($_POST['models']) >= 2) {
    try {
        $comparator = new LicensingComparator();
        $selectedModels = $_POST['models'];
        $cores = $currentEstimate['vCores'];
        $exchangeRate = $_SESSION['prices']['exchangeRate'];
        
        $comparisonResults = $comparator->compare($selectedModels, $cores, $exchangeRate);
    } catch (Exception $e) {
        // Log do erro ou mensagem ao usuário
        $comparisonError = $e->getMessage();
    }
}

require_once '../templates/layout.php';
