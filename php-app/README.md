# Azure ARC vs SPLA Calculator - PHP

Calculadora de custos para comparação entre Azure ARC Pay-As-You-Go e licenciamento SPLA tradicional para SQL Server.

## 📁 Estrutura do Projeto

```
php-app/
├── bin/
│   └── php/              # Binários PHP (opcional)
├── public/               # Arquivos públicos (document root)
│   ├── css/
│   │   └── style.css     # Estilos Tailwind compilados
│   ├── js/
│   │   └── app.js        # JavaScript (geração de PDF)
│   ├── home.php          # Página inicial com cards
│   ├── index.php         # Calculadora principal
│   ├── logo.png          # Logo TD SYNNEX
│   ├── logo_b64_full.txt # Logo em Base64
│   └── logo_base64.txt   # Logo em Base64 (alternativo)
├── src/
│   └── Calculator.php    # Classe de cálculos
└── templates/
    ├── calculator_form.php  # Formulário da calculadora
    └── layout.php           # Template principal
```

## 🚀 Como Executar

### Requisitos
- PHP 8.0 ou superior

### Iniciar Servidor de Desenvolvimento

```bash
cd php-app
php -S localhost:8000 -t public
```

Acesse: http://localhost:8000/home.php

## 📱 Páginas

| Página | URL | Descrição |
|--------|-----|-----------|
| Home | `/home.php` | Página inicial com cards de navegação |
| Calculadora | `/index.php` | Calculadora Azure ARC vs SPLA |

## ⚙️ Funcionalidades

- **Múltiplas Estimativas**: Suporte a abas para diferentes cenários
- **Configuração de Preços**: Modal para ajustar preços SPLA, ARC e câmbio
- **Geração de PDF**: Relatório completo com gráficos comparativos
- **Cálculos em Tempo Real**: Atualização automática dos valores

## 💰 Parâmetros de Cálculo

### Azure ARC (Pay-As-You-Go)
- SQL Standard: $0.10/vCore/hora
- SQL Enterprise: $0.375/vCore/hora

### SPLA (Licenciamento Tradicional)
- SQL Standard: $210/pack de 2 cores
- SQL Enterprise: $855.47/pack de 2 cores

## 🛠️ Tecnologias

- **PHP 8.2** - Backend
- **Tailwind CSS** - Estilização
- **jsPDF** - Geração de PDF no cliente
- **JavaScript** - Interatividade

## 📄 Licença

Projeto interno TD SYNNEX.
