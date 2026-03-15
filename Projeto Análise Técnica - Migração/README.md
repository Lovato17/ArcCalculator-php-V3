# Sistema de Análise de Migração de Recursos Azure

Sistema web em PHP 8.2 para análise de viabilidade de migração de recursos entre assinaturas Azure, desenvolvido com identidade visual **TD SYNNEX**.

## 📋 Descrição

Esta aplicação permite que equipes de Pré-Vendas e Cloud analisem a viabilidade de migração de recursos Azure entre assinaturas, gerando relatórios profissionais em **PDF** e **Excel** com branding TD SYNNEX.

## ✨ Funcionalidades

- **Upload de Arquivos**: Suporte a Excel (.xlsx, .xls) e CSV
- **Análise Automática**: Comparação com a base de dados oficial da Microsoft (16.000+ tipos de recursos)
- **Classificação de Recursos**:
  - ✅ **Migráveis**: Recursos que podem ser movidos
  - ⚠️ **Migráveis com Restrições**: Recursos com limitações específicas
  - ❌ **Não Migráveis**: Recursos que não suportam migração
- **Notas Customizadas**: Adicione observações específicas para cada recurso
- **Relatórios Executivos**: Exportação em PDF e Excel com identidade visual TD SYNNEX
- **Interface Moderna**: Design responsivo com cores corporativas

## 🚀 Instalação

### Requisitos

- PHP 8.2 ou superior
- Composer
- Extensões PHP: `mbstring`, `xml`, `gd`, `zip`

### Passos

1. **Clone ou copie o projeto**
   ```bash
   cd "c:\Users\Pc\Desktop\Projeto Análise Técnica - Migração"
   ```

2. **Instale as dependências**
   ```bash
   composer install
   ```

3. **Configure as permissões dos diretórios**
   ```bash
   # Windows - os diretórios são criados automaticamente
   # Linux/Mac:
   chmod 755 uploads reports
   ```

4. **Inicie o servidor de desenvolvimento**
   ```bash
   # Usando o script do Composer
   composer start
   
   # Ou manualmente
   php -S localhost:8000 -t public
   ```

5. **Acesse no navegador**
   ```
   http://localhost:8000
   ```

## 📁 Estrutura do Projeto

```
projeto-analise-migracao/
├── public/
│   └── index.php                      # Interface web principal
├── src/
│   ├── FileParser.php                 # Parser de Excel/CSV
│   ├── AzureResourceAnalyzer.php      # Motor de análise
│   ├── ReportGenerator.php            # Gerador de PDF/Excel
│   └── data/
│       └── azure-move-support.json    # Base de dados (16.000+ recursos)
├── exemplos/
│   └── exemplo-recursos-azure.csv     # Arquivo de exemplo
├── uploads/                           # Temporários (auto-limpeza 24h)
├── reports/                           # Relatórios gerados (auto-limpeza 7 dias)
├── vendor/                            # Dependências Composer
├── composer.json                      # Configuração do projeto
├── .gitignore                         # Arquivos ignorados pelo Git
└── README.md                          # Documentação
```

## 📊 Formato do Arquivo de Entrada

O arquivo deve conter pelo menos a coluna `Resource Type`. Outras colunas são opcionais.

### Exemplo CSV:
```csv
Resource Name,Resource Type,Resource Group,Location
vm-prod-01,Microsoft.Compute/virtualMachines,rg-prod,eastus
storage-account-01,Microsoft.Storage/storageAccounts,rg-storage,westus
aks-cluster,Microsoft.ContainerService/managedClusters,rg-aks,eastus
```

### Colunas Suportadas:
| Coluna | Obrigatória | Descrição |
|--------|-------------|-----------|
| Resource Type | ✅ Sim | Tipo do recurso Azure (ex: Microsoft.Compute/virtualMachines) |
| Resource Name | Não | Nome do recurso |
| Resource Group | Não | Nome do resource group |
| Location | Não | Região do Azure |
| Subscription | Não | Nome ou ID da assinatura |

## 📄 Relatórios Gerados

