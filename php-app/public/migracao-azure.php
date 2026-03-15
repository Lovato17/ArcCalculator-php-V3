<?php
// filepath: public/migracao-azure.php
session_start();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Migração Azure - TD SYNNEX Tools</title>
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
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Tools</h1>
                <p class="text-sm text-slate-500 font-medium">Ferramentas e Calculadoras</p>
            </div>
        </div>
        <nav class="flex items-center gap-4">
            <a href="home.php" class="text-sm font-medium text-slate-600 hover:text-blue-600 transition-colors">Home</a>
            <a href="migracao-azure.php" class="text-sm font-medium text-blue-600 border-b-2 border-blue-600 pb-1">Migração Azure</a>
            <a href="sql-advisor.php" class="text-sm font-medium text-slate-600 hover:text-blue-600 transition-colors">SQL Advisor</a>
            <a href="home.php#cloud-partner-hub" class="text-sm font-medium text-slate-600 hover:text-blue-600 transition-colors">Cloud Partner HUB</a>
        </nav>
    </header>

    <!-- Título -->
    <div class="mb-8">
        <div class="flex items-center gap-3 mb-2">
            <div class="p-2 rounded-lg" style="background-color: #dbeafe;">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6" style="color: #2563eb;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15a4.5 4.5 0 0 0 4.5 4.5H18a3.75 3.75 0 0 0 1.332-7.257 3 3 0 0 0-3.758-3.848 5.25 5.25 0 0 0-10.233 2.33A4.502 4.502 0 0 0 2.25 15Z" />
                </svg>
            </div>
            <h2 class="text-3xl font-bold text-slate-800">Migração Azure</h2>
        </div>
        <p class="text-slate-600">Selecione o tipo de migração que deseja realizar</p>
    </div>

    <!-- Card Destaque: Análise Técnica -->
    <div class="bg-white rounded-xl shadow-lg border-2 overflow-hidden mb-4" style="border-color:#2563eb;">
        <div class="p-6 flex items-start justify-between gap-6">
            <div class="flex items-start gap-4 flex-1">
                <div class="p-3 rounded-lg shrink-0" style="background-color:#dbeafe;">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-7 h-7" style="color:#2563eb;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <h3 class="text-xl font-bold text-slate-800">Análise Técnica de Recursos Azure</h3>
                        <span class="px-2 py-0.5 text-xs font-bold text-white rounded-full" style="background:#16a34a;">DISPONÍVEL</span>
                    </div>
                    <p class="text-slate-600 text-sm mb-0">Faça upload de uma planilha de recursos Azure e descubra quais podem ser migrados entre assinaturas. Gere relatórios em PDF e Excel com análise de 16.000+ tipos de recursos.</p>
                </div>
            </div>
            <a href="analise-migracao.php" class="shrink-0 flex items-center gap-2 px-5 py-2.5 rounded-lg text-sm font-semibold text-white transition-all" style="background:#2563eb; white-space:nowrap;">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                </svg>
                Abrir Ferramenta
            </a>
        </div>
    </div>

    <!-- Card Destaque: Análise Financeira -->
    <div class="bg-white rounded-xl shadow-lg border-2 overflow-hidden mb-6" style="border-color:#005758;">
        <div class="p-6 flex items-start justify-between gap-6">
            <div class="flex items-start gap-4 flex-1">
                <div class="p-3 rounded-lg shrink-0" style="background-color:#ccfbf1;">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-7 h-7" style="color:#005758;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <h3 class="text-xl font-bold text-slate-800">Análise Financeira de Migração</h3>
                        <span class="px-2 py-0.5 text-xs font-bold text-white rounded-full" style="background:#005758;">EM BREVE</span>
                    </div>
                    <p class="text-slate-600 text-sm mb-0">Calcule o impacto financeiro da migração Azure, compare custos entre modelos de contrato e projete economias ao longo do tempo com base nos recursos do cliente.</p>
                </div>
            </div>
            <a href="analise-financeira.php" class="shrink-0 flex items-center gap-2 px-5 py-2.5 rounded-lg text-sm font-semibold text-white transition-all" style="background:#005758; white-space:nowrap;">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                </svg>
                Abrir Ferramenta
            </a>
        </div>
    </div>

    <!-- Cards de Migração -->
    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem;">

        <!-- Card 1: CSP x CSP -->
        <div class="bg-white rounded-xl shadow-lg border border-slate-200 overflow-hidden hover:shadow-xl transition-shadow duration-300">
            <div class="p-6">
                <div class="flex items-center gap-3 mb-3">
                    <div class="p-3 rounded-lg" style="background-color: #dbeafe;">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6" style="color: #2563eb;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-slate-800">Migração CSP → CSP</h3>
                </div>
                <p class="text-slate-600 text-sm mb-5">Transferência de assinaturas entre parceiros CSP, mantendo continuidade dos serviços e sem interrupção para o cliente final.</p>
                <div class="flex items-center gap-2 text-sm text-blue-500 bg-blue-50 rounded-lg px-3 py-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 shrink-0">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
                    </svg>
                    Em breve disponível
                </div>
            </div>
        </div>

        <!-- Card 2: MOSP x CSP -->
        <div class="bg-white rounded-xl shadow-lg border border-slate-200 overflow-hidden hover:shadow-xl transition-shadow duration-300">
            <div class="p-6">
                <div class="flex items-center gap-3 mb-3">
                    <div class="p-3 rounded-lg" style="background-color: #d1fae5;">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6" style="color: #059669;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 7.5 7.5 3m0 0L12 7.5M7.5 3v13.5m13.5 0L16.5 21m0 0L12 16.5m4.5 4.5V7.5" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-slate-800">Migração MOSP → CSP</h3>
                </div>
                <p class="text-slate-600 text-sm mb-5">Migração de contas Pay-As-You-Go (MOSP) para o modelo CSP, habilitando suporte gerenciado e faturamento consolidado pelo parceiro.</p>
                <div class="flex items-center gap-2 text-sm text-blue-500 bg-blue-50 rounded-lg px-3 py-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 shrink-0">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
                    </svg>
                    Em breve disponível
                </div>
            </div>
        </div>

        <!-- Card 3: Enterprise x CSP -->
        <div class="bg-white rounded-xl shadow-lg border border-slate-200 overflow-hidden hover:shadow-xl transition-shadow duration-300">
            <div class="p-6">
                <div class="flex items-center gap-3 mb-3">
                    <div class="p-3 rounded-lg" style="background-color: #fef3c7;">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6" style="color: #d97706;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-slate-800">Migração Enterprise → CSP</h3>
                </div>
                <p class="text-slate-600 text-sm mb-5">Transição de contratos Enterprise Agreement (EA) para o modelo CSP, oferecendo maior flexibilidade e controle por workload.</p>
                <div class="flex items-center gap-2 text-sm text-blue-500 bg-blue-50 rounded-lg px-3 py-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 shrink-0">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
                    </svg>
                    Em breve disponível
                </div>
            </div>
        </div>

        <!-- Card 4: Sponsorship (Founders HUB) x CSP -->
        <div class="bg-white rounded-xl shadow-lg border border-slate-200 overflow-hidden hover:shadow-xl transition-shadow duration-300">
            <div class="p-6">
                <div class="flex items-center gap-3 mb-3">
                    <div class="p-3 rounded-lg" style="background-color: #fce7f3;">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6" style="color: #db2777;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-slate-800">Migração Sponsorship (Founders HUB) → CSP</h3>
                </div>
                <p class="text-slate-600 text-sm mb-5">Migração de créditos do programa Microsoft for Startups Founders HUB para assinaturas CSP regulares com suporte completo.</p>
                <div class="flex items-center gap-2 text-sm text-blue-500 bg-blue-50 rounded-lg px-3 py-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 shrink-0">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
                    </svg>
                    Em breve disponível
                </div>
            </div>
        </div>

    </div>

  </div>
</body>
</html>
