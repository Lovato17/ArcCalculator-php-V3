# Copilot Instructions — ArcCalculator-php (TD SYNNEX)

## Visão Geral do Projeto

Aplicação PHP interna da **TD SYNNEX** para o time de vendas. Compara modelos de licenciamento do SQL Server 2022 e gera propostas comerciais em PDF, destacando o **Azure ARC PAYG** como solução recomendada.

## Stack Técnica

- **PHP 8.0+** (sem framework)
- **Tailwind CSS** compilado em `public/css/style.css`
- **JavaScript Vanilla** (sem jQuery, React, Vue)
- **jsPDF** (geração de PDF no cliente, via CDN)
- **Sessões PHP** para persistência de dados
- **OpenAI API** (gpt-4o) para chat especialista

## Estrutura do Projeto

```
php-app/
├── public/                  ← document root
│   ├── css/style.css
│   ├── js/app.js
│   ├── home.php             ← Página inicial com cards
│   ├── index.php            ← Calculadora ARC vs SPLA (original)
│   ├── sql-advisor.php      ← SQL Licensing Advisor (6 modelos)
│   ├── chat-api.php         ← Endpoint do chat IA
│   ├── sku-management.php   ← Gestão de SKUs
│   └── logo.png
├── src/
│   ├── Calculator.php       ← Calculadora original (ARC vs SPLA)
│   ├── LicensingAdvisor.php ← Comparação dos 6 modelos
│   ├── config/
│   ├── models/
│   └── services/
└── templates/
    ├── layout.php            ← Template da calculadora original
    └── calculator_form.php
```

## Modelos de Licenciamento SQL Server 2022

### 1. Azure ARC PAYG (Pay-As-You-Go)
- Cobrança por **vCore/hora**
- **NÃO usa packs** — cobra exatamente pelos vCores alocados
- Virtualização ilimitada nativa
- Funciona on-premises sem migrar para nuvem
- Sem necessidade de Software Assurance

### 2. SPLA (Service Provider License Agreement)
- Mensal, **packs de 2 cores**
- **ÚNICA opção legal** para hospedar SQL Server para terceiros comercialmente

### 3. CSP 1 Ano Upfront
- Contrato anual com pagamento antecipado
- Packs de 2 cores

### 4. CSP 3 Anos Upfront
- Contrato trienal com pagamento antecipado
- Packs de 2 cores
- Desconto em relação ao CSP anual

### 5. Software Perpétuo + SA
- CapEx, packs de 2 cores, licença permanente
- Virtualização Máxima **SOMENTE** com SA ativo
- SA = ~25% do valor da licença/ano

### 6. OVS (Open Value Subscription)
- OpEx anual, packs de 2 cores
- SA incluso nativamente
- Virtualização Máxima disponível

## Regras Críticas de Cálculo

### Regra de Packs (TODOS os modelos EXCETO Azure ARC)
```php
$packs = max(2, ceil($vCores / 2));
```
- Mínimo de **2 packs (4 cores)** por VM ou processador físico
- **NUNCA** usar `ceil($vCores / 2)` sem o `max(2, ...)`

### Azure ARC (exceção — sem packs)
```php
$custo = $vCores * $precoVCoreHora * $horasMes;
```
- **NUNCA** aplicar lógica de packs ao Azure ARC

## Preços Padrão

> **Todos os preços estão armazenados em BRL (Real).** Para exibir em USD, o sistema divide pelo `exchangeRate`. O Azure ARC é precificado pela Microsoft em USD e convertido para BRL na inicialização.

```php
// Azure ARC (por vCore/hora) — USD × 5,39 = BRL
'arcStandard'         => 0.539,   // $0,10  × 5,39
'arcEnterprise'       => 2.021,   // $0,375 × 5,39

// SPLA (por pack de 2 cores/mês) — USD × 5,39 = BRL
'splaStandard'        => 1131.90,   // $210,00 × 5,39
'splaEnterprise'      => 4611.08,   // $855,47 × 5,39

// CSP 1 Ano (por pack de 2 cores/ano) — em BRL
'csp1yStandard'       => 15226.33,   // R$15.226,33
'csp1yEnterprise'     => 58377.01,   // R$58.377,01

// CSP 3 Anos (por pack de 2 cores, total 3 anos) — em BRL
'csp3yStandard'       => 38245.50,   // R$38.245,50
'csp3yEnterprise'     => 146564.40,  // R$146.564,40

// Perpétuo (por pack de 2 cores, licença única) — em BRL
'perpetualStandard'   => 32240.30,   // R$32.240,30
'perpetualEnterprise' => 123602.72,  // R$123.602,72
'saPct'               => 25,

// OVS (por pack de 2 cores/ano) — em BRL
'ovsStandard'         => 12651.28,   // R$12.651,28
'ovsEnterprise'       => 48511.95,   // R$48.511,95

// Câmbio
'exchangeRate'        => 5.39,
```

### Lógica de Conversão de Moeda

Preços base em BRL → dividir pelo câmbio para obter USD:

```php
// LicensingAdvisor.php e Calculator.php
private function applyRate(float $value, string $currency): float
{
    return $currency === 'USD' ? $value / $this->prices['exchangeRate'] : $value;
}
```

## Paleta de Cores TD SYNNEX

| Cor        | Hex       | RGB              |
|------------|-----------|------------------|
| Teal       | #005758   | 0, 87, 88        |
| Teal Dark  | #003031   | 0, 48, 49        |
| Charcoal   | #262626   | 38, 38, 38       |
| Gray       | #737373   | 115, 115, 115    |
| Light Gray | #f5f5f7   | 245, 245, 247    |
| Blue       | #0078D4   | 0, 120, 212      |
| Green      | #009600   | 0, 150, 0        |
| Red        | #DC2626   | 220, 38, 38      |

## Padrões de Código

### PHP
- Validar inputs com `filter_input()`
- Output HTML com `htmlspecialchars()`
- Sessões para persistência
- Classes com tipagem forte (PHP 8.0+)

### JavaScript
- Vanilla JS apenas (sem frameworks)
- jsPDF para geração de PDFs
- `fetch()` para chamadas API

### Diretrizes Gerais
- Textos visíveis sempre em **Português do Brasil**
- Azure ARC sempre destacado como **recomendado**
- Manter compatibilidade com `Calculator.php` e `index.php` existentes

## System Prompt do Especialista IA

O chat lateral usa o seguinte system prompt para contexto do GPT-4o:

```
Você é um Especialista em Licenciamento Microsoft com foco em SQL Server 2022,
trabalhando para a TD SYNNEX. Sua missão é ajudar o time de vendas a entender
qual a melhor forma de licenciar o SQL Server 2022...
```

Veja o prompt completo em `public/chat-api.php`.
