# 🚀 Guia Rápido - Sistema de Comparação de Licenciamento

## ⚡ Início Rápido (5 minutos)

### 1️⃣ Verificar Instalação
```bash
# Acesse o teste automatizado
http://localhost/test-comparison.php
```

Se todos os testes passarem ✅, o sistema está pronto!

---

### 2️⃣ Usar na Calculadora

1. Acesse a calculadora principal: `index.php`

2. Preencha os dados básicos:
   - **vCores**: 16 (exemplo)
   - **Tempo de uso**: 36 meses
   - **Edição**: Standard

3. **Ative a comparação:**
   - ✅ Marque: "Comparar Modelos de Licenciamento"

4. **Selecione modelos:**
   - Azure ARC ✓
   - SPLA ✓
   - CSP Subscription ✓
   - Software Perpétuo ✓
   - Open Value ✓
   - Open Value Subscription ✓

5. Clique em **"Comparar Selecionados"**

---

### 3️⃣ Entender os Resultados

#### 📊 Resumo Executivo (topo)
- **Melhor opção para cada período**
- Destaque em cards coloridos
- Valores totais e mensais

#### 📋 Tabela Detalhada
- Comparação lado a lado
- Rankings (#1, #2, #3...)
- Custos por 12, 36 e 60 meses

#### 💡 Observações
- Características importantes
- Requisitos mínimos
- Considerações especiais

---

## 🎯 Casos de Uso Comuns

### Cenário 1: Cliente quer flexibilidade
**Recomendação:** Azure ARC ou CSP
- Sem compromisso de longo prazo
- Cancelamento flexível
- Custos mensais previsíveis

### Cenário 2: Cliente tem CAPEX disponível
**Recomendação:** Software Perpétuo
- Investimento único
- Economia em 5+ anos
- Propriedade permanente

### Cenário 3: Cliente quer previsibilidade (3 anos)
**Recomendação:** Open Value Subscription
- Pagamentos anuais fixos
- SA incluído
- Melhor custo-benefício médio prazo

### Cenário 4: Service Provider
**Recomendação:** SPLA
- Licenciamento específico para SP
- Faturamento baseado em uso real
- Relatório mensal

---

## 📊 Exemplo Real

### Entrada:
- **16 vCores**
- **36 meses**
- **Taxa câmbio: R$ 5,39**

### Resultado Esperado:

| Modelo | Tipo | Total 36m | Mensal | Ranking |
|--------|------|-----------|--------|---------|
| CSP Subscription | Mensal | R$ 32.054 | R$ 890 | #1 |
| Azure ARC | Mensal | R$ 33.696 | R$ 936 | #2 |
| Open Value Sub | Contrato | R$ 34.848 | R$ 968 | #3 |
| SPLA | Mensal | R$ 36.288 | R$ 1.008 | #4 |
| Open Value | Contrato | R$ 51.200 | R$ 1.422 | #5 |
| Perpétuo | CAPEX | R$ 104.678 | R$ 2.908 | #6 |

**💡 Insight:** Para 36 meses, CSP Subscription oferece melhor custo-benefício!

---

## 🔧 Ajustar Preços

### Método 1: Via Interface
1. Clique no ícone ⚙️ (Settings) no topo
2. Ajuste os preços:
   - Azure ARC Standard/Enterprise
   - SPLA Standard/Enterprise
   - Taxa de câmbio
3. Salve

### Método 2: Via Código
Edite: `src/config/licensing-models.php`

```php
'azure_arc' => [
    'pricing' => [
        'monthly_per_core' => 70.00, // Altere aqui
    ]
]
```

---

## 📤 Exportar Resultados

### Opção 1: Imprimir
- Clique em "Imprimir"
- Salve como PDF no navegador

### Opção 2: Excel
- Clique em "Exportar Excel"
- Baixa arquivo CSV
- Abra no Excel/Google Sheets

### Opção 3: Compartilhar
- Clique em "Compartilhar"
- Copia resumo para clipboard
- Cole em email/WhatsApp

---

## ⚠️ Problemas Comuns

### ❌ "Selecione pelo menos 2 modelos"
**Solução:** Marque mais checkboxes antes de comparar

### ❌ Comparação não aparece
**Solução:** Marque o checkbox principal "Comparar Modelos"

### ❌ Erro ao calcular
**Solução:** 
1. Verifique `test-comparison.php`
2. Confira logs de erro do PHP
3. Limpe cache do navegador

### ❌ Estilos quebrados
**Solução:** Force refresh (Ctrl + F5)

---

## 🎨 Personalizar

### Adicionar Novo Modelo
1. Edite `src/config/licensing-models.php`
2. Adicione novo array seguindo o padrão
3. Adicione checkbox em `comparison-selector.php`

### Mudar Períodos de Comparação
Edite `LicensingComparator.php`:
```php
private array $comparisonPeriods = [12, 24, 36, 60];
```

### Adicionar Gráficos
1. Inclua Chart.js no layout
2. Crie elemento canvas
3. Use dados de `$comparisonResults`

---

## 📞 Checklist de Produção

Antes de colocar em produção:

- [ ] Testar com dados reais
- [ ] Atualizar preços conforme tabela Microsoft
- [ ] Verificar taxa de câmbio atual
- [ ] Testar em diferentes navegadores
- [ ] Testar responsividade mobile
- [ ] Backup do banco de dados (se houver)
- [ ] Documentar customizações

---

## 🎓 Recursos Adicionais

### Arquivos de Referência:
- `COMPARACAO_MODELOS_README.md` - Documentação completa
- `test-comparison.php` - Suite de testes
- `src/config/licensing-models.php` - Configurações

### Estrutura de Dados:
```php
$comparisonResults = [
    'model_id' => [
        'name' => 'Nome do Modelo',
        'costs' => [
            12 => ['total' => X, 'monthly_average' => Y],
            36 => ['total' => X, 'monthly_average' => Y],
            60 => ['total' => X, 'monthly_average' => Y]
        ],
        'ranking' => [12 => 1, 36 => 2, 60 => 3]
    ]
]
```

---

## ✨ Próximos Passos

### Melhorias Futuras:
1. **Gráficos interativos** - Chart.js
2. **Exportação PDF** - Relatório completo
3. **Histórico de comparações** - Salvar favoritos
4. **Calculadora de ROI** - Análise detalhada
5. **Multi-idioma** - Inglês/Português
6. **API REST** - Integração externa

---

**🎉 Sistema Pronto para Uso!**

Desenvolvido para TD SYNNEX  
Calculadora ARC - Fevereiro 2026
