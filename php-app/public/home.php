<?php
// filepath: public/home.php
session_start();

// Lê preços do skus.json
$jsonFile = __DIR__ . '/skus.json';
$prices = [];
if (file_exists($jsonFile)) {
    $prices = json_decode(file_get_contents($jsonFile), true) ?: [];
}
// Sincroniza
$_SESSION['prices'] = $prices;

// Processar configurações
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_settings'])) {
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
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Home - TD SYNNEX Tools</title>
  <link rel="stylesheet" href="css/style.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="bg-slate-100 min-h-screen font-sans text-slate-900">
  <div id="app" class="container mx-auto p-4 md:p-8 max-w-[1600px]">
    
    <!-- Header -->
    <header class="flex flex-col md:flex-row justify-between items-center mb-8 pb-6 border-b border-slate-200">
        <div class="flex items-center gap-4">
            <img src="logo.png" alt="TD SYNNEX Logo" class="h-12 w-auto object-contain">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">
                    Tools
                </h1>
                <p class="text-sm text-slate-500 font-medium">Ferramentas e Calculadoras</p>
            </div>
        </div>
        <!-- Menu de Navegação -->
        <div class="flex items-center gap-6">
            <nav class="flex items-center gap-4">
                <a href="home.php" class="text-sm font-medium text-blue-600 border-b-2 border-blue-600 pb-1">Home</a>
                <a href="migracao-azure.php" class="text-sm font-medium text-slate-600 hover:text-blue-600 transition-colors">Migração Azure</a>
                <a href="sql-advisor.php" class="text-sm font-medium text-slate-600 hover:text-blue-600 transition-colors">SQL Advisor</a>
                <a href="#cloud-partner-hub" class="text-sm font-medium text-slate-600 hover:text-blue-600 transition-colors">Cloud Partner HUB</a>
            </nav>
            <!-- Botão Settings -->
            <button id="openSettings" class="p-2 bg-blue-100 hover:bg-blue-200 rounded-lg text-blue-600 hover:text-blue-700 transition-all duration-200 border border-blue-200" title="Configurações de Preços">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                </svg>
            </button>
        </div>
    </header>

    <!-- Modal de Configurações -->
    <div id="settingsModal" class="fixed inset-0 bg-black/30 backdrop-blur-sm flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-xl shadow-2xl p-8 mx-auto" style="width: 800px;">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-blue-100 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-blue-600">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-slate-900">Configurações de Preços</h3>
                </div>
                <button id="closeSettings" class="p-1.5 hover:bg-slate-100 rounded-lg text-slate-400 hover:text-slate-600 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form method="POST" id="settingsForm">
                <input type="hidden" name="save_settings" value="1">
                <div style="max-height: 60vh; overflow-y: auto; padding-right: 4px;">
                
                <!-- Seção Azure ARC -->
                <div class="mb-6">
                    <h4 class="text-sm font-semibold text-slate-700 mb-3 flex items-center gap-2">
                        <span class="w-2 h-2 bg-blue-500 rounded-full"></span>
                        Azure ARC (por vCore/hora)
                    </h4>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">Standard (USD)</label>
                            <input type="number" step="0.001" name="arcStandard" value="<?php echo $_SESSION['prices']['arcStandard'] ?? 0.539; ?>"
                                   class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">Enterprise (USD)</label>
                            <input type="number" step="0.001" name="arcEnterprise" value="<?php echo $_SESSION['prices']['arcEnterprise'] ?? 2.021; ?>"
                                   class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                </div>

                <!-- Seção SPLA -->
                <div class="mb-6">
                    <h4 class="text-sm font-semibold text-slate-700 mb-3 flex items-center gap-2">
                        <span class="w-2 h-2 bg-gray-400 rounded-full"></span>
                        SPLA (por pack de 2 cores/mês)
                    </h4>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">Standard (BRL)</label>
                            <input type="number" step="0.01" name="splaStandard" value="<?php echo $_SESSION['prices']['splaStandard'] ?? 1131.90; ?>"
                                   class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">Enterprise (BRL)</label>
                            <input type="number" step="0.01" name="splaEnterprise" value="<?php echo $_SESSION['prices']['splaEnterprise'] ?? 4611.08; ?>"
                                   class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                </div>

                <!-- Seção CSP 1 Ano -->
                <div class="mb-6">
                    <h4 class="text-sm font-semibold text-slate-700 mb-3 flex items-center gap-2">
                        <span class="w-2 h-2 bg-indigo-500 rounded-full"></span>
                        CSP 1 Ano Upfront (por pack de 2 cores/ano)
                    </h4>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">Standard (USD)</label>
                            <input type="number" step="0.01" name="csp1yStandard" value="<?php echo $_SESSION['prices']['csp1yStandard'] ?? 15226.33; ?>"
                                   class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">Enterprise (USD)</label>
                            <input type="number" step="0.01" name="csp1yEnterprise" value="<?php echo $_SESSION['prices']['csp1yEnterprise'] ?? 58377.01; ?>"
                                   class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                </div>

                <!-- Seção CSP 3 Anos -->
                <div class="mb-6">
                    <h4 class="text-sm font-semibold text-slate-700 mb-3 flex items-center gap-2">
                        <span class="w-2 h-2 bg-purple-500 rounded-full"></span>
                        CSP 3 Anos Upfront (por pack de 2 cores, total 3 anos)
                    </h4>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">Standard (USD)</label>
                            <input type="number" step="0.01" name="csp3yStandard" value="<?php echo $_SESSION['prices']['csp3yStandard'] ?? 38245.50; ?>"
                                   class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">Enterprise (USD)</label>
                            <input type="number" step="0.01" name="csp3yEnterprise" value="<?php echo $_SESSION['prices']['csp3yEnterprise'] ?? 146564.40; ?>"
                                   class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                </div>

                <!-- Seção Perpétuo -->
                <div class="mb-6">
                    <h4 class="text-sm font-semibold text-slate-700 mb-3 flex items-center gap-2">
                        <span class="w-2 h-2 bg-amber-500 rounded-full"></span>
                        Software Perpétuo (por pack de 2 cores, licença única)
                    </h4>
                    <div class="grid grid-cols-2 gap-4 mb-3">
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">Standard (USD)</label>
                            <input type="number" step="0.01" name="perpetualStandard" value="<?php echo $_SESSION['prices']['perpetualStandard'] ?? 32240.30; ?>"
                                   class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">Enterprise (USD)</label>
                            <input type="number" step="0.01" name="perpetualEnterprise" value="<?php echo $_SESSION['prices']['perpetualEnterprise'] ?? 123602.72; ?>"
                                   class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                    <div class="w-1/2">
                        <label class="block text-xs font-medium text-slate-600 mb-1">SA (% da licença/ano)</label>
                        <input type="number" step="0.1" name="saPct" value="<?php echo $_SESSION['prices']['saPct'] ?? 25; ?>"
                               class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>

                <!-- Seção OVS -->
                <div class="mb-6">
                    <h4 class="text-sm font-semibold text-slate-700 mb-3 flex items-center gap-2">
                        <span class="w-2 h-2 bg-teal-500 rounded-full"></span>
                        OVS — Open Value Subscription (por pack de 2 cores/ano)
                    </h4>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">Standard (USD)</label>
                            <input type="number" step="0.01" name="ovsStandard" value="<?php echo $_SESSION['prices']['ovsStandard'] ?? 12651.28; ?>"
                                   class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">Enterprise (USD)</label>
                            <input type="number" step="0.01" name="ovsEnterprise" value="<?php echo $_SESSION['prices']['ovsEnterprise'] ?? 48511.95; ?>"
                                   class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                </div>

                <!-- Seção Câmbio -->
                <div class="mb-6">
                    <h4 class="text-sm font-semibold text-slate-700 mb-3 flex items-center gap-2">
                        <span class="w-2 h-2 bg-green-500 rounded-full"></span>
                        Taxa de Câmbio
                    </h4>
                    <div class="w-1/2">
                        <label class="block text-xs font-medium text-slate-600 mb-1">USD → BRL</label>
                        <input type="number" step="0.01" name="exchangeRate" value="<?php echo $_SESSION['prices']['exchangeRate'] ?? 5.39; ?>"
                               class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>

                </div><!-- /scrollable -->

                <!-- Botões -->
                <div class="flex justify-end gap-3 pt-4 border-t border-slate-200">
                    <button type="button" id="cancelSettings" class="px-4 py-2 text-sm font-medium text-slate-600 hover:text-slate-800 hover:bg-slate-100 rounded-lg transition-colors" style="background-color: #e2e8f0;">
                        Cancelar
                    </button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white rounded-lg transition-colors" style="background-color: #2563eb;">
                        Salvar Configurações
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Título da Página -->
    <div class="mb-8">
        <h2 class="text-3xl font-bold text-slate-800 mb-2">Bem-vindo às Ferramentas TD SYNNEX</h2>
        <p class="text-slate-600">Selecione uma das opções abaixo para começar</p>
    </div>

    <!-- Cards Grid -->
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.5rem;">
        
        <!-- Card 1: Migração Azure -->
        <div id="migracao-azure" class="bg-white rounded-xl shadow-lg border border-slate-200 overflow-hidden hover:shadow-xl transition-shadow duration-300">
            <div class="p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="p-3 rounded-lg" style="background-color: #dbeafe;">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6" style="color: #2563eb;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15a4.5 4.5 0 0 0 4.5 4.5H18a3.75 3.75 0 0 0 1.332-7.257 3 3 0 0 0-3.758-3.848 5.25 5.25 0 0 0-10.233 2.33A4.502 4.502 0 0 0 2.25 15Z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-slate-800">Migração Azure</h3>
                </div>
                <p class="text-slate-600 text-sm mb-4">Ferramentas para auxiliar na migração entre diferentes modelos de licenciamento Azure.</p>
                
                <ul class="space-y-2">
                    <li>
                        <a href="migracao-azure.php" class="flex items-center gap-2 text-sm text-slate-700 hover:text-blue-600 p-2 rounded-lg hover:bg-slate-50 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                            </svg>
                            Migração CSP x CSP
                        </a>
                    </li>
                    <li>
                        <a href="migracao-azure.php" class="flex items-center gap-2 text-sm text-slate-700 hover:text-blue-600 p-2 rounded-lg hover:bg-slate-50 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                            </svg>
                            Migração MOSP x CSP
                        </a>
                    </li>
                    <li>
                        <a href="migracao-azure.php" class="flex items-center gap-2 text-sm text-slate-700 hover:text-blue-600 p-2 rounded-lg hover:bg-slate-50 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                            </svg>
                            Migração Enterprise x CSP
                        </a>
                    </li>
                    <li>
                        <a href="migracao-azure.php" class="flex items-center gap-2 text-sm text-slate-700 hover:text-blue-600 p-2 rounded-lg hover:bg-slate-50 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                            </svg>
                            Migração Sponsorship (Founders HUB) x CSP
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Card 2: SQL Licensing Advisor -->
        <div class="bg-white rounded-xl shadow-lg border border-slate-200 overflow-hidden hover:shadow-xl transition-shadow duration-300 relative">
            <div class="absolute top-3 right-3">
                <span class="px-2 py-0.5 text-xs font-bold text-white rounded-full" style="background: linear-gradient(135deg, #7c3aed, #a855f7);">NOVO</span>
            </div>
            <div class="p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="p-3 rounded-lg" style="background-color: #ede9fe;">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6" style="color: #7c3aed;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-slate-800">SQL Licensing Advisor</h3>
                </div>
                <p class="text-slate-600 text-sm mb-4">Compare 6 modelos de licenciamento SQL Server 2022 e gere propostas comerciais em PDF.</p>
                
                <ul class="space-y-2">
                    <li>
                        <a href="sql-advisor.php" class="flex items-center gap-2 text-sm text-slate-700 hover:text-violet-600 p-2 rounded-lg hover:bg-violet-50 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                            </svg>
                            Comparar Modelos (ARC, SPLA, CSP, OVS...)
                        </a>
                    </li>
                    <li>
                        <a href="sql-advisor.php" class="flex items-center gap-2 text-sm text-slate-700 hover:text-violet-600 p-2 rounded-lg hover:bg-violet-50 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                            </svg>
                            Gerar Proposta Comercial PDF
                        </a>
                    </li>
                    <li>
                        <a href="sql-advisor.php" class="flex items-center gap-2 text-sm text-slate-700 hover:text-violet-600 p-2 rounded-lg hover:bg-violet-50 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                            </svg>
                            Chat com Especialista IA
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Card 3: Migração M365 Tier 1 to Tier 2 -->
        <div id="migracao-m365" class="bg-white rounded-xl shadow-lg border border-slate-200 overflow-hidden hover:shadow-xl transition-shadow duration-300 relative">
            <div class="absolute top-3 right-3">
                <span class="px-2 py-0.5 text-xs font-bold text-white rounded-full" style="background: linear-gradient(135deg, #ea580c, #f97316);">EM BREVE</span>
            </div>
            <div class="p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="p-3 rounded-lg" style="background-color: #fff7ed;">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6" style="color: #ea580c;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-slate-800">Migração M365</h3>
                </div>
                <p class="text-slate-600 text-sm mb-4">Ferramenta para migração de assinaturas Microsoft 365 de Tier 1 para Tier 2 (Indirect Reseller).</p>
                
                <ul class="space-y-2">
                    <li>
                        <a href="#" class="flex items-center gap-2 text-sm text-slate-700 hover:text-orange-600 p-2 rounded-lg hover:bg-orange-50 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                            </svg>
                            Migração Tier 1 → Tier 2
                        </a>
                    </li>
                    <li>
                        <a href="#" class="flex items-center gap-2 text-sm text-slate-700 hover:text-orange-600 p-2 rounded-lg hover:bg-orange-50 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                            </svg>
                            Mapeamento de SKUs M365
                        </a>
                    </li>
                    <li>
                        <a href="#" class="flex items-center gap-2 text-sm text-slate-700 hover:text-orange-600 p-2 rounded-lg hover:bg-orange-50 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                            </svg>
                            Proposta Comercial M365
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Card 4: Cloud Partner HUB -->
        <div id="cloud-partner-hub" class="bg-white rounded-xl shadow-lg border border-slate-200 overflow-hidden hover:shadow-xl transition-shadow duration-300">
            <div class="p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="p-3 rounded-lg" style="background-color: #ede9fe;">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6" style="color: #7c3aed;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-slate-800">Cloud Partner HUB</h3>
                </div>
                <p class="text-slate-600 text-sm mb-4">Portal de recursos e ferramentas para parceiros de nuvem.</p>
                
                <div class="flex items-center gap-2 text-sm text-violet-600 p-2 rounded-lg bg-violet-50">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                    </svg>
                    Acessar Portal
                </div>
            </div>
        </div>

    </div>

  </div>

  <script>
    // Settings Modal
    const settingsModal = document.getElementById('settingsModal');
    const openSettingsBtn = document.getElementById('openSettings');
    const closeSettingsBtn = document.getElementById('closeSettings');
    const cancelSettingsBtn = document.getElementById('cancelSettings');

    function openSettingsModal() {
        settingsModal.classList.remove('hidden');
    }

    function closeSettingsModal() {
        settingsModal.classList.add('hidden');
    }

    openSettingsBtn.addEventListener('click', openSettingsModal);
    closeSettingsBtn.addEventListener('click', closeSettingsModal);
    cancelSettingsBtn.addEventListener('click', closeSettingsModal);

    settingsModal.addEventListener('click', (e) => {
        if (e.target === settingsModal) {
            closeSettingsModal();
        }
    });
  </script>
</body>
</html>
