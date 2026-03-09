<?php
session_start();

// Definição de preços padrão
$defaultPrices = [
    'splaStandard' => 210,
    'splaEnterprise' => 855.47,
    'arcStandard' => 0.10,
    'arcEnterprise' => 0.375,
    'cspMonthlyPerCore' => 62.00,
    'perpetualPerCore' => 3717.00,
    'perpetualSaPercentage' => 25,
    'openValuePerCore' => 3200.00,
    'openValueSubAnnualPerCore' => 1100.00,
    'exchangeRate' => 5.39
];

// Inicializa ou atualiza a sessão com os preços padrão para chaves faltantes
if (!isset($_SESSION['prices'])) {
    $_SESSION['prices'] = $defaultPrices;
} else {
    // Garante que chaves novas sejam adicionadas mantendo valores existentes
    $_SESSION['prices'] = array_merge($defaultPrices, $_SESSION['prices']);
    
    // Força a atualização de chaves numéricas que podem ter vindo vazias ou zero se for o caso
    foreach ($defaultPrices as $key => $val) {
        if (!isset($_SESSION['prices'][$key])) {
            $_SESSION['prices'][$key] = $val;
        }
    }
}

// Processa salvamento de preços
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_prices'])) {
    $_SESSION['prices'] = [
        'splaStandard' => floatval($_POST['splaStandard']),
        'splaEnterprise' => floatval($_POST['splaEnterprise']),
        'arcStandard' => floatval($_POST['arcStandard']),
        'arcEnterprise' => floatval($_POST['arcEnterprise']),
        'cspMonthlyPerCore' => floatval($_POST['cspMonthlyPerCore']),
        'perpetualPerCore' => floatval($_POST['perpetualPerCore']),
        'perpetualSaPercentage' => floatval($_POST['perpetualSaPercentage']),
        'openValuePerCore' => floatval($_POST['openValuePerCore']),
        'openValueSubAnnualPerCore' => floatval($_POST['openValueSubAnnualPerCore']),
        'exchangeRate' => floatval($_POST['exchangeRate'])
    ];
    $successMessage = "Preços atualizados com sucesso!";
}

