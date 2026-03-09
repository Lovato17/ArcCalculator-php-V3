<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Azure ARC vs SPLA - Calculadora (PHP) | TD SYNNEX</title>
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
                    Azure ARC <span class="text-slate-400 font-light">vs</span> SPLA
                </h1>
                <p class="text-sm text-slate-500 font-medium">Calculadora de Custos</p>
            </div>
        </div>
        <!-- Menu de Navegação -->
        <div class="flex items-center gap-6">
            <nav class="flex items-center gap-4">
                <a href="home.php" class="text-sm font-medium text-slate-600 hover:text-blue-600 transition-colors">Home</a>
                <a href="sku-management.php" class="text-sm font-medium text-slate-600 hover:text-blue-600 transition-colors">SKUs e Preços</a>
                <a href="home.php" class="text-sm font-medium text-slate-600 hover:text-blue-600 transition-colors">Migrações Azure</a>
            </nav>
            <!-- Botão Settings -->
            <button id="openSettings" class="p-2 bg-blue-100 hover:bg-blue-200 rounded-lg text-blue-600 hover:text-blue-700 transition-all duration-200 border border-blue-200" title="Configurações">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                </svg>
            </button>
        </div>
    </header>

    <!-- Modal de Configurações -->
    <div id="settingsModal" class="fixed inset-0 bg-black/30 backdrop-blur-sm flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-xl shadow-2xl p-8 mx-auto" style="width: 500px;">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-blue-100 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-blue-600">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-slate-900">Configurações</h3>
                </div>
                <button id="closeSettings" class="p-1.5 hover:bg-slate-100 rounded-lg text-slate-400 hover:text-slate-600 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="space-y-4">
                <!-- Opção: Gerenciar SKUs e Preços -->
                <a href="sku-management.php" class="block p-4 bg-gradient-to-br from-blue-50 to-blue-100 hover:from-blue-100 hover:to-blue-200 border-2 border-blue-200 hover:border-blue-300 rounded-lg transition-all duration-200 group">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-blue-600 rounded-lg group-hover:scale-110 transition-transform">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6 text-white">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-semibold text-slate-900 mb-1">SKUs e Preços</h4>
                            <p class="text-sm text-slate-600">Configure os preços dos produtos SPLA e Azure ARC</p>
                        </div>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-blue-600 group-hover:translate-x-1 transition-transform">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                        </svg>
                    </div>
                </a>

                <!-- Futuras opções de configuração podem ser adicionadas aqui -->
                <div class="p-4 bg-slate-50 border-2 border-dashed border-slate-300 rounded-lg opacity-60">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-slate-300 rounded-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6 text-slate-500">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.343 3.94c.09-.542.56-.94 1.11-.94h1.093c.55 0 1.02.398 1.11.94l.149.894c.07.424.384.764.78.93.398.164.855.142 1.205-.108l.737-.527a1.125 1.125 0 0 1 1.45.12l.773.774c.39.389.44 1.002.12 1.45l-.527.737c-.25.35-.272.806-.107 1.204.165.397.505.71.93.78l.893.15c.543.09.94.559.94 1.109v1.094c0 .55-.397 1.02-.94 1.11l-.894.149c-.424.07-.764.383-.929.78-.165.398-.143.854.107 1.204l.527.738c.32.447.269 1.06-.12 1.45l-.774.773a1.125 1.125 0 0 1-1.449.12l-.738-.527c-.35-.25-.806-.272-1.203-.107-.398.165-.71.505-.781.929l-.149.894c-.09.542-.56.94-1.11.94h-1.094c-.55 0-1.019-.398-1.11-.94l-.148-.894c-.071-.424-.384-.764-.781-.93-.398-.164-.854-.142-1.204.108l-.738.527c-.447.32-1.06.269-1.45-.12l-.773-.774a1.125 1.125 0 0 1-.12-1.45l.527-.737c.25-.35.272-.806.108-1.204-.165-.397-.506-.71-.93-.78l-.894-.15c-.542-.09-.94-.56-.94-1.109v-1.094c0-.55.398-1.02.94-1.11l.894-.149c.424-.07.765-.383.93-.78.165-.398.143-.854-.108-1.204l-.526-.738a1.125 1.125 0 0 1 .12-1.45l.773-.773a1.125 1.125 0 0 1 1.45-.12l.737.527c.35.25.807.272 1.204.107.397-.165.71-.505.78-.929l.15-.894Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-semibold text-slate-600 mb-1">Outras Configurações</h4>
                            <p class="text-sm text-slate-500">Em breve mais opções estarão disponíveis</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6 pt-4 border-t border-slate-200">
                <button type="button" id="cancelSettings" class="w-full px-4 py-2 text-sm font-medium text-slate-600 hover:text-slate-800 hover:bg-slate-100 rounded-lg transition-colors">
                    Fechar
                </button>
            </div>
        </div>
    </div>

    <!-- Abas de Estimativas -->
    <div class="flex items-center gap-2 overflow-x-auto pb-1 mb-6 border-b border-slate-200">
        <?php foreach ($_SESSION['estimates'] as $estimate): ?>
        <div class="relative group flex-shrink-0">
            <a href="?tab=<?php echo $estimate['id']; ?>" 
               class="flex items-center gap-2 px-6 py-3 rounded-t-lg font-medium text-sm transition-all duration-200 border-2 border-b-0 relative min-w-[160px] group/tab
               <?php echo $activeTab === $estimate['id'] 
                   ? 'bg-gradient-to-br from-white to-blue-50/30 border-tdsynnex-blue/30 text-tdsynnex-blue shadow-lg shadow-blue-100/50 translate-y-[2px]' 
                   : 'bg-white/40 border-slate-200/60 text-slate-600 hover:bg-white hover:border-slate-300 hover:text-slate-900 hover:shadow-md'; ?>">
                
                <!-- Ícone da aba -->
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 <?php echo $activeTab === $estimate['id'] ? 'text-tdsynnex-blue' : 'text-slate-400 group-hover/tab:text-slate-600'; ?>">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 0 0 6 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0 1 18 16.5h-2.25m-7.5 0h7.5m-7.5 0-1 3m8.5-3 1 3m0 0 .5 1.5m-.5-1.5h-9.5m0 0-.5 1.5M9 11.25v1.5M12 9v3.75m3-6v6" />
                </svg>
                
                <span id="tab-name-<?php echo $estimate['id']; ?>" class="flex-1 truncate"><?php echo htmlspecialchars($estimate['name']); ?></span>
                
                <!-- Indicador ativo -->
                <?php if ($activeTab === $estimate['id']): ?>
                <div class="absolute bottom-0 left-0 right-0 h-[3px] bg-gradient-to-r from-tdsynnex-blue via-blue-500 to-tdsynnex-blue rounded-t-full"></div>
                <?php endif; ?>
            </a>
            
            <!-- Botões de ação -->
            <?php if ($activeTab === $estimate['id']): ?>
            <div class="absolute right-2 top-1/2 -translate-y-1/2 flex gap-1 opacity-0 group-hover:opacity-100 transition-all duration-200 z-20 bg-gradient-to-r from-transparent via-white to-white pl-4 pr-1">
                <button onclick="renameTab(<?php echo $estimate['id']; ?>, '<?php echo htmlspecialchars(addslashes($estimate['name'])); ?>')" 
                        class="p-1.5 hover:bg-blue-50 rounded-md text-slate-400 hover:text-tdsynnex-blue transition-all duration-150 hover:scale-110" 
                        title="Renomear estimativa">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3.5 h-3.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                    </svg>
                </button>
                <?php if (count($_SESSION['estimates']) > 1): ?>
                <form method="POST" style="display:inline;" onsubmit="return confirm('⚠️ Deseja realmente excluir esta estimativa?\n\nEsta ação não pode ser desfeita.');">
                    <input type="hidden" name="remove_estimate" value="<?php echo $estimate['id']; ?>">
                    <button type="submit" class="p-1.5 hover:bg-red-50 rounded-md text-slate-400 hover:text-red-500 transition-all duration-150 hover:scale-110" title="Excluir estimativa">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3.5 h-3.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                        </svg>
                    </button>
                </form>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
        
        <!-- Botão adicionar nova aba -->
        <form method="POST" style="display:inline;" class="flex-shrink-0">
            <input type="hidden" name="add_estimate" value="1">
            <button type="submit" class="flex items-center gap-2 px-4 py-3 text-slate-500 hover:text-tdsynnex-blue hover:bg-blue-50 rounded-t-lg transition-all duration-200 border-2 border-b-0 border-transparent hover:border-blue-200/50 group ml-1" title="Adicionar nova estimativa">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 group-hover:rotate-90 transition-transform duration-300">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span class="text-sm font-medium">Nova</span>
            </button>
        </form>
    </div>

    <?php 
      // Inclui o formulário da calculadora
      include 'calculator_form.php'; 
    ?>

    <script>
    function renameTab(tabId, currentName) {
        // Cria um modal customizado mais elegante
        const modal = document.createElement('div');
        modal.className = 'fixed inset-0 bg-black/30 backdrop-blur-sm flex items-center justify-center z-50 animate-fadeIn';
        modal.innerHTML = `
            <div class="bg-white rounded-xl shadow-2xl p-6 w-full max-w-md mx-4 animate-scaleIn">
                <div class="flex items-center gap-3 mb-4">
                    <div class="p-2 bg-blue-50 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-tdsynnex-blue">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-slate-900">Renomear Estimativa</h3>
                </div>
                <input type="text" id="rename-input" value="${currentName}" 
                       class="w-full px-4 py-2.5 border-2 border-slate-200 rounded-lg focus:outline-none focus:border-tdsynnex-blue focus:ring-4 focus:ring-blue-100 transition-all mb-4"
                       placeholder="Digite o novo nome...">
                <div class="flex gap-3 justify-end">
                    <button onclick="this.closest('.fixed').remove()" 
                            class="px-4 py-2 text-slate-600 hover:bg-slate-100 rounded-lg font-medium transition-colors">
                        Cancelar
                    </button>
                    <button onclick="submitRename(${tabId})" 
                            class="px-4 py-2 bg-tdsynnex-blue text-white hover:bg-blue-700 rounded-lg font-medium transition-colors shadow-md hover:shadow-lg">
                        Salvar
                    </button>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        document.getElementById('rename-input').focus();
        document.getElementById('rename-input').select();
        
        // Fechar ao clicar fora
        modal.addEventListener('click', (e) => {
            if (e.target === modal) modal.remove();
        });
        
        // Enter para salvar, ESC para cancelar
        document.getElementById('rename-input').addEventListener('keydown', (e) => {
            if (e.key === 'Enter') submitRename(tabId);
            if (e.key === 'Escape') modal.remove();
        });
    }
    
    function submitRename(tabId) {
        const newName = document.getElementById('rename-input').value.trim();
        if (newName) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="rename_estimate" value="${tabId}">
                <input type="hidden" name="new_name" value="${newName}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    }
    
    // Adiciona animações CSS
    const style = document.createElement('style');
    style.textContent = `
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes scaleIn {
            from { transform: scale(0.9); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
        .animate-fadeIn {
            animation: fadeIn 0.2s ease-out;
        }
        .animate-scaleIn {
            animation: scaleIn 0.2s ease-out;
        }
    `;
    document.head.appendChild(style);

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

  </div>

  <!-- Dados para JS -->
  <script>
    window.calculatorData = {
      estimate: <?php echo json_encode($currentEstimate); ?>,
      result: <?php echo json_encode($result); ?>
    };
  </script>

  <!-- JS para interatividade e PDF -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <script src="js/app.js?v=<?php echo time(); ?>"></script>
</body>
</html>