### PDF (Identidade TD SYNNEX)
- **Design Executivo**: Layout profissional com cores corporativas
- **Cabeçalho Branded**: Logo e informações TD SYNNEX
- **Resumo Executivo**: Estatísticas e percentuais de viabilidade
- **Análise Detalhada**: Tabela completa com todos os recursos
- **Recomendações**: Sugestões automáticas baseadas nos resultados
- **Notas Customizadas**: Observações específicas por recurso

### Excel (Análise Completa)
- **Aba Resumo**: Dashboard com estatísticas gerais e gráficos
- **Aba Detalhes**: Lista completa com filtros e formatação condicional
- **Aba Por Provider**: Agrupamento por provedor Azure (Compute, Storage, Network, etc.)
- **Notas Integradas**: Observações customizadas visíveis em cada linha

## 🎨 Identidade Visual TD SYNNEX

As cores oficiais são aplicadas automaticamente:
- **Teal Principal**: `#005758` - Títulos e elementos principais
- **Azul Destaque**: `#08BED5` - Destaques e call-to-actions
- **Verde Status**: `#82C341` - Recursos migráveis
- **Amarelo Atenção**: `#FFD100` - Recursos com restrições
- **Vermelho Alerta**: `#D9272E` - Recursos não migráveis

## 🔄 Atualizando a Base de Dados

A base de dados de recursos migráveis está em `src/data/azure-move-support.json`. Para atualizar:

1. Acesse a [documentação oficial da Microsoft](https://learn.microsoft.com/en-us/azure/azure-resource-manager/management/move-support-resources)

2. Edite o arquivo JSON seguindo a estrutura:
   ```json
   {
     "Microsoft.Compute/virtualMachines": {
       "resourceGroup": true,
       "subscription": true,
       "region": true,
       "status": "movable",
       "notes": "Use Azure Resource Mover para mover VMs."
     }
   }
   ```

3. Status possíveis:
   - `movable`: Pode ser migrado
   - `movable-with-restrictions`: Pode ser migrado com restrições
   - `not-movable`: Não pode ser migrado

4. Atualize o campo `lastUpdated` nos metadados

## 🛠️ Tecnologias Utilizadas

- **PHP 8.2**: Linguagem principal com tipos estritos
- **PhpSpreadsheet 2.3**: Manipulação de Excel/CSV e geração de arquivos .xlsx
- **mPDF 8.2**: Geração de relatórios PDF profissionais
- **Bootstrap 5.3**: Framework CSS responsivo
- **Bootstrap Icons 1.11**: Biblioteca de ícones

### Dependências (Composer)
```json
{
  "phpoffice/phpspreadsheet": "^2.3",
  "mpdf/mpdf": "^8.2"
}
```

## 📝 Notas Importantes

1. **Auto-Limpeza**: 
   - Uploads são removidos automaticamente após 24 horas
   - Relatórios são removidos automaticamente após 7 dias
2. **Sessão PHP**: Resultados da análise são mantidos na sessão do usuário
3. **Recursos Desconhecidos**: Tipos não encontrados na base são marcados como "Tipo desconhecido"
4. **Base de Dados**: 16.000+ tipos de recursos Azure catalogados
5. **Notas Customizadas**: Persistem durante a sessão e são incluídas em todos os relatórios
6. **Performance**: Análise de até 10.000 recursos em menos de 5 segundos

## 🔗 Referências

- [Move Support Resources - Microsoft Learn](https://learn.microsoft.com/en-us/azure/azure-resource-manager/management/move-support-resources)
- [Azure Resource Mover Documentation](https://learn.microsoft.com/en-us/azure/resource-mover/overview)
- [Move Resources Guide](https://learn.microsoft.com/en-us/azure/azure-resource-manager/management/move-resource-group-and-subscription)
- [TD SYNNEX Brand Guidelines](https://www.tdsynnex.com/)

## 🔐 Licença e Uso

Este projeto é de **uso interno** para análise de migração Azure pela equipe TD SYNNEX.

## 📧 Suporte

Para dúvidas ou sugestões sobre o sistema, entre em contato com a equipe de Cloud & Pré-Vendas TD SYNNEX.

---

**Desenvolvido para TD SYNNEX** | Última atualização: Janeiro 2026