$prices = $_SESSION['prices'];
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciamento de SKUs e Preços | TD SYNNEX</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="bg-slate-100 min-h-screen font-sans text-slate-900">
    <div class="container mx-auto p-4 md:p-8 max-w-[1400px]">
        
        <!-- Header -->
        <header class="flex flex-col md:flex-row justify-between items-center mb-8 pb-6 border-b border-slate-200">
            <div class="flex items-center gap-4">
                <div class="h-12 w-12 bg-gradient-to-br from-blue-600 to-blue-700 rounded-lg flex items-center justify-center shadow-md">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-7 h-7 text-white">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 tracking-tight">
                        Gerenciamento de SKUs e Preços
                    </h1>
                    <p class="text-sm text-slate-500 font-medium">Configure os preços dos produtos</p>
                </div>
            </div>
            <div class="flex items-center gap-6">
                <nav class="flex items-center gap-4">
                    <a href="home.php" class="text-sm font-medium text-slate-600 hover:text-blue-600 transition-colors">Home</a>
                    <a href="index.php" class="text-sm font-medium text-slate-600 hover:text-blue-600 transition-colors">Calculadora</a>
                    <a href="home.php" class="text-sm font-medium text-slate-600 hover:text-blue-600 transition-colors">Migrações Azure</a>
                </nav>
            </div>
        </header>

        <?php if (isset($successMessage)): ?>
        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg flex items-center gap-3 animate-fadeIn">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-green-600">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
            <span class="text-green-800 font-medium"><?php echo $successMessage; ?></span>
        </div>
        <?php endif; ?>

        <div class="bg-white rounded-xl shadow-lg p-8">
            <form method="POST">
                <input type="hidden" name="save_prices" value="1">

                <!-- Seção SPLA -->
                <div class="mb-8">
                    <div class="flex items-center gap-3 mb-4 pb-3 border-b border-slate-200">
                        <div class="p-2 bg-gray-100 rounded-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-gray-600">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 14.25h13.5m-13.5 0a3 3 0 0 1-3-3m3 3a3 3 0 1 0 0 6h13.5a3 3 0 1 0 0-6m-16.5-3a3 3 0 0 1 3-3h13.5a3 3 0 0 1 3 3m-19.5 0a4.5 4.5 0 0 1 .9-2.7L5.737 5.1a3.375 3.375 0 0 1 2.7-1.35h7.126c1.062 0 2.062.5 2.7 1.35l2.587 3.45a4.5 4.5 0 0 1 .9 2.7m0 0a3 3 0 0 1-3 3m0 3h.008v.008h-.008v-.008Zm0-6h.008v.008h-.008v-.008Zm-3 6h.008v.008h-.008v-.008Zm0-6h.008v.008h-.008v-.008Z" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-semibold text-slate-900">SPLA (Service Provider License Agreement)</h2>
                            <p class="text-sm text-slate-500">Preços por pack de 2 cores</p>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-slate-50 p-6 rounded-lg border border-slate-200">
                            <label class="block mb-3">
                                <div class="text-sm font-semibold text-slate-700">SQL Server Standard</div>
                                <div class="text-xs text-slate-500 mt-0.5">Pack de 2 cores/mês</div>
                            </label>
                            <div class="relative">
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 font-medium text-sm">USD</span>
                                <input type="number" step="0.01" name="splaStandard" value="<?php echo $prices['splaStandard']; ?>" 
                                       class="w-full pl-4 pr-14 py-3 border-2 border-slate-300 rounded-lg text-lg font-semibold focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                                       required>
                            </div>
                            <p class="mt-2 text-xs text-slate-600 flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3.5 h-3.5 text-blue-500">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-7-4a1 1 0 1 1-2 0 1 1 0 0 1 2 0ZM9 9a.75.75 0 0 0 0 1.5h.253a.25.25 0 0 1 .244.304l-.459 2.066A1.75 1.75 0 0 0 10.747 15H11a.75.75 0 0 0 0-1.5h-.253a.25.25 0 0 1-.244-.304l.459-2.066A1.75 1.75 0 0 0 9.253 9H9Z" clip-rule="evenodd" />
                                </svg>
                                Valor padrão: USD 210.00
                            </p>
                        </div>

                        <div class="bg-slate-50 p-6 rounded-lg border border-slate-200">
                            <label class="block mb-3">
                                <div class="text-sm font-semibold text-slate-700">SQL Server Enterprise</div>
                                <div class="text-xs text-slate-500 mt-0.5">Pack de 2 cores/mês</div>
                            </label>
                            <div class="relative">
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 font-medium text-sm">USD</span>
                                <input type="number" step="0.01" name="splaEnterprise" value="<?php echo $prices['splaEnterprise']; ?>" 
                                       class="w-full pl-4 pr-14 py-3 border-2 border-slate-300 rounded-lg text-lg font-semibold focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                                       required>
                            </div>
                            <p class="mt-2 text-xs text-slate-600 flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3.5 h-3.5 text-blue-500">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-7-4a1 1 0 1 1-2 0 1 1 0 0 1 2 0ZM9 9a.75.75 0 0 0 0 1.5h.253a.25.25 0 0 1 .244.304l-.459 2.066A1.75 1.75 0 0 0 10.747 15H11a.75.75 0 0 0 0-1.5h-.253a.25.25 0 0 1-.244-.304l.459-2.066A1.75 1.75 0 0 0 9.253 9H9Z" clip-rule="evenodd" />
                                </svg>
                                Valor padrão: USD 855.47
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Seção Azure ARC -->
                <div class="mb-8">
                    <div class="flex items-center gap-3 mb-4 pb-3 border-b border-slate-200">
                        <div class="p-2 bg-blue-100 rounded-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-blue-600">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15a4.5 4.5 0 0 0 4.5 4.5H18a3.75 3.75 0 0 0 1.332-7.257 3 3 0 0 0-3.758-3.848 5.25 5.25 0 0 0-10.233 2.33A4.502 4.502 0 0 0 2.25 15Z" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-semibold text-slate-900">Azure ARC</h2>
                            <p class="text-sm text-slate-500">Preços por vCore/hora</p>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-blue-50 p-6 rounded-lg border border-blue-200">
                            <label class="block mb-3">
                                <div class="text-sm font-semibold text-slate-700">SQL Server Standard</div>
                                <div class="text-xs text-slate-500 mt-0.5">Por vCore/hora</div>
                            </label>
                            <div class="relative">
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 font-medium text-sm">USD</span>
                                <input type="number" step="0.001" name="arcStandard" value="<?php echo $prices['arcStandard']; ?>" 
                                       class="w-full pl-4 pr-14 py-3 border-2 border-blue-300 rounded-lg text-lg font-semibold focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                                       required>
                            </div>
                            <p class="mt-2 text-xs text-slate-600 flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3.5 h-3.5 text-blue-500">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-7-4a1 1 0 1 1-2 0 1 1 0 0 1 2 0ZM9 9a.75.75 0 0 0 0 1.5h.253a.25.25 0 0 1 .244.304l-.459 2.066A1.75 1.75 0 0 0 10.747 15H11a.75.75 0 0 0 0-1.5h-.253a.25.25 0 0 1-.244-.304l.459-2.066A1.75 1.75 0 0 0 9.253 9H9Z" clip-rule="evenodd" />
                                </svg>
                                Valor padrão: USD 0.100
                            </p>
                        </div>

                        <div class="bg-blue-50 p-6 rounded-lg border border-blue-200">
                            <label class="block mb-3">
                                <div class="text-sm font-semibold text-slate-700">SQL Server Enterprise</div>
                                <div class="text-xs text-slate-500 mt-0.5">Por vCore/hora</div>
                            </label>
                            <div class="relative">
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 font-medium text-sm">USD</span>
                                <input type="number" step="0.001" name="arcEnterprise" value="<?php echo $prices['arcEnterprise']; ?>" 
                                       class="w-full pl-4 pr-14 py-3 border-2 border-blue-300 rounded-lg text-lg font-semibold focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                                       required>
                            </div>
                            <p class="mt-2 text-xs text-slate-600 flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3.5 h-3.5 text-blue-500">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-7-4a1 1 0 1 1-2 0 1 1 0 0 1 2 0ZM9 9a.75.75 0 0 0 0 1.5h.253a.25.25 0 0 1 .244.304l-.459 2.066A1.75 1.75 0 0 0 10.747 15H11a.75.75 0 0 0 0-1.5h-.253a.25.25 0 0 1-.244-.304l.459-2.066A1.75 1.75 0 0 0 9.253 9H9Z" clip-rule="evenodd" />
                                </svg>
                                Valor padrão: USD 0.375
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Seção CSP Subscription -->
                <div class="mb-8">
                    <div class="flex items-center gap-3 mb-4 pb-3 border-b border-slate-200">
                        <div class="p-2 bg-purple-100 rounded-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-purple-600">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-semibold text-slate-900">CSP Subscription</h2>
                            <p class="text-sm text-slate-500">Cloud Solution Provider - Modelo mensal</p>
                        </div>
                    </div>
                    
                    <div class="max-w-md">
                        <div class="bg-purple-50 p-6 rounded-lg border border-purple-200">
                            <label class="block mb-3">
                                <div class="text-sm font-semibold text-slate-700">Preço Mensal por Core</div>
                                <div class="text-xs text-slate-500 mt-0.5">Valor base (antes de descontos por volume)</div>
                            </label>
                            <div class="relative">
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 font-medium text-sm">USD</span>
                                <input type="number" step="0.01" name="cspMonthlyPerCore" value="<?php echo $prices['cspMonthlyPerCore']; ?>" 
                                       class="w-full pl-4 pr-14 py-3 border-2 border-purple-300 rounded-lg text-lg font-semibold focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all"
                                       required>
                            </div>
                            <p class="mt-2 text-xs text-slate-600 flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3.5 h-3.5 text-blue-500">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-7-4a1 1 0 1 1-2 0 1 1 0 0 1 2 0ZM9 9a.75.75 0 0 0 0 1.5h.253a.25.25 0 0 1 .244.304l-.459 2.066A1.75 1.75 0 0 0 10.747 15H11a.75.75 0 0 0 0-1.5h-.253a.25.25 0 0 1-.244-.304l.459-2.066A1.75 1.75 0 0 0 9.253 9H9Z" clip-rule="evenodd" />
                                </svg>
                                Valor padrão: USD 62.00
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Seção Software Perpétuo -->
                <div class="mb-8">
                    <div class="flex items-center gap-3 mb-4 pb-3 border-b border-slate-200">
                        <div class="p-2 bg-amber-100 rounded-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-amber-600">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-semibold text-slate-900">Software Perpétuo</h2>
                            <p class="text-sm text-slate-500">Licença permanente com Software Assurance opcional</p>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-amber-50 p-6 rounded-lg border border-amber-200">
                            <label class="block mb-3">
                                <div class="text-sm font-semibold text-slate-700">Preço da Licença por Core</div>
                                <div class="text-xs text-slate-500 mt-0.5">Pagamento único (perpétuo)</div>
                            </label>
                            <div class="relative">
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 font-medium text-sm">USD</span>
                                <input type="number" step="0.01" name="perpetualPerCore" value="<?php echo $prices['perpetualPerCore']; ?>" 
                                       class="w-full pl-4 pr-14 py-3 border-2 border-amber-300 rounded-lg text-lg font-semibold focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-all"
                                       required>
                            </div>
                            <p class="mt-2 text-xs text-slate-600 flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3.5 h-3.5 text-blue-500">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-7-4a1 1 0 1 1-2 0 1 1 0 0 1 2 0ZM9 9a.75.75 0 0 0 0 1.5h.253a.25.25 0 0 1 .244.304l-.459 2.066A1.75 1.75 0 0 0 10.747 15H11a.75.75 0 0 0 0-1.5h-.253a.25.25 0 0 1-.244-.304l.459-2.066A1.75 1.75 0 0 0 9.253 9H9Z" clip-rule="evenodd" />
                                </svg>
                                Valor padrão: USD 3,717.00
                            </p>
                        </div>

                        <div class="bg-amber-50 p-6 rounded-lg border border-amber-200">
                            <label class="block mb-3">
                                <div class="text-sm font-semibold text-slate-700">Software Assurance (% anual)</div>
                                <div class="text-xs text-slate-500 mt-0.5">Percentual sobre o valor da licença</div>
                            </label>
                            <div class="relative">
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 font-medium text-sm">%</span>
                                <input type="number" step="0.1" name="perpetualSaPercentage" value="<?php echo $prices['perpetualSaPercentage']; ?>" 
                                       class="w-full pl-4 pr-14 py-3 border-2 border-amber-300 rounded-lg text-lg font-semibold focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-all"
                                       required>
                            </div>
                            <p class="mt-2 text-xs text-slate-600 flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3.5 h-3.5 text-blue-500">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-7-4a1 1 0 1 1-2 0 1 1 0 0 1 2 0ZM9 9a.75.75 0 0 0 0 1.5h.253a.25.25 0 0 1 .244.304l-.459 2.066A1.75 1.75 0 0 0 10.747 15H11a.75.75 0 0 0 0-1.5h-.253a.25.25 0 0 1-.244-.304l.459-2.066A1.75 1.75 0 0 0 9.253 9H9Z" clip-rule="evenodd" />
                                </svg>
                                Valor padrão: 25%
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Seção Open Value -->
                <div class="mb-8">
                    <div class="flex items-center gap-3 mb-4 pb-3 border-b border-slate-200">
                        <div class="p-2 bg-teal-100 rounded-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-teal-600">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-semibold text-slate-900">Open Value</h2>
                            <p class="text-sm text-slate-500">Contrato de 3 anos com pagamento parcelado</p>
                        </div>
                    </div>
                    
                    <div class="max-w-md">
                        <div class="bg-teal-50 p-6 rounded-lg border border-teal-200">
                            <label class="block mb-3">
                                <div class="text-sm font-semibold text-slate-700">Preço por Core</div>
                                <div class="text-xs text-slate-500 mt-0.5">Valor total dividido em 3 anos</div>
                            </label>
                            <div class="relative">
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 font-medium text-sm">USD</span>
                                <input type="number" step="0.01" name="openValuePerCore" value="<?php echo $prices['openValuePerCore']; ?>" 
                                       class="w-full pl-4 pr-14 py-3 border-2 border-teal-300 rounded-lg text-lg font-semibold focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-all"
                                       required>
                            </div>
                            <p class="mt-2 text-xs text-slate-600 flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3.5 h-3.5 text-blue-500">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-7-4a1 1 0 1 1-2 0 1 1 0 0 1 2 0ZM9 9a.75.75 0 0 0 0 1.5h.253a.25.25 0 0 1 .244.304l-.459 2.066A1.75 1.75 0 0 0 10.747 15H11a.75.75 0 0 0 0-1.5h-.253a.25.25 0 0 1-.244-.304l.459-2.066A1.75 1.75 0 0 0 9.253 9H9Z" clip-rule="evenodd" />
                                </svg>
                                Valor padrão: USD 3,200.00
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Seção Open Value Subscription -->
                <div class="mb-8">
                    <div class="flex items-center gap-3 mb-4 pb-3 border-b border-slate-200">
                        <div class="p-2 bg-indigo-100 rounded-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-indigo-600">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-semibold text-slate-900">Open Value Subscription</h2>
                            <p class="text-sm text-slate-500">Assinatura anual sem propriedade perpétua</p>
                        </div>
                    </div>
                    
                    <div class="max-w-md">
                        <div class="bg-indigo-50 p-6 rounded-lg border border-indigo-200">
                            <label class="block mb-3">
                                <div class="text-sm font-semibold text-slate-700">Preço Anual por Core</div>
                                <div class="text-xs text-slate-500 mt-0.5">Valor por ano de assinatura</div>
                            </label>
                            <div class="relative">
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 font-medium text-sm">USD</span>
                                <input type="number" step="0.01" name="openValueSubAnnualPerCore" value="<?php echo $prices['openValueSubAnnualPerCore']; ?>" 
                                       class="w-full pl-4 pr-14 py-3 border-2 border-indigo-300 rounded-lg text-lg font-semibold focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
                                       required>
                            </div>
                            <p class="mt-2 text-xs text-slate-600 flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3.5 h-3.5 text-blue-500">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-7-4a1 1 0 1 1-2 0 1 1 0 0 1 2 0ZM9 9a.75.75 0 0 0 0 1.5h.253a.25.25 0 0 1 .244.304l-.459 2.066A1.75 1.75 0 0 0 10.747 15H11a.75.75 0 0 0 0-1.5h-.253a.25.25 0 0 1-.244-.304l.459-2.066A1.75 1.75 0 0 0 9.253 9H9Z" clip-rule="evenodd" />
                                </svg>
                                Valor padrão: USD 1,100.00
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Seção Taxa de Câmbio -->
                <div class="mb-8">
                    <div class="flex items-center gap-3 mb-4 pb-3 border-b border-slate-200">
                        <div class="p-2 bg-green-100 rounded-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-green-600">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-semibold text-slate-900">Taxa de Câmbio</h2>
                            <p class="text-sm text-slate-500">Conversão USD → BRL</p>
                        </div>
                    </div>
                    
                    <div class="max-w-md">
                        <div class="bg-green-50 p-6 rounded-lg border border-green-200">
                            <label class="block text-sm font-semibold text-slate-700 mb-2">
                                Dólar Americano → Real Brasileiro
                            </label>
                            <div class="relative">
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 font-medium text-sm">BRL</span>
                                <input type="number" step="0.01" name="exchangeRate" value="<?php echo $prices['exchangeRate']; ?>" 
                                       class="w-full pl-4 pr-14 py-3 border-2 border-green-300 rounded-lg text-lg font-semibold focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all"
                                       required>
                            </div>
                            <p class="mt-2 text-xs text-slate-600 flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3.5 h-3.5 text-blue-500">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-7-4a1 1 0 1 1-2 0 1 1 0 0 1 2 0ZM9 9a.75.75 0 0 0 0 1.5h.253a.25.25 0 0 1 .244.304l-.459 2.066A1.75 1.75 0 0 0 10.747 15H11a.75.75 0 0 0 0-1.5h-.253a.25.25 0 0 1-.244-.304l.459-2.066A1.75 1.75 0 0 0 9.253 9H9Z" clip-rule="evenodd" />
                                </svg>
                                Valor padrão: R$ 5.39
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Botões de Ação -->
                <div class="flex items-center justify-between pt-6 border-t border-slate-200">
                    <a href="index.php" class="inline-flex items-center gap-2 px-6 py-3 text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-lg font-medium transition-all">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                        </svg>
                        Voltar à Calculadora
                    </a>
                    
                    <div class="flex gap-3">
                        <button type="button" onclick="resetToDefaults()" class="px-6 py-3 text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-lg font-medium transition-all border border-slate-300">
                            Restaurar Padrões
                        </button>
                        <button type="submit" class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-all shadow-md hover:shadow-lg" style="background-color: #2563eb;">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                            Salvar Configurações
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Informações Adicionais -->
        <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex items-start gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-blue-600 mt-0.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
                    </svg>
                    <div>
                        <h3 class="font-semibold text-blue-900 text-sm mb-1">Sobre os Preços</h3>
                        <p class="text-xs text-blue-700">Os valores configurados aqui serão utilizados em todos os cálculos da calculadora.</p>
                    </div>
                </div>
            </div>

            <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                <div class="flex items-start gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-amber-600 mt-0.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                    </svg>
                    <div>
                        <h3 class="font-semibold text-amber-900 text-sm mb-1">Atualizações</h3>
                        <p class="text-xs text-amber-700">Mantenha os preços atualizados conforme as tabelas oficiais da Microsoft.</p>
                    </div>
                </div>
            </div>

            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                <div class="flex items-start gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-green-600 mt-0.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                    </svg>
                    <div>
                        <h3 class="font-semibold text-green-900 text-sm mb-1">Segurança</h3>
                        <p class="text-xs text-green-700">As configurações são armazenadas na sessão e aplicadas imediatamente.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    function resetToDefaults() {
        if (confirm('⚠️ Deseja restaurar todos os preços para os valores padrão?\n\nEsta ação irá substituir as configurações atuais.')) {
            document.querySelector('input[name="splaStandard"]').value = '210';
            document.querySelector('input[name="splaEnterprise"]').value = '855.47';
            document.querySelector('input[name="arcStandard"]').value = '0.10';
            document.querySelector('input[name="arcEnterprise"]').value = '0.375';
            document.querySelector('input[name="cspMonthlyPerCore"]').value = '62.00';
            document.querySelector('input[name="perpetualPerCore"]').value = '3717.00';
            document.querySelector('input[name="perpetualSaPercentage"]').value = '25';
            document.querySelector('input[name="openValuePerCore"]').value = '3200.00';
            document.querySelector('input[name="openValueSubAnnualPerCore"]').value = '1100.00';
            document.querySelector('input[name="exchangeRate"]').value = '5.39';
        }
    }

    // Animação de fade-in
    const style = document.createElement('style');
    style.textContent = `
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fadeIn {
            animation: fadeIn 0.3s ease-out;
        }
    `;
    document.head.appendChild(style);
    </script>
</body>
</html>
