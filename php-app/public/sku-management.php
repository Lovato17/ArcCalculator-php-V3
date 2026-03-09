<?php
session_start();

$jsonFile = __DIR__ . '/skus.json';

// Definição de preços padrão (fallback em BRL baseado nos valores passados)
$defaultPrices = [
    'arcStandard' => 0.539,
    'arcEnterprise' => 2.021,
    'splaStandard' => 1131.90,
    'splaEnterprise' => 4611.08,
    'csp1yStandard' => 15226.33,
    'csp1yEnterprise' => 58377.01,
    'csp3yStandard' => 38245.50,
    'csp3yEnterprise' => 146564.40,
    'perpetualStandard' => 32240.30,
    'perpetualEnterprise' => 123602.72,
    'saPct' => 25,
    'ovsStandard' => 12651.28,
    'ovsEnterprise' => 48511.95,
    'exchangeRate' => 5.39
];

// Lê o arquivo JSON
$prices = $defaultPrices;
if (file_exists($jsonFile)) {
    $jsonContent = file_get_contents($jsonFile);
    $decoded = json_decode($jsonContent, true);
    if (is_array($decoded)) {
        $prices = array_merge($defaultPrices, $decoded);
    }
}

// Processa salvamento de preços
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_prices'])) {
    $newPrices = [];
    foreach ($defaultPrices as $key => $val) {
        if (isset($_POST[$key])) {
            $newPrices[$key] = floatval(str_replace(',', '.', str_replace('.', '', $_POST[$key])));
        } else {
            $newPrices[$key] = $val;
        }
    }
    
    // Save to JSON
    file_put_contents($jsonFile, json_encode($newPrices, JSON_PRETTY_PRINT));
    $prices = $newPrices;
    
    $successMessage = "Preços base atualizados com sucesso e salvos no sistema (skus.json)!";
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciamento de SKUs e Preços | TD SYNNEX</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .sku-table { width: 100%; border-collapse: separate; border-spacing: 0; }
        .sku-table thead th { background: #005758; color: #fff; font-size: 0.78rem; font-weight: 600; padding: 12px 16px; text-align: left; letter-spacing: 0.04em; text-transform: uppercase; position: sticky; top: 0; z-index: 10; }
        .sku-table thead th:first-child { border-radius: 12px 0 0 0; }
        .sku-table thead th:last-child { border-radius: 0 12px 0 0; }
        .sku-table tbody td { padding: 0; border-bottom: 1px solid #e8edf3; vertical-align: middle; }
        .sku-table tbody tr:hover td { background: #f0fdfa; }
        .sku-table tbody tr:last-child td:first-child { border-radius: 0 0 0 12px; }
        .sku-table tbody tr:last-child td:last-child { border-radius: 0 0 12px 0; }
        .sku-table input[type="number"] { width: 100%; padding: 10px 14px; border: none; background: transparent; font-size: 0.88rem; font-weight: 500; color: #1e293b; font-family: 'Inter', sans-serif; outline: none; transition: background 0.15s; }
        .sku-table input[type="number"]:focus { background: #e0f2f1; }
        .sku-table input[type="number"]::-webkit-inner-spin-button { opacity: 0.3; }
        .model-badge { display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 6px; font-size: 0.7rem; font-weight: 700; letter-spacing: 0.03em; text-transform: uppercase; }
        .badge-arc { background: #dbeafe; color: #1d4ed8; }
        .badge-spla { background: #ffedd5; color: #c2410c; }
        .badge-csp1 { background: #f3e8ff; color: #7c3aed; }
        .badge-csp3 { background: #e0e7ff; color: #4338ca; }
        .badge-perp { background: #ccfbf1; color: #0f766e; }
        .badge-ovs { background: #ffe4e6; color: #be123c; }
        .badge-fx { background: #f1f5f9; color: #475569; }
        .edition-std { color: #475569; font-weight: 500; }
        .edition-ent { color: #1e293b; font-weight: 600; }
        .unit-label { font-size: 0.72rem; color: #94a3b8; font-weight: 500; white-space: nowrap; }

        /* ===== MOBILE RESPONSIVE ===== */
        @media (max-width: 768px) {
          .sku-table { min-width: 520px; }
          .sku-table thead th { font-size: 0.7rem; padding: 10px 10px; }
          .sku-table input[type="number"] { padding: 8px 10px; font-size: 0.82rem; }
          .model-badge { font-size: 0.62rem; padding: 3px 7px; gap: 4px; }
          .unit-label { font-size: 0.62rem; white-space: normal; }
          .container { padding-left: 1rem; padding-right: 1rem; }
        }
        @media (max-width: 640px) {
          .save-btn-wrap { justify-content: stretch !important; }
          .save-btn-wrap button { width: 100%; justify-content: center; }
          h1 { font-size: 1.25rem !important; }
        }
    </style>
</head>
<body class="bg-slate-50 min-h-screen text-slate-900">
    <div class="container mx-auto p-4 md:p-8 max-w-[1200px]">
        
        <!-- Header -->
        <header class="flex flex-col md:flex-row justify-between items-center mb-8 pb-6 border-b border-slate-200">
            <div class="flex items-center gap-4">
                <div class="h-12 w-12 rounded-xl flex items-center justify-center shadow-md" style="background:linear-gradient(135deg,#002829,#005758);">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-7 h-7 text-white">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Tabela de Preços Base</h1>
                    <p class="text-sm text-slate-500 font-medium">SKUs SQL Server 2022 — valores refletidos na Calculadora e Advisor</p>
                </div>
            </div>
            <div class="flex items-center gap-3 mt-4 md:mt-0">
                <a href="home.php" class="text-sm font-medium text-slate-600 hover:text-slate-900 px-3 py-2 rounded-lg hover:bg-slate-100 transition">Home</a>
                <a href="sql-advisor.php" class="text-sm font-medium text-white px-4 py-2 rounded-lg shadow transition" style="background:#005758;">Voltar ao Advisor</a>
            </div>
        </header>

        <?php if (isset($successMessage)): ?>
        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-xl flex items-center gap-3">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-emerald-600 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
            <span class="text-emerald-800 font-medium text-sm"><?php echo $successMessage; ?></span>
        </div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="save_prices" value="1">

            <div class="bg-white rounded-2xl shadow-lg border border-slate-100 overflow-hidden">
                <div style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
                <table class="sku-table">
                    <thead>
                        <tr>
                            <th style="width:200px;">Modelo</th>
                            <th style="width:100px;">Edição</th>
                            <th>Valor (R$)</th>
                            <th style="width:180px;">Unidade</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Azure ARC -->
                        <tr>
                            <td rowspan="2" style="padding:12px 16px;">
                                <span class="model-badge badge-arc">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:12px;height:12px;"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15a4.5 4.5 0 0 0 4.5 4.5H18a3.75 3.75 0 0 0 .75-7.425A4.502 4.502 0 0 0 14.25 9 4.5 4.5 0 0 0 6 10.5a4.5 4.5 0 0 0-3.75 4.5Z"/></svg>
                                    Azure ARC
                                </span>
                                <div class="text-xs text-slate-400 mt-1">Pay-As-You-Go</div>
                            </td>
                            <td style="padding:0 16px;"><span class="edition-std">Standard</span></td>
                            <td><input type="number" step="0.001" name="arcStandard" value="<?php echo htmlspecialchars($prices['arcStandard']); ?>"></td>
                            <td style="padding:0 16px;"><span class="unit-label">R$ / vCore / hora</span></td>
                        </tr>
                        <tr>
                            <td style="padding:0 16px;"><span class="edition-ent">Enterprise</span></td>
                            <td><input type="number" step="0.001" name="arcEnterprise" value="<?php echo htmlspecialchars($prices['arcEnterprise']); ?>"></td>
                            <td style="padding:0 16px;"><span class="unit-label">R$ / vCore / hora</span></td>
                        </tr>

                        <!-- SPLA -->
                        <tr>
                            <td rowspan="2" style="padding:12px 16px;">
                                <span class="model-badge badge-spla">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:12px;height:12px;"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/></svg>
                                    SPLA
                                </span>
                                <div class="text-xs text-slate-400 mt-1">Mensal recorrente</div>
                            </td>
                            <td style="padding:0 16px;"><span class="edition-std">Standard</span></td>
                            <td><input type="number" step="0.01" name="splaStandard" value="<?php echo htmlspecialchars($prices['splaStandard']); ?>"></td>
                            <td style="padding:0 16px;"><span class="unit-label">R$ / pack 2-core / mês</span></td>
                        </tr>
                        <tr>
                            <td style="padding:0 16px;"><span class="edition-ent">Enterprise</span></td>
                            <td><input type="number" step="0.01" name="splaEnterprise" value="<?php echo htmlspecialchars($prices['splaEnterprise']); ?>"></td>
                            <td style="padding:0 16px;"><span class="unit-label">R$ / pack 2-core / mês</span></td>
                        </tr>

                        <!-- CSP 1 Ano -->
                        <tr>
                            <td rowspan="2" style="padding:12px 16px;">
                                <span class="model-badge badge-csp1">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:12px;height:12px;"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg>
                                    CSP 1 Ano
                                </span>
                                <div class="text-xs text-slate-400 mt-1">Upfront anual</div>
                            </td>
                            <td style="padding:0 16px;"><span class="edition-std">Standard</span></td>
                            <td><input type="number" step="0.01" name="csp1yStandard" value="<?php echo htmlspecialchars($prices['csp1yStandard']); ?>"></td>
                            <td style="padding:0 16px;"><span class="unit-label">R$ / pack 2-core / ano</span></td>
                        </tr>
                        <tr>
                            <td style="padding:0 16px;"><span class="edition-ent">Enterprise</span></td>
                            <td><input type="number" step="0.01" name="csp1yEnterprise" value="<?php echo htmlspecialchars($prices['csp1yEnterprise']); ?>"></td>
                            <td style="padding:0 16px;"><span class="unit-label">R$ / pack 2-core / ano</span></td>
                        </tr>

                        <!-- CSP 3 Anos -->
                        <tr>
                            <td rowspan="2" style="padding:12px 16px;">
                                <span class="model-badge badge-csp3">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:12px;height:12px;"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5m-9-6h.008v.008H12v-.008ZM12 15h.008v.008H12V15Zm0 2.25h.008v.008H12v-.008ZM9.75 15h.008v.008H9.75V15Zm0 2.25h.008v.008H9.75v-.008ZM7.5 15h.008v.008H7.5V15Zm0 2.25h.008v.008H7.5v-.008Zm6.75-4.5h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V15Zm0 2.25h.008v.008h-.008v-.008Zm2.25-4.5h.008v.008H16.5v-.008Zm0 2.25h.008v.008H16.5V15Z"/></svg>
                                    CSP 3 Anos
                                </span>
                                <div class="text-xs text-slate-400 mt-1">Upfront trienal</div>
                            </td>
                            <td style="padding:0 16px;"><span class="edition-std">Standard</span></td>
                            <td><input type="number" step="0.01" name="csp3yStandard" value="<?php echo htmlspecialchars($prices['csp3yStandard']); ?>"></td>
                            <td style="padding:0 16px;"><span class="unit-label">R$ / pack 2-core / 3 anos</span></td>
                        </tr>
                        <tr>
                            <td style="padding:0 16px;"><span class="edition-ent">Enterprise</span></td>
                            <td><input type="number" step="0.01" name="csp3yEnterprise" value="<?php echo htmlspecialchars($prices['csp3yEnterprise']); ?>"></td>
                            <td style="padding:0 16px;"><span class="unit-label">R$ / pack 2-core / 3 anos</span></td>
                        </tr>

                        <!-- Perpétuo -->
                        <tr>
                            <td rowspan="3" style="padding:12px 16px;">
                                <span class="model-badge badge-perp">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:12px;height:12px;"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z"/></svg>
                                    Perpétuo + SA
                                </span>
                                <div class="text-xs text-slate-400 mt-1">Licença permanente</div>
                            </td>
                            <td style="padding:0 16px;"><span class="edition-std">Standard</span></td>
                            <td><input type="number" step="0.01" name="perpetualStandard" value="<?php echo htmlspecialchars($prices['perpetualStandard']); ?>"></td>
                            <td style="padding:0 16px;"><span class="unit-label">R$ / pack 2-core (licença)</span></td>
                        </tr>
                        <tr>
                            <td style="padding:0 16px;"><span class="edition-ent">Enterprise</span></td>
                            <td><input type="number" step="0.01" name="perpetualEnterprise" value="<?php echo htmlspecialchars($prices['perpetualEnterprise']); ?>"></td>
                            <td style="padding:0 16px;"><span class="unit-label">R$ / pack 2-core (licença)</span></td>
                        </tr>
                        <tr>
                            <td style="padding:0 16px;"><span class="edition-std" style="color:#0f766e;font-weight:600;">SA %</span></td>
                            <td><input type="number" step="0.1" name="saPct" value="<?php echo htmlspecialchars($prices['saPct']); ?>"></td>
                            <td style="padding:0 16px;"><span class="unit-label">% anual sobre licença</span></td>
                        </tr>

                        <!-- OVS -->
                        <tr>
                            <td rowspan="2" style="padding:12px 16px;">
                                <span class="model-badge badge-ovs">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:12px;height:12px;"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 12c0-1.232-.046-2.453-.138-3.662a4.006 4.006 0 0 0-3.7-3.7 48.678 48.678 0 0 0-7.324 0 4.006 4.006 0 0 0-3.7 3.7c-.017.22-.032.441-.046.662M19.5 12l3-3m-3 3-3-3m-12 3c0 1.232.046 2.453.138 3.662a4.006 4.006 0 0 0 3.7 3.7 48.656 48.656 0 0 0 7.324 0 4.006 4.006 0 0 0 3.7-3.7c.017-.22.032-.441.046-.662M4.5 12l3 3m-3-3-3 3"/></svg>
                                    OVS
                                </span>
                                <div class="text-xs text-slate-400 mt-1">Assinatura anual</div>
                            </td>
                            <td style="padding:0 16px;"><span class="edition-std">Standard</span></td>
                            <td><input type="number" step="0.01" name="ovsStandard" value="<?php echo htmlspecialchars($prices['ovsStandard']); ?>"></td>
                            <td style="padding:0 16px;"><span class="unit-label">R$ / pack 2-core / ano</span></td>
                        </tr>
                        <tr>
                            <td style="padding:0 16px;"><span class="edition-ent">Enterprise</span></td>
                            <td><input type="number" step="0.01" name="ovsEnterprise" value="<?php echo htmlspecialchars($prices['ovsEnterprise']); ?>"></td>
                            <td style="padding:0 16px;"><span class="unit-label">R$ / pack 2-core / ano</span></td>
                        </tr>

                        <!-- Câmbio -->
                        <tr style="background:#f8fafc;">
                            <td style="padding:12px 16px;">
                                <span class="model-badge badge-fx">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:12px;height:12px;"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                    Câmbio
                                </span>
                                <div class="text-xs text-slate-400 mt-1">USD → BRL</div>
                            </td>
                            <td style="padding:0 16px;"><span class="edition-std" style="color:#475569;font-weight:600;">Taxa</span></td>
                            <td><input type="number" step="0.01" name="exchangeRate" value="<?php echo htmlspecialchars($prices['exchangeRate']); ?>"></td>
                            <td style="padding:0 16px;"><span class="unit-label">R$ por 1 USD</span></td>
                        </tr>
                    </tbody>
                </table>
                </div><!-- /overflow-x-auto -->
            </div>

            <div class="save-btn-wrap flex justify-end pt-6 mt-6 mb-10">
                <button type="submit" class="px-8 py-3 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transition-all" style="background:linear-gradient(135deg,#002829,#005758);">
                    <span class="flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 3.75H6.912a2.25 2.25 0 0 0-2.15 1.588L2.35 13.177a2.25 2.25 0 0 0-.1.661V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18v-4.162c0-.224-.034-.447-.1-.661L19.24 5.338a2.25 2.25 0 0 0-2.15-1.588H15M2.25 13.5h3.86a2.25 2.25 0 0 1 2.012 1.244l.256.512a2.25 2.25 0 0 0 2.013 1.244h3.218a2.25 2.25 0 0 0 2.013-1.244l.256-.512a2.25 2.25 0 0 1 2.013-1.244h3.859M12 3v8.25m0 0-3-3m3 3 3-3" />
                        </svg>
                        Salvar Preços Base (skus.json)
                    </span>
                </button>
            </div>
            
        </form>
    </div>
</body>
</html>
