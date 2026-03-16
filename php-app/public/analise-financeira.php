<?php
declare(strict_types=1);
session_start();
// Helpers de tipo de migracao
function migrationLabel(string $type): string {
    return $type === 'mpn_csp' ? 'MPN → CSP' : 'MOSP → CSP';
}
function migrationQtyLabel(string $type): string {
    return $type === 'mpn_csp' ? 'UsageQuantity' : 'Quantity';
}
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

require_once __DIR__ . '/../vendor/autoload.php';

use AzureMigration\FinancialAnalyzer;

$uploadDir = __DIR__ . '/../uploads';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$error   = null;
$success = null;
$results = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'analyze';

    if ($action === 'analyze') {
        // Determinar a origem: upload direto (file) ou chunked (uploadId na sessão)
        $tmpPath  = null;
        $origName = '';
        $fileOk   = false;

        $uploadId = preg_replace('/[^a-zA-Z0-9_-]/', '', (string)($_POST['uploadId'] ?? ''));

        if ($uploadId !== '' && !empty($_SESSION['chunkedUpload']) && $_SESSION['chunkedUpload']['uploadId'] === $uploadId) {
            // Chunked upload — arquivo já montado pelo upload-chunk.php
            $chunked  = $_SESSION['chunkedUpload'];
            $tmpPath  = $chunked['filePath'];
            $origName = $chunked['fileName'];
            unset($_SESSION['chunkedUpload']);

            if (!file_exists($tmpPath)) {
                $error = 'Arquivo montado nao encontrado. Tente novamente.';
            } elseif (strtolower(pathinfo($origName, PATHINFO_EXTENSION)) !== 'csv') {
                $error = 'Apenas arquivos CSV sao suportados.';
                @unlink($tmpPath);
            } else {
                $fileOk = true;
            }
        } elseif (isset($_FILES['file'])) {
            // Upload direto (arquivo pequeno, sem chunks)
            $file = $_FILES['file'];
            $ext  = strtolower(pathinfo((string)$file['name'], PATHINFO_EXTENSION));

            if ($file['error'] !== UPLOAD_ERR_OK) {
                $error = 'Erro no upload: codigo ' . (int)$file['error'];
            } elseif ($ext !== 'csv') {
                $error = 'Apenas arquivos CSV sao suportados.';
            } elseif ($file['size'] > 200 * 1024 * 1024) {
                $error = 'Arquivo muito grande (maximo 200 MB).';
            } else {
                $tmpPath  = $uploadDir . '/cost_' . uniqid() . '.csv';
                $origName = (string)$file['name'];
                if (!move_uploaded_file($file['tmp_name'], $tmpPath)) {
                    $error = 'Falha ao salvar arquivo temporario.';
                } else {
                    $fileOk = true;
                }
            }
        } else {
            $error = 'Nenhum arquivo recebido. Selecione um CSV.';
        }

        if ($fileOk && $tmpPath) {
            set_time_limit(300);
            $exchangeRate  = max(0.01, (float)($_POST['exchangeRate'] ?? 5.39));
            $clientName    = htmlspecialchars(trim((string)($_POST['clientName']     ?? '')), ENT_QUOTES);
            $refMonth      = htmlspecialchars(trim((string)($_POST['referenceMonth'] ?? '')), ENT_QUOTES);
            $migrationType = in_array($_POST['migrationType'] ?? '', ['mosp_csp', 'mpn_csp'], true)
                             ? $_POST['migrationType'] : 'mosp_csp';
            $qtyColumn     = $migrationType === 'mpn_csp' ? 'usagequantity' : 'quantity';

            $analyzer    = new FinancialAnalyzer();
            $parseResult = $analyzer->parseFile($tmpPath, $qtyColumn);

            if (!$parseResult['success']) {
                $error = $parseResult['error'];
            } else {
                $results = $analyzer->analyze($parseResult['data'], $exchangeRate);
                $results['clientName']     = $clientName;
                $results['referenceMonth'] = $refMonth;
                $results['migrationType']  = $migrationType;
                $results['filename']       = htmlspecialchars($origName, ENT_QUOTES);
                $_SESSION['financialResults'] = $results;
                $cnt = $results['summary']['totalRows'];
                $uid = $results['summary']['uniqueMeterIds'];
                $success = "Analise concluida! {$cnt} linhas processadas, {$uid} MeterIDs unicos consultados na API.";
            }

            @unlink($tmpPath);
        }
    } elseif ($action === 'export_csv') {
        $results = $_SESSION['financialResults'] ?? null;
        if ($results) {
            header('Content-Type: text/csv; charset=UTF-8');
            header('Content-Disposition: attachment; filename="analise_financeira_' . date('Y-m-d') . '.csv"');
            header('Cache-Control: no-cache, no-store');
            echo "\xEF\xBB\xBF";
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Recurso','Servico','Resource Group','MeterID','Quantidade','Unidade','Preco MOSP (USD)','Custo MOSP (USD)','Preco CSP (USD)','Custo CSP (USD)','Diferenca (USD)','Variacao %','Status API']);
            foreach ($results['results'] as $r) {
                fputcsv($out, [
                    $r['resourceName'],
                    $r['meterCategory'] ?: $r['serviceFamily'],
                    $r['resourceGroup'],
                    $r['meterId'],
                    $r['quantity'],
                    $r['unitOfMeasure'],
                    number_format($r['unitPriceMosp'],  6, '.', ''),
                    number_format($r['costMosp'],       4, '.', ''),
                    $r['unitPriceCsp'] !== null ? number_format($r['unitPriceCsp'], 6, '.', '') : 'N/D',
                    $r['costCsp']      !== null ? number_format($r['costCsp'],      4, '.', '') : 'N/D',
                    $r['difference']   !== null ? number_format($r['difference'],   4, '.', '') : 'N/D',
                    $r['differencePercent'] !== null ? number_format($r['differencePercent'], 2) . '%' : 'N/D',
                    $r['priceFound'] ? 'Encontrado' : 'Nao encontrado na API',
                ]);
            }
            fclose($out);
            exit;
        }
        $results = null;
    } elseif ($action === 'new_analysis') {
        unset($_SESSION['financialResults']);
        header('Location: analise-financeira.php');
        exit;
    }
} else {
    $results = $_SESSION['financialResults'] ?? null;
}

// ---- Helpers de formatacao ----
function fmtUsd(float $v): string { return '$' . number_format($v, 2, ',', '.'); }
function fmtBrl(float $v): string { return "R$\u{00A0}" . number_format($v, 2, ',', '.'); }
function fmtPct(float $v, bool $sign = true): string {
    $s = $sign && $v > 0 ? '+' : '';
    return $s . number_format($v, 1, ',', '.') . '%';
}
function diffClass(float $v): string { return $v < -0.001 ? 'text-success' : ($v > 0.001 ? 'text-danger' : 'text-muted'); }
function diffBg(float $v): string    { return $v < -0.001 ? 'var(--td-green-light)' : ($v > 0.001 ? 'var(--td-red-light)' : '#f8fafc'); }
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analise Financeira de Migracao - TD SYNNEX Tools</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --td-teal:        #005758;
            --td-blue:        #0097D7;
            --td-green:       #82C341;
            --td-yellow:      #FFD100;
            --td-red:         #D9272E;
            --td-dark:        #1e293b;
            --td-gray:        #64748b;
            --td-green-light: #f0fdf4;
            --td-red-light:   #fff1f2;
        }
        body { background: #f8fafc; font-family: 'Inter','Segoe UI',sans-serif; }
        .app-header {
            background:#fff; border-bottom:1px solid #e2e8f0;
            padding:.875rem 2rem; display:flex;
            justify-content:space-between; align-items:center; margin-bottom:2rem;
        }
        .fin-card {
            background:#fff; border:none; border-radius:10px;
            box-shadow:0 1px 3px rgba(0,0,0,.06),0 1px 2px rgba(0,0,0,.04);
            margin-bottom:1.25rem;
        }
        .fin-card .card-header {
            background:#fff; border-bottom:1px solid #f1f5f9;
            padding:1rem 1.25rem; font-weight:600; font-size:.9rem;
            color:var(--td-dark); border-radius:10px 10px 0 0 !important;
            display:flex; align-items:center;
        }
        .fin-card .card-header i { color:var(--td-teal); font-size:1rem; }
        .upload-zone {
            border:2px dashed #cbd5e1; border-radius:8px; padding:36px 16px;
            text-align:center; cursor:pointer; transition:all .2s; background:#f8fafc;
        }
        .upload-zone:hover,.upload-zone.dragover {
            border-color:var(--td-teal); background:rgba(0,87,88,.04);
        }
        .btn-teal { background:var(--td-teal); color:#fff; border:none; }
        .btn-teal:hover { background:#003031; color:#fff; }
        .stat-box {
            background:#fff; border-radius:10px; padding:1.25rem 1rem;
            box-shadow:0 1px 3px rgba(0,0,0,.06); border-top:4px solid #e2e8f0;
            text-align:center; height:100%;
        }
        .stat-box.mosp   { border-top-color:#64748b; }
        .stat-box.csp    { border-top-color:var(--td-teal); }
        .stat-box.tax    { border-top-color:#0097D7; }
        .stat-box.diff   { border-top-color:var(--td-green); }
        .stat-box.diff.negative { border-top-color:var(--td-red); }
        .stat-box .stat-value { font-size:1.5rem; font-weight:800; line-height:1.1; color:var(--td-dark); }
        .stat-box .stat-brl   { font-size:.85rem; color:var(--td-gray); font-weight:500; margin-top:2px; }
        .stat-box .stat-label { font-size:.72rem; text-transform:uppercase; color:var(--td-gray);
            letter-spacing:.5px; margin-top:.5rem; border-top:1px solid #f1f5f9;
            padding-top:.5rem; font-weight:600; }
        .breakdown-bar {
            height:10px; border-radius:4px; background:#e2e8f0;
            overflow:hidden; margin-top:4px;
        }
        .breakdown-bar-fill { height:100%; border-radius:4px; background:var(--td-teal); transition:width .4s; }
        .table-fin th { background:var(--td-teal); color:#fff; font-size:.78rem;
            text-transform:uppercase; letter-spacing:.4px; white-space:nowrap; }
        .table-fin td { font-size:.82rem; vertical-align:middle; }
        .badge-found     { background:#d1fae5; color:#065f46; font-size:.7rem; padding:2px 7px; border-radius:20px; font-weight:600; }
        .badge-partial   { background:#fef9c3; color:#854d0e; }
        .badge-fallback  { background:#ffedd5; color:#9a3412; }
        .badge-not-found { background:#fee2e2; color:#991b1b; font-size:.7rem; padding:2px 7px; border-radius:20px; font-weight:600; }
        .vchk { display:inline-flex;align-items:center;gap:2px;font-size:.68rem;font-weight:600;
                border-radius:4px;padding:1px 5px;white-space:nowrap; }
        .vchk-ok  { background:#dcfce7;color:#15803d; }
        .vchk-err { background:#fee2e2;color:#b91c1c; }
        .vchk-na  { background:#f1f5f9;color:#94a3b8; }
        .sub-id   { font-family:monospace;font-size:.68rem;color:#94a3b8; }
        .meter-id { font-family:monospace; font-size:.72rem; color:var(--td-gray); word-break:break-all; }
        .diff-positive { color:var(--td-red)!important;   font-weight:600; }
        .diff-negative { color:#16a34a!important; font-weight:600; }
        /* ── Resizable columns ── */
        .table-fin { table-layout:fixed; width:100%; }
        .table-fin thead th { position:relative; overflow:hidden; user-select:none; }
        .col-resize-handle {
            position:absolute; right:0; top:0; bottom:0; width:6px;
            cursor:col-resize; background:transparent; z-index:2;
        }
        .col-resize-handle:hover, .col-resize-handle.active {
            background:rgba(255,255,255,0.35);
        }
        @keyframes spin-anim { to { transform:rotate(360deg); } }
        .spin-icon { animation:spin-anim 1s linear infinite; display:inline-block; }
    </style>
</head>
<body>

<header class="app-header">
    <div class="d-flex align-items-center gap-3">
        <img src="logo.png" alt="TD SYNNEX" style="height:44px;width:auto;object-fit:contain;">
        <div>
            <h1 class="mb-0" style="font-size:1.2rem;font-weight:700;color:#1e293b;">Tools</h1>
            <p class="mb-0" style="font-size:.75rem;color:#64748b;font-weight:500;">Ferramentas e Calculadoras</p>
        </div>
    </div>
    <nav class="d-flex align-items-center gap-3">
        <a href="home.php"           style="font-size:.85rem;font-weight:500;color:#475569;text-decoration:none;">Home</a>
        <a href="migracao-azure.php" style="font-size:.85rem;font-weight:500;color:#2563eb;border-bottom:2px solid #2563eb;padding-bottom:2px;text-decoration:none;">Migracao Azure</a>
        <a href="sql-advisor.php"    style="font-size:.85rem;font-weight:500;color:#475569;text-decoration:none;">SQL Advisor</a>
        <a href="home.php#cloud-partner-hub" style="font-size:.85rem;font-weight:500;color:#475569;text-decoration:none;">Cloud Partner HUB</a>
    </nav>
</header>

<div class="container-fluid px-4" style="max-width:1400px;margin:0 auto;">

    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb" style="font-size:.82rem;">
            <li class="breadcrumb-item"><a href="migracao-azure.php" style="color:var(--td-teal);text-decoration:none;">Migracao Azure</a></li>
            <li class="breadcrumb-item active">Analise Financeira MOSP - CSP</li>
        </ol>
    </nav>

    <?php
        $activeMigType = $results['migrationType'] ?? 'mosp_csp';
        $activeMigLabel = migrationLabel($activeMigType);
        $activeQtyLabel = migrationQtyLabel($activeMigType);
    ?>
    <div class="mb-4 d-flex align-items-center gap-3">
        <div style="background:#ccfbf1;padding:10px;border-radius:8px;">
            <i class="bi bi-cash-coin" style="color:var(--td-teal);font-size:1.4rem;"></i>
        </div>
        <div>
            <h2 class="mb-0" style="font-size:1.5rem;font-weight:700;color:#1e293b;">
                Analise Financeira de Migracao
                <span style="color:var(--td-teal);"><?= htmlspecialchars($activeMigLabel) ?></span>
            </h2>
            <p class="mb-0" style="font-size:.85rem;color:#64748b;">
                Upload do export do Cost Management e comparativo de custos via API publica da Microsoft.
                <span style="color:var(--td-teal);font-weight:600;">Coluna de quantidade: <?= htmlspecialchars($activeQtyLabel) ?></span>
            </p>
        </div>
    </div>

    <?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert" style="border-radius:8px;font-size:.88rem;">
        <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <?php if ($success): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert" style="border-radius:8px;font-size:.88rem;">
        <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($success) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <div class="row">

        <!-- ===== UPLOAD FORM ===== -->
        <div class="col-lg-3 mb-4">
            <div class="fin-card">
                <div class="card-header"><i class="bi bi-upload me-2"></i>Upload do Export</div>
                <div class="card-body p-3">
                    <form method="POST" enctype="multipart/form-data" id="uploadForm">
                        <input type="hidden" name="action" value="analyze">
                        <input type="hidden" name="uploadId" id="uploadIdField" value="">

                        <!-- Tipo de Migração -->
                        <div class="mb-3" style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:10px 12px;">
                            <label class="form-label" style="font-size:.82rem;font-weight:700;color:#005758;margin-bottom:6px;display:flex;align-items:center;gap:5px;">
                                <i class="bi bi-arrow-left-right"></i> Tipo de Migração
                            </label>
                            <select class="form-select form-select-sm" name="migrationType" id="migrationType" onchange="updateMigrationHint()">
                                <option value="mosp_csp" <?= ($results['migrationType'] ?? 'mosp_csp') === 'mosp_csp' ? 'selected' : '' ?>>MOSP → CSP (Pay-As-You-Go)</option>
                                <option value="mpn_csp"  <?= ($results['migrationType'] ?? '') === 'mpn_csp'  ? 'selected' : '' ?>>MPN → CSP (via Benefício MPN)</option>
                            </select>
                            <div id="migrationHint" style="font-size:.72rem;color:#64748b;margin-top:5px;">
                                Coluna de quantidade: <code id="migrationQtyCol"><?= ($results['migrationType'] ?? 'mosp_csp') === 'mpn_csp' ? 'UsageQuantity' : 'Quantity' ?></code>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" style="font-size:.82rem;font-weight:600;color:#374151;">Cliente (opcional)</label>
                            <input type="text" class="form-control form-control-sm" name="clientName"
                                   value="<?= htmlspecialchars($results['clientName'] ?? '') ?>"
                                   placeholder="Ex: Empresa XYZ">
                        </div>

                        <div class="mb-3">
                            <label class="form-label" style="font-size:.82rem;font-weight:600;color:#374151;">Cambio USD &rarr; BRL</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">R$</span>
                                <input type="number" step="0.01" min="0.01" class="form-control" name="exchangeRate"
                                       value="<?= htmlspecialchars((string)($results['summary']['exchangeRate'] ?? '5.39')) ?>">
                            </div>
                        </div>

                        <div class="upload-zone mb-3" id="dropZone" onclick="document.getElementById('fileInput').click()">
                            <i class="bi bi-file-earmark-bar-graph" style="font-size:2rem;color:#94a3b8;"></i>
                            <p class="mt-2 mb-1" style="font-size:.85rem;color:#475569;">Arraste o CSV ou clique</p>
                            <small class="text-muted" style="font-size:.75rem;">Formato: .csv (Cost Management Export)</small>
                            <input type="file" id="fileInput" name="file" accept=".csv" class="d-none"
                                   onchange="handleFile(this)">
                        </div>

                        <div id="selectedFile" class="alert alert-info d-none p-2 mb-2" style="font-size:.82rem;">
                            <i class="bi bi-file-earmark me-1"></i><span id="fileName"></span>
                        </div>

                        <button type="submit" class="btn btn-teal btn-sm w-100" id="analyzeBtn" disabled>
                            <i class="bi bi-search me-1"></i>Calcular Custos CSP
                        </button>

                        <!-- Progress bar de upload chunked -->
                        <div id="uploadProgress" class="d-none mt-2">
                            <div style="background:#e2e8f0;border-radius:6px;height:22px;overflow:hidden;position:relative;">
                                <div id="uploadProgressBar" style="background:linear-gradient(90deg,#005758,#0097D7);height:100%;width:0%;border-radius:6px;transition:width .2s;"></div>
                                <span id="uploadProgressText" style="position:absolute;top:0;left:0;right:0;text-align:center;font-size:.72rem;font-weight:700;color:#fff;line-height:22px;">0%</span>
                            </div>
                            <div id="uploadProgressDetail" style="font-size:.72rem;color:#64748b;margin-top:4px;text-align:center;"></div>
                        </div>
                    </form>

                    <hr style="margin:14px 0;">
                    <!-- Impostos -->
                    <div class="mb-2">
                        <label class="form-label" style="font-size:.82rem;font-weight:600;color:#374151;">
                            <i class="bi bi-receipt me-1" style="color:var(--td-teal);"></i>Impostos (%)
                        </label>
                        <div class="input-group input-group-sm">
                            <input type="number" id="taxInput" step="0.01" min="0" max="999"
                                   class="form-control" value="0"
                                   oninput="recalcTable()">
                            <span class="input-group-text">%</span>
                        </div>
                        <div style="font-size:.72rem;color:#94a3b8;margin-top:4px;">Total c/ Impostos = Total FOB &times; (1 + %/100)&nbsp;&nbsp;ex: 9,25</div>
                    </div>

                    <!-- Markup -->
                    <div class="mb-3">
                        <label class="form-label" style="font-size:.82rem;font-weight:600;color:#374151;">
                            <i class="bi bi-percent me-1" style="color:var(--td-teal);"></i>Markup (%)
                        </label>
                        <div class="input-group input-group-sm">
                            <input type="number" id="markupInput" step="0.1" min="0" max="500"
                                   class="form-control" value="0"
                                   oninput="recalcTable()">
                            <span class="input-group-text">%</span>
                        </div>
                        <div style="font-size:.72rem;color:#94a3b8;margin-top:4px;">Total c/ Markup = Total c/ Impostos &times; (1 + %/100)</div>
                    </div>

                    <hr style="margin:14px 0;">
                        <i class="bi bi-info-circle me-1" style="color:var(--td-teal);"></i>Colunas obrigatorias:
                    </p>
                    <ul style="font-size:.76rem;color:#64748b;padding-left:1.2rem;margin-bottom:8px;">
                        <li><code>MeterId</code> &mdash; ID do medidor Azure</li>
                        <li><code>Quantity</code> &mdash; Quantidade consumida</li>
                        <li><code>CostInBillingCurrency</code> &mdash; Custo MOSP</li>
                    </ul>
                    <p style="font-size:.74rem;color:#94a3b8;">
                        Schema: <a href="https://learn.microsoft.com/en-us/azure/cost-management-billing/dataset-schema/cost-usage-details-mca-partner-subscription" target="_blank" style="color:var(--td-teal);">MCA Partner 2019-11-01</a>
                    </p>

                    <a href="exemplo-cost-management.csv" class="btn btn-sm btn-outline-secondary w-100" style="font-size:.78rem;">
                        <i class="bi bi-download me-1"></i>Baixar CSV de Exemplo
                    </a>
                </div>
            </div>
        </div>

        <!-- ===== RESULTS ===== -->
        <div class="col-lg-9">
        <?php if ($results): ?>
            <?php $s = $results['summary']; ?>

            <!-- Info bar -->
            <div class="d-flex flex-wrap align-items-center gap-2 mb-3" style="font-size:.82rem;color:#64748b;">
                <span style="background:#f0fdf4;color:#005758;font-weight:600;padding:2px 8px;border-radius:12px;font-size:.76rem;border:1px solid #bbf7d0;">
                    <i class="bi bi-arrow-left-right me-1"></i><?= htmlspecialchars($activeMigLabel) ?>
                </span>
                <?php if ($results['clientName']): ?>
                <span><i class="bi bi-building me-1"></i><strong><?= htmlspecialchars($results['clientName']) ?></strong></span>
                <?php endif; ?>
                <?php if (!empty($results['filename'])): ?>
                <span class="text-muted">|</span>
                <span><i class="bi bi-file-earmark-text me-1"></i><?= htmlspecialchars($results['filename']) ?></span>
                <?php endif; ?>
                <span class="text-muted">|</span>
                <span><i class="bi bi-hash me-1"></i><?= number_format($s['totalRows']) ?> linhas &middot; <?= $s['uniqueMeterIds'] ?> MeterIDs unicos</span>
            </div>

            <!-- Stat Cards -->
            <div class="row g-3 mb-3">
                <div class="col-6 col-md-4">
                    <div class="stat-box csp">
                        <div class="stat-value" id="cardTotalFob"><?= fmtUsd($s['totalCsp']) ?></div>
                        <div class="stat-brl" id="cardTotalFobBrl"><?= fmtBrl($s['totalCspBrl']) ?></div>
                        <div class="stat-label">Total FOB (CSP)</div>
                    </div>
                </div>
                <div class="col-6 col-md-4">
                    <div class="stat-box tax">
                        <div class="stat-value" id="cardTotalTax">—</div>
                        <div class="stat-brl" id="cardTotalTaxBrl" style="font-size:.78rem;">defina os impostos</div>
                        <div class="stat-label">Total c/ Impostos</div>
                    </div>
                </div>
                <div class="col-6 col-md-4">
                    <div class="stat-box tax" style="border-top-color:var(--td-teal);">
                        <div class="stat-value" id="cardTotalMarkup">—</div>
                        <div class="stat-brl" id="cardTotalMarkupBrl" style="font-size:.78rem;">defina markup</div>
                        <div class="stat-label">Total c/ Markup</div>
                    </div>
                </div>
            </div>


            <?php if ($s['notFoundCount'] > 0): ?>
            <div class="alert alert-warning py-2 px-3" style="font-size:.82rem;border-radius:8px;">
                <i class="bi bi-exclamation-triangle me-1"></i>
                <strong><?= $s['notFoundCount'] ?> MeterID(s)</strong> nao foram encontrados na API de precos da Microsoft. Os valores CSP/diferenca dessas linhas sao exibidos como &ldquo;N/D&rdquo;.
                <details class="mt-1">
                    <summary style="cursor:pointer;font-size:.78rem;">Ver IDs nao encontrados</summary>
                    <div class="mt-1" style="font-family:monospace;font-size:.75rem;word-break:break-all;">
                        <?= implode('<br>', array_map('htmlspecialchars', $s['notFoundMeterIds'])) ?>
                    </div>
                </details>
            </div>
            <?php endif; ?>

            <!-- Export toolbar -->
            <div class="fin-card mb-3">
                <div class="card-body p-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h6 class="mb-0" style="font-weight:600;color:#1e293b;">
                        <i class="bi bi-file-earmark-arrow-down me-2" style="color:var(--td-teal);"></i>Exportar Resultado
                    </h6>
                    <div class="d-flex gap-2 flex-wrap">
                        <form method="POST" class="d-inline">
                            <input type="hidden" name="action" value="export_csv">
                            <button type="submit" class="btn btn-sm btn-outline-success">
                                <i class="bi bi-file-earmark-spreadsheet me-1"></i>CSV
                            </button>
                        </form>
                        <button type="button" class="btn btn-sm btn-teal" onclick="gerarPropostaPDF()" id="btnPdf">
                            <i class="bi bi-file-earmark-pdf me-1"></i>Proposta PDF
                        </button>
                        <form method="POST" class="d-inline">
                            <input type="hidden" name="action" value="new_analysis">
                            <button type="submit" class="btn btn-sm btn-outline-primary"
                                    onclick="return confirm('Iniciar nova analise? Os dados atuais serao descartados.')">
                                <i class="bi bi-arrow-counterclockwise me-1"></i>Nova Analise
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Breakdowns -->
            <div class="row g-3 mb-3">
                <!-- Por Servico -->
                <div class="col-md-4">
                    <div class="fin-card h-100">
                        <div class="card-header"><i class="bi bi-grid me-2"></i>Por Servico</div>
                        <div class="card-body p-3">
                            <?php foreach (array_slice($s['byService'], 0, 8, true) as $svcName => $svcData): ?>
                            <?php $pct = $s['totalCsp'] > 0 ? ($svcData['costCsp'] / $s['totalCsp']) * 100 : 0; ?>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span style="font-size:.82rem;font-weight:500;color:var(--td-dark);"><?= htmlspecialchars($svcName) ?></span>
                                    <div class="text-end">
                                        <?php if ($svcData['costCsp'] > 0): ?>
                                        <span style="font-size:.82rem;font-weight:700;color:var(--td-teal);"><?= fmtUsd($svcData['costCsp']) ?></span>
                                        <?php else: ?>
                                        <span style="font-size:.78rem;color:#94a3b8;">N/D</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="breakdown-bar">
                                    <div class="breakdown-bar-fill" style="width:<?= min(100, $pct) ?>%;"></div>
                                </div>
                                <div style="font-size:.72rem;color:#94a3b8;"><?= number_format($pct,1) ?>% do total CSP &middot; <?= $svcData['count'] ?> linha(s)</div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Por Resource Group -->
                <div class="col-md-4">
                    <div class="fin-card h-100">
                        <div class="card-header"><i class="bi bi-folder me-2"></i>Por Resource Group</div>
                        <div class="card-body p-3">
                            <?php foreach (array_slice($s['byResourceGroup'], 0, 8, true) as $rgName => $rgData): ?>
                            <?php $pct = $s['totalCsp'] > 0 ? ($rgData['costCsp'] / $s['totalCsp']) * 100 : 0; ?>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span style="font-size:.82rem;font-weight:500;color:var(--td-dark);"><?= htmlspecialchars($rgName) ?></span>
                                    <div class="text-end">
                                        <?php if ($rgData['costCsp'] > 0): ?>
                                        <span style="font-size:.82rem;font-weight:700;color:var(--td-teal);"><?= fmtUsd($rgData['costCsp']) ?></span>
                                        <?php else: ?>
                                        <span style="font-size:.78rem;color:#94a3b8;">N/D</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="breakdown-bar">
                                    <div class="breakdown-bar-fill" style="width:<?= min(100, $pct) ?>%;background:#0097D7;"></div>
                                </div>
                                <div style="font-size:.72rem;color:#94a3b8;"><?= number_format($pct,1) ?>% do total CSP &middot; <?= $rgData['count'] ?> linha(s)</div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Por Assinatura -->
                <div class="col-md-4">
                    <div class="fin-card h-100">
                        <div class="card-header"><i class="bi bi-building me-2"></i>Por Assinatura</div>
                        <div class="card-body p-3">
                            <?php if (count($s['bySubscription']) <= 1 && array_key_first($s['bySubscription']) === 'Sem Assinatura'): ?>
                            <div class="text-center py-4" style="color:#94a3b8;font-size:.82rem;">
                                <i class="bi bi-check-circle" style="font-size:1.5rem;color:#0097D7;display:block;margin-bottom:.5rem;"></i>
                                Cliente possui apenas uma assinatura Azure.
                            </div>
                            <?php else: ?>
                            <?php foreach (array_slice($s['bySubscription'], 0, 8, true) as $subName => $subData): ?>
                            <?php $pct = $s['totalCsp'] > 0 ? ($subData['costCsp'] / $s['totalCsp']) * 100 : 0; ?>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <div style="min-width:0;">
                                        <div style="font-size:.82rem;font-weight:500;color:var(--td-dark);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:160px;" title="<?= htmlspecialchars($subName) ?>"><?= htmlspecialchars($subName) ?></div>
                                        <?php if (!empty($subData['subscriptionId'])): ?>
                                        <div style="font-size:.68rem;color:#94a3b8;font-family:monospace;"><?= htmlspecialchars(substr($subData['subscriptionId'], 0, 18)) ?>...</div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-end">
                                        <?php if ($subData['costCsp'] > 0): ?>
                                        <span style="font-size:.82rem;font-weight:700;color:var(--td-teal);"><?= fmtUsd($subData['costCsp']) ?></span>
                                        <?php else: ?>
                                        <span style="font-size:.78rem;color:#94a3b8;">N/D</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="breakdown-bar">
                                    <div class="breakdown-bar-fill" style="width:<?= min(100, $pct) ?>%;background:#82C341;"></div>
                                </div>
                                <div style="font-size:.72rem;color:#94a3b8;"><?= number_format($pct,1) ?>% do total CSP &middot; <?= $subData['count'] ?> linha(s)</div>
                            </div>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabela detalhada -->
            <?php
                // Detecta se o CSV usa DD/MM/YYYY: se qualquer primeiro segmento > 12 não pode ser mês
                $slashDMY_ = false;
                foreach ($results['results'] as $_rd) {
                    if (preg_match('/^(\d{1,2})\//', trim($_rd['date'] ?? ''), $_dm) && (int)$_dm[1] > 12) {
                        $slashDMY_ = true;
                        break;
                    }
                }
                // Helper: converte qualquer formato de data para YYYY-MM-DD
                $toIso_ = function(string $raw) use ($slashDMY_): string {
                    $raw = trim($raw);
                    if ($raw === '') return '';
                    // Remove time portion (T or space)
                    $dp = preg_split('/[T ]/', $raw)[0];
                    // Already YYYY-MM-DD
                    if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $dp)) return $dp;
                    // DD/MM/YYYY or MM/DD/YYYY — detectar pelo flag ou pelo valor > 12
                    if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $dp, $m)) {
                        $isDay1 = $slashDMY_ || (int)$m[1] > 12;
                        [$month, $day] = $isDay1 ? [$m[2], $m[1]] : [$m[1], $m[2]];
                        return $m[3] . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-' . str_pad($day, 2, '0', STR_PAD_LEFT);
                    }
                    // Fallback: strtotime handles many formats
                    $ts = @strtotime($dp);
                    return ($ts !== false && $ts > 0) ? date('Y-m-d', $ts) : '';
                };
                // Calcular data mínima e máxima dos dados para pré-popular o filtro
                $allDates_ = [];
                foreach ($results['results'] as $row_) {
                    $iso = $toIso_(trim($row_['date'] ?? ''));
                    if ($iso !== '') $allDates_[] = $iso;
                }
                $minDate_ = $allDates_ ? min($allDates_) : '';
                $maxDate_ = $allDates_ ? max($allDates_) : '';
            ?>
            <div class="fin-card">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <span><i class="bi bi-table me-2"></i>Detalhamento Linha a Linha</span>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <div class="d-flex align-items-center gap-1" style="font-size:.8rem;">
                            <label style="color:#94a3b8;white-space:nowrap;margin:0;"><i class="bi bi-calendar-range me-1"></i>De</label>
                            <input type="date" id="dateFrom" class="form-control form-control-sm" style="width:140px;" oninput="filterTable()"
                                   value="<?= htmlspecialchars($minDate_) ?>" min="<?= htmlspecialchars($minDate_) ?>" max="<?= htmlspecialchars($maxDate_) ?>">
                            <label style="color:#94a3b8;margin:0;">Até</label>
                            <input type="date" id="dateTo" class="form-control form-control-sm" style="width:140px;" oninput="filterTable()"
                                   value="<?= htmlspecialchars($maxDate_) ?>" min="<?= htmlspecialchars($minDate_) ?>" max="<?= htmlspecialchars($maxDate_) ?>">
                            <button class="btn btn-sm btn-outline-secondary" onclick="clearDateFilter()" title="Limpar filtro de data" style="padding:2px 8px;">
                                <i class="bi bi-x"></i>
                            </button>
                        </div>
                        <input type="text" id="searchInput" class="form-control form-control-sm" style="width:180px;"
                               placeholder="Filtrar recurso..." oninput="filterTable()">
                    </div>
                </div>
                <div class="card-body p-0">
                    <div style="overflow-x:auto;max-height:500px;overflow-y:auto;">
                        <table class="table table-hover table-fin mb-0" id="detailTable">
                            <thead style="position:sticky;top:0;z-index:1;">
                                <tr>
                                    <th style="width:160px;">Resource Name</th>
                                    <th style="width:100px;">Data</th>
                                    <th style="width:270px;">Meter ID</th>
                                    <th style="width:140px;">Subscription</th>
                                    <th style="width:280px;">Subscription ID</th>
                                    <th style="width:155px;">Servico / Meter</th>
                                    <th style="width:130px;">Resource Group</th>
                                    <th style="width:90px;">Região</th>
                                    <th style="width:85px;">Qtd</th>
                                    <th style="width:95px;">Custo MOSP</th>
                                    <th style="width:110px;">Preço Unit. FOB</th>
                                    <th style="width:135px;">Total sem Impostos</th>
                                    <th style="width:130px;">Total c/ Impostos</th>
                                    <th style="width:130px;">Total c/ Markup</th>
                                    <th style="width:215px;">Validação</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($results['results'] as $r):
                                $diff    = $r['difference'];
                                $dClass  = $diff !== null ? ($diff < -0.001 ? 'diff-negative' : ($diff > 0.001 ? 'diff-positive' : '')) : '';
                            ?>
                            <?php
                                // Normalise date to YYYY-MM-DD for data-date filter attribute
                                $isoDate_ = $toIso_(trim($r['date'] ?? ''));
                            ?>
                            <tr data-search="<?= htmlspecialchars(strtolower($r['resourceName'].'|'.$r['meterCategory'].'|'.$r['meterName'].'|'.$r['resourceGroup'].'|'.$r['resourceLocation'].'|'.$r['subscriptionId'].'|'.$r['subscriptionName'].'|'.$r['meterId'])) ?>"
                                data-date="<?= htmlspecialchars($isoDate_) ?>"
                                data-mosp="<?= $r['costMosp'] !== null ? number_format((float)$r['costMosp'], 10, '.', '') : '' ?>"
                                data-csp="<?= $r['costCsp'] !== null ? number_format((float)$r['costCsp'], 10, '.', '') : '' ?>">
                                <td>
                                    <div style="font-weight:500;font-size:.82rem;"><?= htmlspecialchars($r['resourceName'] ?: '—') ?></div>
                                </td>
                                <td style="font-size:.82rem;white-space:nowrap;"><?php
                                    // Display: converter ISO para DD/MM/YYYY; MM/DD/YYYY (Azure) → DD/MM/YYYY
                                    $iso = $toIso_(trim($r['date'] ?? ''));
                                    if ($iso !== '' && preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $iso, $m)) {
                                        echo $m[3] . '/' . $m[2] . '/' . $m[1];
                                    } else {
                                        echo '—';
                                    }
                                ?></td>
                                <td>
                                    <div class="meter-id" style="word-break:break-all;"><?= htmlspecialchars($r['meterId']) ?></div>
                                </td>
                                <td>
                                    <div style="font-size:.8rem;font-weight:500;"><?= htmlspecialchars($r['subscriptionName'] ?: '—') ?></div>
                                </td>
                                <td>
                                    <div class="sub-id" style="word-break:break-all;"><?= htmlspecialchars($r['subscriptionId'] ?: '—') ?></div>
                                </td>
                                <td>
                                    <div style="font-size:.82rem;"><?= htmlspecialchars($r['meterCategory'] ?: $r['serviceFamily'] ?: '—') ?></div>
                                    <?php if ($r['meterName']): ?>
                                    <div style="font-size:.72rem;color:#64748b;"><?= htmlspecialchars($r['meterName']) ?></div>
                                    <?php endif; ?>
                                    <?php if ($r['meterSubcategory']): ?>
                                    <div style="font-size:.68rem;color:#94a3b8;"><?= htmlspecialchars($r['meterSubcategory']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td style="font-size:.82rem;"><?= htmlspecialchars($r['resourceGroup'] ?: '—') ?></td>
                                <td style="font-size:.82rem;"><?= htmlspecialchars($r['resourceLocation'] ?: '—') ?></td>
                                <td style="font-size:.82rem;white-space:nowrap;"><?= number_format($r['quantity'], 2, ',', '.') ?> <span style="color:#94a3b8;font-size:.72rem;"><?= htmlspecialchars($r['unitOfMeasure']) ?></span></td>
                                <td style="font-size:.82rem;font-weight:600;"><?= fmtUsd($r['costMosp']) ?></td>
                                <?php
                                    $unitCsp_ = $r['unitPriceCsp'];
                                    $totalCsp_ = $r['costCsp']; // qty * unitCspApi
                                ?>
                                <td style="font-size:.82rem;">
                                    <?= $unitCsp_ !== null ? '$' . number_format($unitCsp_, 6, ',', '.') : '<span class="text-muted">N/D</span>' ?>
                                </td>
                                <td style="font-size:.82rem;font-weight:600;" class="col-total-fob"
                                    data-fob="<?= $totalCsp_ !== null ? number_format($totalCsp_, 10, '.', '') : '' ?>">
                                    <?= $totalCsp_ !== null ? fmtUsd($totalCsp_) : '<span class="text-muted">N/D</span>' ?>
                                </td>
                                <td style="font-size:.82rem;font-weight:600;" class="col-total-tax">
                                    <?= $totalCsp_ !== null ? fmtUsd($totalCsp_) : '<span class="text-muted">N/D</span>' ?>
                                </td>
                                <td style="font-size:.82rem;font-weight:600;" class="col-total-markup">
                                    <?= $totalCsp_ !== null ? fmtUsd($totalCsp_) : '<span class="text-muted">N/D</span>' ?>
                                </td>
                                <td>
                                    <?php
                                        // ── helpers inline ──
                                        $mId_      = $r['meterId'];
                                        $csvRaw_   = $r['productId'] ?? '';
                                        $csvSku_   = ($csvRaw_ !== '' && !str_contains($csvRaw_, '/') && strlen($csvRaw_) >= 5)
                                                     ? substr($csvRaw_, 0, -4) . '/' . substr($csvRaw_, -4)
                                                     : $csvRaw_;
                                        $csvNorm_  = strtolower(str_replace([' ','-','_'], '', $r['resourceLocation'] ?? ''));

                                        $filterStr_ = $r['apiFilterUsed'] ?? "meterId eq '{$mId_}'";
                                        if (!$r['priceFound']) {
                                            $f1_ = "meterId eq '{$mId_}'"
                                                 . ($csvSku_ !== '' ? " and skuId eq '{$csvSku_}'" : '')
                                                 . ($csvNorm_ !== '' ? " and armRegionName eq '{$csvNorm_}'" : '')
                                                 . (!empty($r['unitOfMeasure']) ? " and unitOfMeasure eq '{$r['unitOfMeasure']}'" : '')
                                                 . " and priceType eq 'Consumption'";
                                            $filterStr_ = $f1_;
                                        }
                                        $apiUrl_ = 'https://prices.azure.com/api/retail/prices?api-version=2023-01-01-preview&$filter=' . rawurlencode($filterStr_);

                                        // função inline para gerar badge de check
                                        $chk = function(string $label, ?string $csvVal, ?string $apiVal, bool $normalize = false) {
                                            if ($apiVal === null || $apiVal === '') {
                                                return '<span class="vchk vchk-na" title="' . htmlspecialchars($label) . ': não retornado pela API">' . htmlspecialchars($label) . '</span>';
                                            }
                                            $c = $normalize
                                                ? strtolower(str_replace([' ','-','_'], '', (string)$csvVal)) === strtolower($apiVal)
                                                : (string)$csvVal === (string)$apiVal;
                                            $icon = $c ? '✓' : '≠';
                                            $cls  = $c ? 'vchk-ok' : 'vchk-err';
                                            $tip  = htmlspecialchars("{$label}\nCSV: " . ($csvVal ?: '—') . "\nAPI: {$apiVal}");
                                            return "<span class=\"vchk {$cls}\" title=\"{$tip}\">{$icon} " . htmlspecialchars($label) . '</span>';
                                        };

                                        if ($r['priceFound']) {
                                            $lvl_     = (int)($r['apiMatchLevel'] ?? 0);
                                            $score_   = (int)($r['apiMatchScore'] ?? 0);
                                            $maxScr_  = (int)($r['apiMatchMaxScore'] ?? 0);
                                            $pct_     = $maxScr_ > 0 ? round($score_ / $maxScr_ * 100) : 0;
                                            $lvlTip_  = match($lvl_) {
                                                1 => 'Query: meterId + priceType=Consumption',
                                                2 => 'Query: meterId (sem filtro priceType — fallback)',
                                                default => 'Encontrado',
                                            };
                                            echo '<div class="d-flex flex-wrap gap-1" style="max-width:210px;">';
                                            // sku
                                            echo $chk('skuId', $csvSku_, $r['apiSkuId'] ?? null);
                                            // região
                                            echo $chk('Região', $csvNorm_, $r['apiArmRegion'] ?? null);
                                            // unitOfMeasure
                                            echo $chk('UoM', $r['unitOfMeasure'], $r['apiUnitOfMeasure'] ?? null);
                                            // meterName
                                            echo $chk('Meter', $r['meterName'], $r['cspMeterName'] ?? null);
                                            // score badge
                                            $scoreColor_ = $pct_ >= 90 ? 'vchk-ok' : ($pct_ >= 50 ? 'vchk-na' : 'vchk-err');
                                            echo '<span class="vchk ' . $scoreColor_ . '" title="' . htmlspecialchars("Score: {$score_}/{$maxScr_} ({$pct_}%)\n{$lvlTip_}") . '">' . $pct_ . '%</span>';
                                            // link API
                                            echo '<a href="' . htmlspecialchars($apiUrl_) . '" target="_blank" rel="noopener noreferrer" class="vchk vchk-na" title="Abrir query na API Microsoft" style="text-decoration:none;">&#x1F517; API</a>';
                                            echo '</div>';
                                        } else {
                                            echo '<div class="d-flex flex-wrap gap-1" style="max-width:210px;">';
                                            echo '<span class="vchk vchk-err">N/D</span>';
                                            echo '<a href="' . htmlspecialchars($apiUrl_) . '" target="_blank" rel="noopener noreferrer" class="vchk vchk-na" title="Tentar query na API Microsoft" style="text-decoration:none;">&#x1F517; API</a>';
                                            echo '</div>';
                                        }
                                    ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="p-2 px-3" style="font-size:.78rem;color:#94a3b8;border-top:1px solid #f1f5f9;">
                        <span id="rowCount"><?= count($results['results']) ?></span> de <?= count($results['results']) ?> linhas exibidas
                        <span id="dateFilterHint" style="margin-left:10px;color:#0097D7;font-weight:500;"></span>
                    </div>
                </div>
            </div>

        <?php else: ?>
            <!-- Empty state -->
            <div class="fin-card d-flex align-items-center justify-content-center" style="min-height:420px;">
                <div class="text-center py-5 px-4">
                    <div style="background:#ccfbf1;width:72px;height:72px;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 1.25rem;">
                        <i class="bi bi-cloud-arrow-up" style="font-size:2rem;color:var(--td-teal);"></i>
                    </div>
                    <h5 style="font-weight:700;color:#1e293b;">Nenhuma analise realizada</h5>
                    <p class="text-muted mb-0" style="max-width:400px;font-size:.87rem;">
                        Faca o upload do arquivo CSV exportado do <strong>Azure Cost Management</strong>
                        para calcular e comparar os custos MOSP vs CSP.
                    </p>
                </div>
            </div>
        <?php endif; ?>
        </div><!-- col-lg-9 -->
    </div><!-- row -->
</div><!-- container -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function updateMigrationHint() {
    const sel = document.getElementById('migrationType');
    const col = document.getElementById('migrationQtyCol');
    const hint = document.getElementById('migrationHint');
    if (!sel || !col) return;
    const isMpn = sel.value === 'mpn_csp';
    col.textContent = isMpn ? 'UsageQuantity' : 'Quantity';
    hint.style.color = isMpn ? '#7c3aed' : '#64748b';
}

// ============================================================
// CHUNKED UPLOAD — envia arquivo em pedaços de 2 MB
// Bypassa qualquer limite de Nginx / IIS / proxy
// ============================================================
const CHUNK_SIZE = 512 * 1024; // 512 KB por chunk — abaixo do limite padrão Nginx (1 MB)

function generateUploadId() {
    return 'up_' + Date.now() + '_' + Math.random().toString(36).substring(2, 10);
}

async function uploadChunked(file) {
    const totalChunks = Math.ceil(file.size / CHUNK_SIZE);
    const uploadId    = generateUploadId();

    const progressDiv  = document.getElementById('uploadProgress');
    const progressBar  = document.getElementById('uploadProgressBar');
    const progressText = document.getElementById('uploadProgressText');
    const progressDet  = document.getElementById('uploadProgressDetail');

    progressDiv.classList.remove('d-none');
    progressBar.style.width = '0%';
    progressText.textContent = '0%';
    progressDet.textContent = `Enviando ${file.name} (${(file.size / 1024 / 1024).toFixed(1)} MB) em ${totalChunks} parte(s)...`;

    for (let i = 0; i < totalChunks; i++) {
        const start = i * CHUNK_SIZE;
        const end   = Math.min(start + CHUNK_SIZE, file.size);
        const blob  = file.slice(start, end);

        const fd = new FormData();
        fd.append('chunk', blob, 'chunk');
        fd.append('chunkIndex',  String(i));
        fd.append('totalChunks', String(totalChunks));
        fd.append('uploadId',    uploadId);
        fd.append('fileName',    file.name);

        let retries = 0;
        let resp;
        while (retries < 3) {
            try {
                resp = await fetch('upload-chunk.php', { method: 'POST', body: fd });
                if (resp.ok) break;
            } catch (e) { /* retry */ }
            retries++;
            await new Promise(r => setTimeout(r, 1000 * retries));
        }

        if (!resp || !resp.ok) {
            const errText = resp ? await resp.text() : 'Sem resposta do servidor';
            throw new Error(`Falha no chunk ${i + 1}/${totalChunks}: ${errText}`);
        }

        const data = await resp.json();
        if (!data.ok) {
            throw new Error(data.error || `Erro no chunk ${i + 1}`);
        }

        const pct = Math.round(((i + 1) / totalChunks) * 100);
        progressBar.style.width = pct + '%';
        progressText.textContent = pct + '%';
        progressDet.textContent = `Parte ${i + 1} de ${totalChunks} enviada (${(end / 1024 / 1024).toFixed(1)} MB)`;
    }

    progressDet.textContent = 'Upload concluido! Iniciando analise...';
    return uploadId;
}

function handleFile(input) {
    const file = input.files[0];
    if (!file) return;
    document.getElementById('fileName').textContent = file.name;
    document.getElementById('selectedFile').classList.remove('d-none');
    document.getElementById('analyzeBtn').disabled = false;
    document.querySelector('.upload-zone').style.borderColor = 'var(--td-teal)';
}

// Interceptar submit do formulário
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('uploadForm');
    if (!form) return;

    form.addEventListener('submit', async function(e) {
        const fileInput = document.getElementById('fileInput');
        const file = fileInput?.files?.[0];

        // Se não tem arquivo, deixa o form submeter normalmente (pode ser ação sem upload)
        if (!file) return;

        // Impede o submit padrão
        e.preventDefault();

        const btn = document.getElementById('analyzeBtn');
        const progressDiv = document.getElementById('uploadProgress');
        const progressDet = document.getElementById('uploadProgressDetail');

        btn.disabled = true;
        btn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Enviando...';

        try {
            const uploadId = await uploadChunked(file);

            // Colocar o uploadId no form e limpar o file input para não enviar via multipart
            document.getElementById('uploadIdField').value = uploadId;

            // Remover o file para evitar que o form tente enviar o arquivo grande
            fileInput.value = '';

            btn.innerHTML = '<i class="bi bi-gear me-1 spin-icon"></i>Processando API...';
            if (progressDet) progressDet.textContent = 'Upload concluido! Processando analise na API Microsoft...';

            // Submeter o form normalmente (agora sem o arquivo, apenas com uploadId)
            form.submit();
        } catch (err) {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-search me-1"></i>Calcular Custos CSP';
            progressDiv.classList.add('d-none');
            alert('Erro no upload: ' + err.message);
        }
    });
});

// Drag-and-drop
const dz = document.getElementById('dropZone');
if (dz) {
    dz.addEventListener('dragover',  e => { e.preventDefault(); dz.classList.add('dragover'); });
    dz.addEventListener('dragleave', ()  => dz.classList.remove('dragover'));
    dz.addEventListener('drop',      e  => {
        e.preventDefault();
        dz.classList.remove('dragover');
        const fi = document.getElementById('fileInput');
        fi.files = e.dataTransfer.files;
        handleFile(fi);
    });
}

function clearDateFilter() {
    const df = document.getElementById('dateFrom');
    const dt = document.getElementById('dateTo');
    if (df) df.value = df.min || '';
    if (dt) dt.value = dt.max || '';
    filterTable();
}

function filterTable() {
    const q       = (document.getElementById('searchInput')?.value || '').toLowerCase();
    const dFrom   = document.getElementById('dateFrom')?.value  || '';
    const dTo     = document.getElementById('dateTo')?.value    || '';
    const rows    = document.querySelectorAll('#detailTable tbody tr');
    let visible   = 0;

    rows.forEach(tr => {
        const matchText = !q || tr.dataset.search.includes(q);
        const rowDate   = (tr.dataset.date || '').substring(0, 10); // YYYY-MM-DD
        // Rows with no parseable date are never hidden by the date filter
        const matchFrom = !dFrom || !rowDate || rowDate >= dFrom;
        const matchTo   = !dTo   || !rowDate || rowDate <= dTo;
        const show      = matchText && matchFrom && matchTo;
        tr.style.display = show ? '' : 'none';
        if (show) visible++;
    });

    const rc = document.getElementById('rowCount');
    if (rc) rc.textContent = visible;

    // Populate date-range hint
    updateDateHint(dFrom, dTo);

    // Recalc sums from visible rows only
    recalcTable();
}

function updateDateHint(dFrom, dTo) {
    const hint = document.getElementById('dateFilterHint');
    if (!hint) return;
    if (!dFrom && !dTo) { hint.textContent = ''; return; }
    const fmt = d => d ? new Date(d + 'T00:00:00').toLocaleDateString('pt-BR') : '…';
    hint.textContent = `Período: ${fmt(dFrom)} – ${fmt(dTo)}`;
}

function fmtUsdJs(v) {
    return '$' + v.toLocaleString('pt-BR', {minimumFractionDigits:2, maximumFractionDigits:2});
}

function recalcTable() {
    const tax    = parseFloat(document.getElementById('taxInput').value)    || 0;
    const markup = parseFloat(document.getElementById('markupInput').value) || 0;
    const tFactor = 1 + (tax    / 100);
    const mFactor = 1 + (markup / 100);

    const exchRate = <?= $results ? (float)($results['summary']['exchangeRate'] ?? 5.39) : 5.39 ?>;

    let sumFob = 0, sumTax = 0, sumMarkup = 0;

    document.querySelectorAll('#detailTable tbody tr').forEach(tr => {
        const fobTd    = tr.querySelector('.col-total-fob');
        const taxTd    = tr.querySelector('.col-total-tax');
        const markupTd = tr.querySelector('.col-total-markup');
        const visible  = tr.style.display !== 'none';

        if (fobTd) {
            const raw = parseFloat(fobTd.dataset.fob);
            if (!isNaN(raw)) {
                const withTax    = raw * tFactor;
                const withMarkup = withTax * mFactor;
                if (taxTd)    taxTd.textContent    = fmtUsdJs(withTax);
                if (markupTd) markupTd.textContent = fmtUsdJs(withMarkup);
                if (visible) { sumFob += raw; sumTax += withTax; sumMarkup += withMarkup; }
            }
        }

    });

    // Cards — Impostos / Markup
    const cardTax    = document.getElementById('cardTotalTax');
    const cardTaxBrl = document.getElementById('cardTotalTaxBrl');
    const cardMkp    = document.getElementById('cardTotalMarkup');
    const cardMkpBrl = document.getElementById('cardTotalMarkupBrl');
    if (cardTax) {
        cardTax.textContent    = fmtUsdJs(sumTax);
        cardTaxBrl.textContent = 'R$ ' + (sumTax * exchRate).toLocaleString('pt-BR', {minimumFractionDigits:2, maximumFractionDigits:2});
    }
    if (cardMkp) {
        cardMkp.textContent    = fmtUsdJs(sumMarkup);
        cardMkpBrl.textContent = 'R$ ' + (sumMarkup * exchRate).toLocaleString('pt-BR', {minimumFractionDigits:2, maximumFractionDigits:2});
    }

    // Card — FOB (live)
    const cardFob    = document.getElementById('cardTotalFob');
    const cardFobBrl = document.getElementById('cardTotalFobBrl');
    if (cardFob) {
        cardFob.textContent    = fmtUsdJs(sumFob);
        cardFobBrl.textContent = 'R$ ' + (sumFob * exchRate).toLocaleString('pt-BR', {minimumFractionDigits:2, maximumFractionDigits:2});
    }

}

// Bootstrap Tooltips

// Auto-apply date filter on page load so pre-populated dates take effect
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('detailTable')) filterTable();
});
(function initColResize() {
    const table = document.getElementById('detailTable');
    if (!table) return;

    const ths = Array.from(table.querySelectorAll('thead tr:first-child th'));

    // Inject resize handles + lock current widths as pixels
    ths.forEach(th => {
        th.style.width = th.getBoundingClientRect().width + 'px';
        const h = document.createElement('div');
        h.className = 'col-resize-handle';
        th.appendChild(h);
    });
    table.style.tableLayout = 'fixed';

    let dragging = false, startX = 0, startW = 0, activeTh = null, activeHandle = null;

    ths.forEach(th => {
        const handle = th.querySelector('.col-resize-handle');
        handle.addEventListener('mousedown', e => {
            dragging   = true;
            startX     = e.clientX;
            startW     = th.getBoundingClientRect().width;
            activeTh   = th;
            activeHandle = handle;
            handle.classList.add('active');
            document.body.style.cursor = 'col-resize';
            e.preventDefault();
            e.stopPropagation();
        });
    });

    document.addEventListener('mousemove', e => {
        if (!dragging || !activeTh) return;
        const newW = Math.max(50, startW + (e.clientX - startX));
        activeTh.style.width = newW + 'px';
    });

    document.addEventListener('mouseup', () => {
        if (activeHandle) activeHandle.classList.remove('active');
        document.body.style.cursor = '';
        dragging = false; activeTh = null; activeHandle = null;
    });
})();
</script>
<?php if ($results): ?>
<script>
const pdfData = <?= json_encode([
    'clientName'     => $results['clientName']     ?? '',
    'referenceMonth' => $results['referenceMonth'] ?? '',
    'filename'       => $results['filename']       ?? '',
    'exchangeRate'   => (float)($results['summary']['exchangeRate'] ?? 5.39),
    'migrationType'  => $results['migrationType']  ?? 'mosp_csp',
    'migrationLabel' => migrationLabel($results['migrationType'] ?? 'mosp_csp'),
    'summary' => [
        'totalRows'       => $results['summary']['totalRows'],
        'uniqueMeterIds'  => $results['summary']['uniqueMeterIds'],
        'notFoundCount'   => $results['summary']['notFoundCount'],
        'totalMosp'       => (float)$results['summary']['totalMosp'],
        'totalMospBrl'    => (float)$results['summary']['totalMospBrl'],
        'totalCsp'        => (float)$results['summary']['totalCsp'],
        'totalCspBrl'     => (float)$results['summary']['totalCspBrl'],
        'differencePercent' => (float)$results['summary']['differencePercent'],
        'byService'       => array_map(fn($v) => ['costMosp' => (float)$v['costMosp'], 'costCsp' => (float)($v['costCsp'] ?? 0), 'count' => (int)$v['count']], array_slice($results['summary']['byService'], 0, 8, true)),
        'byResourceGroup' => array_map(fn($v) => ['costMosp' => (float)$v['costMosp'], 'costCsp' => (float)($v['costCsp'] ?? 0), 'count' => (int)$v['count']], array_slice($results['summary']['byResourceGroup'], 0, 8, true)),
    ],
], JSON_UNESCAPED_UNICODE) ?>;

// Pre-load logo once and cache it globally
(function _preloadPdfLogo() {
    if (window._finLogoDataUrl !== undefined) return;
    window._finLogoDataUrl = null;
    const img = new Image();
    img.crossOrigin = 'anonymous';
    img.onload = function() {
        const c = document.createElement('canvas');
        c.width = img.width; c.height = img.height;
        c.getContext('2d').drawImage(img, 0, 0);
        window._finLogoDataUrl = c.toDataURL('image/png');
    };
    img.onerror = function() { window._finLogoDataUrl = null; };
    img.src = 'logo.png';
})();

function _pdfHeader(doc, title, W, TEAL, BLUE, WHITE, DARK, GRAY, ML) {
    const GRAY_L = [160, 165, 170];
    const BORDER = [226, 232, 240];
    // Thin teal bar
    doc.setFillColor(...TEAL);
    doc.rect(0, 0, W, 4, 'F');
    // Logo or text
    const _logo = window._finLogoDataUrl || null;
    if (_logo) {
        try { doc.addImage(_logo, 'PNG', ML, 9, 36, 7.3); } catch(e) {}
    } else {
        doc.setFontSize(10); doc.setTextColor(...TEAL);
        doc.setFont('helvetica', 'bold'); doc.text('TD SYNNEX', ML, 15);
    }
    // Date right
    const _today = new Date().toLocaleDateString('pt-BR', {day:'2-digit', month:'2-digit', year:'numeric'});
    doc.setFontSize(7.5); doc.setTextColor(...GRAY_L);
    doc.setFont('helvetica', 'normal'); doc.text(_today, W - ML, 15, {align:'right'});
    // Separator
    doc.setDrawColor(...BORDER); doc.setLineWidth(0.3);
    doc.line(ML, 20, W - ML, 20);
    // Page title + accent line
    doc.setFontSize(13); doc.setTextColor(...DARK);
    doc.setFont('helvetica', 'bold'); doc.text(title, ML, 31);
    doc.setFillColor(...TEAL); doc.rect(ML, 34, 28, 1.8, 'F');
}

async function gerarPropostaPDF() {
    const btn = document.getElementById('btnPdf');
    if (btn) { btn.disabled = true; btn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Gerando...'; }
    // Ensure logo is loaded
    if (window._finLogoDataUrl === null) {
        await new Promise(resolve => {
            const img = new Image();
            img.crossOrigin = 'anonymous';
            img.onload = function() {
                const c = document.createElement('canvas');
                c.width = img.width; c.height = img.height;
                c.getContext('2d').drawImage(img, 0, 0);
                window._finLogoDataUrl = c.toDataURL('image/png');
                resolve();
            };
            img.onerror = function() { window._finLogoDataUrl = null; resolve(); };
            img.src = 'logo.png';
        });
    }
    await new Promise(r => setTimeout(r, 30));
    try { _doGerarPDF(); } finally {
        if (btn) { btn.disabled = false; btn.innerHTML = '<i class="bi bi-file-earmark-pdf me-1"></i>Proposta PDF'; }
    }
}

function _doGerarPDF() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF({ orientation: 'portrait', unit: 'mm', format: 'a4' });
    const W = 210, H = 297, ML = 15, MR = 15;
    const CW = W - ML - MR;

    const TEAL      = [0, 87, 88];
    const TEAL_DARK = [0, 48, 49];
    const BLUE      = [0, 151, 215];
    const GREEN_C   = [130, 195, 65];
    const DARK      = [30, 41, 59];
    const GRAY      = [100, 116, 139];
    const WHITE     = [255, 255, 255];
    const LIGHT     = [248, 250, 252];
    const ECO_GREEN = [22, 163, 74];
    const ECO_RED   = [220, 38, 38];

    // --- Collect live data from DOM ---
    const tax    = parseFloat(document.getElementById('taxInput')?.value)    || 0;
    const markup = parseFloat(document.getElementById('markupInput')?.value) || 0;
    const tFactor = 1 + tax / 100;
    const mFactor = 1 + markup / 100;
    const exch = pdfData.exchangeRate;

    let sumFob = 0, sumMosp = 0;
    const visibleRows = [];
    document.querySelectorAll('#detailTable tbody tr').forEach(tr => {
        if (tr.style.display === 'none') return;
        const fobRaw = parseFloat(tr.querySelector('.col-total-fob')?.dataset?.fob) || 0;
        const mosp   = parseFloat(tr.dataset.mosp) || 0;
        sumFob  += fobRaw;
        sumMosp += mosp;
        const cells = tr.querySelectorAll('td');
        visibleRows.push({
            resource : (cells[0]?.querySelector('div')?.textContent || '—').trim(),
            date     : (cells[1]?.textContent || '—').trim(),
            service  : (cells[5]?.querySelector('div')?.textContent || '—').trim(),
            rg       : (cells[6]?.textContent || '—').trim(),
            region   : (cells[7]?.textContent || '—').trim(),
            mosp, fob: fobRaw,
            tax      : fobRaw * tFactor,
            markup   : fobRaw * tFactor * mFactor,
        });
    });
    const sumTax    = sumFob * tFactor;
    const sumMarkup = sumTax * mFactor;
    const saving    = sumMosp - sumFob;
    const isEco     = saving >= 0;
    const pctAbs    = sumMosp > 0 ? Math.abs(saving / sumMosp * 100) : 0;

    const fmtU  = v => '$' + v.toLocaleString('pt-BR', {minimumFractionDigits:2, maximumFractionDigits:2});
    const fmtB  = v => 'R$ ' + (v * exch).toLocaleString('pt-BR', {minimumFractionDigits:2, maximumFractionDigits:2});
    const fmtN  = (v, d) => v.toLocaleString('pt-BR', {minimumFractionDigits:d, maximumFractionDigits:d});
    const trunc = (s, n) => s && s.length > n ? s.substring(0, n - 1) + '...' : (s || '—');

    // ============================================================
    // PAGE 1 — CAPA (sql-advisor design)
    // ============================================================
    const TEAL_L   = [0, 128, 130];
    const GRAY_L   = [160, 165, 170];
    const BG       = [245, 247, 250];
    const BORDER   = [226, 232, 240];
    const CHARCOAL = [38, 38, 38];
    const todayFull = new Date().toLocaleDateString('pt-BR', {day:'numeric', month:'long', year:'numeric'});
    const dFrom = document.getElementById('dateFrom')?.value;
    const dTo   = document.getElementById('dateTo')?.value;
    const fmtDate = d => d ? new Date(d + 'T00:00:00').toLocaleDateString('pt-BR') : '...';

    // White background
    doc.setFillColor(...WHITE); doc.rect(0, 0, W, H, 'F');
    // Thin teal bar
    doc.setFillColor(...TEAL); doc.rect(0, 0, W, 4, 'F');
    // Logo
    const _logo = window._finLogoDataUrl || null;
    if (_logo) {
        try { doc.addImage(_logo, 'PNG', ML, 18, 50, 10.2); } catch(e) {}
    } else {
        doc.setFontSize(16); doc.setTextColor(...TEAL_DARK);
        doc.setFont('helvetica', 'bold'); doc.text('TD SYNNEX', ML, 26);
    }
    // Teal side accent
    doc.setFillColor(...TEAL); doc.rect(0, 80, 6, 95, 'F');

    // Title
    doc.setFontSize(11); doc.setTextColor(...TEAL_L);
    doc.setFont('helvetica', 'bold');
    doc.text('PROPOSTA COMERCIAL', ML + 8, 100);

    doc.setFontSize(30); doc.setTextColor(...CHARCOAL);
    doc.setFont('helvetica', 'bold');
    doc.text('Analise Financeira', ML + 8, 118);
    doc.setFontSize(22);
    doc.text(pdfData.migrationLabel || 'MOSP para Azure CSP', ML + 8, 131);

    doc.setFontSize(12); doc.setTextColor(...GRAY);
    doc.setFont('helvetica', 'normal');
    doc.text('Comparativo de Custos via TD SYNNEX', ML + 8, 141);

    // Client box
    const clientBoxY = 158;
    doc.setFillColor(...BG);
    doc.roundedRect(ML + 8, clientBoxY, CW - 8, 28, 3, 3, 'F');
    doc.setFillColor(...TEAL); doc.rect(ML + 8, clientBoxY, 3, 28, 'F');
    doc.setFontSize(8); doc.setTextColor(...TEAL_L);
    doc.setFont('helvetica', 'bold');
    doc.text('PREPARADO PARA', ML + 18, clientBoxY + 9);
    doc.setFontSize(17); doc.setTextColor(...CHARCOAL);
    doc.setFont('helvetica', 'bold');
    const clientLabel = (pdfData.clientName || 'Cliente').substring(0, 42);
    doc.text(clientLabel, ML + 18, clientBoxY + 21);

    // Bottom metadata bar
    const metaY = H - 52;
    doc.setDrawColor(...BORDER); doc.setLineWidth(0.3);
    doc.line(ML, metaY, W - MR, metaY);
    const metaCols = [
        {label: 'DATA DA ANALISE', value: todayFull},
        {label: 'PERIODO',         value: (dFrom || dTo) ? fmtDate(dFrom) + ' a ' + fmtDate(dTo) : 'Todo o periodo'},
    ];
    const colW_ = CW / metaCols.length;
    metaCols.forEach((col, i) => {
        const cx = ML + i * colW_;
        doc.setFontSize(7); doc.setTextColor(...TEAL_L);
        doc.setFont('helvetica', 'bold'); doc.text(col.label, cx, metaY + 9);
        doc.setFontSize(9); doc.setTextColor(...CHARCOAL);
        doc.setFont('helvetica', 'normal');
        const val = col.value.length > 28 ? col.value.substring(0, 27) + '…' : col.value;
        doc.text(val, cx, metaY + 17);
    });
    // Confidential note
    doc.setFontSize(7); doc.setTextColor(...GRAY_L);
    doc.setFont('helvetica', 'italic');
    doc.text('Documento Confidencial — TD SYNNEX Brasil — Valores sujeitos a alteracao', ML, H - 16);

    let y = 125; // reset y (used by pages 2+)

    // ============================================================
    // PAGE 2 — SUMARIO EXECUTIVO
    // ============================================================
    doc.addPage();
    _pdfHeader(doc, 'Sumario Executivo', W, TEAL, BLUE, WHITE, DARK, GRAY, ML);

    y = 44;

    // Row 1: 3 metric cards
    const cardW3 = (CW - 8) / 3;
    [
        { label: 'Total MOSP (Atual)',  val: fmtU(sumMosp),   sub: fmtB(sumMosp),   color: GRAY },
        { label: 'Total CSP FOB',       val: fmtU(sumFob),    sub: fmtB(sumFob),    color: TEAL },
        { label: 'Taxa de Cambio',      val: 'R$ ' + fmtN(exch, 2), sub: 'USD > BRL', color: BLUE },
    ].forEach((m, i) => {
        const x = ML + i * (cardW3 + 4);
        doc.setFillColor(...LIGHT);
        doc.roundedRect(x, y, cardW3, 26, 3, 3, 'F');
        doc.setFillColor(...m.color);
        doc.rect(x, y, cardW3, 2, 'F');
        doc.setFont('helvetica', 'bold'); doc.setFontSize(7); doc.setTextColor(...GRAY);
        doc.text(m.label.toUpperCase(), x + 3, y + 9);
        doc.setFont('helvetica', 'bold'); doc.setFontSize(12); doc.setTextColor(...DARK);
        doc.text(m.val, x + 3, y + 18);
        doc.setFont('helvetica', 'normal'); doc.setFontSize(7.5); doc.setTextColor(...GRAY);
        doc.text(m.sub, x + 3, y + 23);
    });
    y += 32;

    // Row 2: 3 more cards
    [
        { label: tax > 0 ? 'Total c/ Impostos (' + fmtN(tax,2) + '%)' : 'Total c/ Impostos', val: fmtU(sumTax), sub: fmtB(sumTax), color: BLUE },
        { label: markup > 0 ? 'Total c/ Markup (' + fmtN(markup,1) + '%)' : 'Total c/ Markup',     val: fmtU(sumMarkup), sub: fmtB(sumMarkup), color: [0,150,136] },
        { label: isEco ? 'Economia (MOSP vs CSP)' : 'Variacao (MOSP vs CSP)', val: (isEco ? '-' : '+') + fmtU(Math.abs(saving)), sub: (isEco ? '-' : '+') + fmtN(pctAbs,1) + '%', color: isEco ? ECO_GREEN : ECO_RED },
    ].forEach((m, i) => {
        const x = ML + i * (cardW3 + 4);
        doc.setFillColor(...LIGHT);
        doc.roundedRect(x, y, cardW3, 26, 3, 3, 'F');
        doc.setFillColor(...m.color);
        doc.rect(x, y, cardW3, 2, 'F');
        doc.setFont('helvetica', 'bold'); doc.setFontSize(7); doc.setTextColor(...GRAY);
        doc.text(m.label.toUpperCase(), x + 3, y + 9);
        doc.setFont('helvetica', 'bold'); doc.setFontSize(12); doc.setTextColor(...DARK);
        doc.text(m.val, x + 3, y + 18);
        doc.setFont('helvetica', 'normal'); doc.setFontSize(7.5); doc.setTextColor(...GRAY);
        doc.text(m.sub, x + 3, y + 23);
    });
    y += 34;

    // Parameters table
    doc.setFont('helvetica', 'bold'); doc.setFontSize(11); doc.setTextColor(...DARK);
    doc.text('Parametros da Analise', ML, y); y += 5;

    const params = [
        ['Total de registros no CSV',          pdfData.summary.totalRows + ' registros'],
        ['Linhas exibidas (filtro ativo)',      visibleRows.length + ' registros'],
        ['MeterIDs unicos consultados na API',  pdfData.summary.uniqueMeterIds + ' IDs'],
        ['MeterIDs encontrados na API',         (pdfData.summary.uniqueMeterIds - pdfData.summary.notFoundCount) + ' de ' + pdfData.summary.uniqueMeterIds],
        ['Impostos aplicados',                  tax > 0 ? fmtN(tax, 2) + '%' : 'Nao aplicado'],
        ['Markup aplicado',                     markup > 0 ? fmtN(markup, 1) + '%' : 'Nao aplicado'],
        ['Periodo analisado',                   dFrom ? fmtDate(dFrom) + ' a ' + fmtDate(dTo) : 'Todo o periodo'],
        ['Arquivo CSV',                         pdfData.filename || '—'],
    ];
    params.forEach(([label, val], i) => {
        const ry = y + i * 8.5;
        if (i % 2 === 0) { doc.setFillColor(...LIGHT); doc.rect(ML, ry - 3.5, CW, 8.5, 'F'); }
        doc.setFont('helvetica', 'normal'); doc.setFontSize(8.5); doc.setTextColor(...GRAY);
        doc.text(label, ML + 3, ry + 1);
        doc.setFont('helvetica', 'bold'); doc.setTextColor(...DARK);
        doc.text(val, W - MR, ry + 1, {align: 'right'});
    });
    y += params.length * 8.5 + 8;

    // Top Services
    const svcEntries = Object.entries(pdfData.summary.byService);
    if (svcEntries.length > 0) {
        doc.setFont('helvetica', 'bold'); doc.setFontSize(11); doc.setTextColor(...DARK);
        doc.text('Principais Servicos Azure', ML, y); y += 3;
        const svcRows = svcEntries.slice(0, 7).map(([name, d]) => [
            trunc(name, 38),
            fmtU(d.costMosp),
            fmtU(d.costCsp),
            d.costMosp > 0 ? (((d.costCsp / d.costMosp) - 1) * 100).toLocaleString('pt-BR',{minimumFractionDigits:1,maximumFractionDigits:1}) + '%' : '—',
            d.count + ' linhas',
        ]);
        doc.autoTable({
            startY: y,
            head: [['Servico', 'Custo MOSP', 'Custo CSP FOB', 'Var.%', 'Linhas']],
            body: svcRows,
            styles: { fontSize: 8, cellPadding: 2 },
            headStyles: { fillColor: TEAL, textColor: WHITE, fontStyle: 'bold', fontSize: 8 },
            alternateRowStyles: { fillColor: LIGHT },
            margin: { left: ML, right: MR },
            tableWidth: CW,
            columnStyles: { 0: {cellWidth: 65}, 1: {halign:'right'}, 2: {halign:'right'}, 3: {halign:'right'}, 4: {halign:'right'} },
        });
        y = doc.lastAutoTable.finalY + 6;
    }

    // Top Resource Groups
    const rgEntries = Object.entries(pdfData.summary.byResourceGroup);
    if (rgEntries.length > 0 && y < H - 60) {
        doc.setFont('helvetica', 'bold'); doc.setFontSize(11); doc.setTextColor(...DARK);
        doc.text('Principais Resource Groups', ML, y); y += 3;
        const rgRows = rgEntries.slice(0, 6).map(([name, d]) => [
            trunc(name, 38),
            fmtU(d.costMosp),
            fmtU(d.costCsp),
            d.count + ' linhas',
        ]);
        doc.autoTable({
            startY: y,
            head: [['Resource Group', 'Custo MOSP', 'Custo CSP FOB', 'Linhas']],
            body: rgRows,
            styles: { fontSize: 8, cellPadding: 2 },
            headStyles: { fillColor: TEAL, textColor: WHITE, fontStyle: 'bold', fontSize: 8 },
            alternateRowStyles: { fillColor: LIGHT },
            margin: { left: ML, right: MR },
            tableWidth: CW,
            columnStyles: { 0: {cellWidth: 70}, 1: {halign:'right'}, 2: {halign:'right'}, 3: {halign:'right'} },
        });
    }

    // ============================================================
    // PAGE 3 — POR QUE AZURE CSP VIA TD SYNNEX
    // ============================================================
    doc.addPage();
    _pdfHeader(doc, 'Por que Azure CSP via TD SYNNEX?', W, TEAL, BLUE, WHITE, DARK, GRAY, ML);

    y = 44;
    doc.setFont('helvetica', 'normal'); doc.setFontSize(9.5); doc.setTextColor(...GRAY);
    const introTxt = 'A migracao para o modelo Azure CSP via TD SYNNEX representa a estrategia mais vantajosa para empresas que buscam otimizar custos de nuvem com suporte especializado, faturamento local e condicoes comerciais exclusivas no mercado brasileiro.';
    const introLines = doc.splitTextToSize(introTxt, CW);
    doc.text(introLines, ML, y);
    y += introLines.length * 4.8 + 8;

    const benefits = [
        {
            title: 'Precos CSP com Desconto vs. MOSP (Pay-As-You-Go)',
            desc:  'Acesso a precos do canal Microsoft CSP, sistematicamente inferiores ao modelo MOSP. Faturamento flexivel em BRL elimina exposicao ao cambio USD e simplifica o planejamento financeiro.',
        },
        {
            title: 'Suporte Especializado em Portugues',
            desc:  'Time dedicado de especialistas Azure certificados, com SLA de atendimento, suporte tecnico L1/L2 em portugues e acompanhamento proativo da assinatura para maximizar eficiencia operacional.',
        },
        {
            title: 'Faturamento em Real com Nota Fiscal Brasileira',
            desc:  'Emissao de NF-e brasileira, contrato local em conformidade com a legislacao nacional (LGPD, ANATEL), sem complexidades de transacoes internacionais ou exposicao cambial.',
        },
        {
            title: 'Governanca, FinOps e Gestao de Custos',
            desc:  'Implementacao de politicas de governanca, gestao de budget por centro de custo, alertas automaticos de consumo e relatorios periodicos de rightsizing para eliminar recursos ociosos.',
        },
        {
            title: 'Azure Hybrid Benefit + Reserved Instances',
            desc:  'Orientacao para aproveitar o Azure Hybrid Benefit (reutilizacao de licencas Windows Server e SQL Server existentes) e Reserved Instances com economia adicional de ate 72% vs PAYG.',
        },
        {
            title: 'Flexibilidade sem Lock-in de Contrato',
            desc:  'O modelo CSP oferece escala imediata sem compromisso de prazo minimo obrigatorio, permitindo ajustar recursos conforme a demanda do negocio mes a mes com total agilidade.',
        },
    ];

    benefits.forEach(b => {
        const descLines = doc.splitTextToSize(b.desc, CW - 12);
        const boxH = 9 + descLines.length * 4.3 + 4;
        if (y + boxH > H - 28) { doc.addPage(); _pdfHeader(doc, 'Por que Azure CSP via TD SYNNEX?', W, TEAL, BLUE, WHITE, DARK, GRAY, ML); y = 44; }
        doc.setFillColor(240, 253, 252);
        doc.roundedRect(ML, y, CW, boxH, 3, 3, 'F');
        doc.setFillColor(...TEAL);
        doc.rect(ML, y, 3, boxH, 'F');
        doc.setFont('helvetica', 'bold'); doc.setFontSize(9.5); doc.setTextColor(...TEAL);
        doc.text(b.title, ML + 7, y + 7);
        doc.setFont('helvetica', 'normal'); doc.setFontSize(8.5); doc.setTextColor(...DARK);
        doc.text(descLines, ML + 7, y + 13);
        y += boxH + 5;
    });

    // TD SYNNEX promo box
    if (y + 28 < H - 28) {
        doc.setFillColor(...TEAL);
        doc.roundedRect(ML, y, CW, 28, 4, 4, 'F');
        doc.setTextColor(...WHITE);
        doc.setFont('helvetica', 'bold'); doc.setFontSize(11);
        doc.text('TD SYNNEX - Maior Distribuidor Microsoft no Brasil', W / 2, y + 10, {align: 'center'});
        doc.setFont('helvetica', 'normal'); doc.setFontSize(8.5);
        const promoLines = doc.splitTextToSize('Acesso a programas exclusivos Microsoft, descontos de back-end, rebates e beneficios do programa CSP Tier 1 - todos repassados em condicoes competitivas ao cliente final.', CW - 20);
        doc.text(promoLines, W / 2, y + 18, {align: 'center'});
        y += 36;
    }

    // ============================================================
    // PAGE 4 — DETALHAMENTO LINHA A LINHA
    // ============================================================
    if (visibleRows.length > 0) {
        doc.addPage();
        _pdfHeader(doc, 'Detalhamento de Recursos (' + visibleRows.length + ' linhas)', W, TEAL, BLUE, WHITE, DARK, GRAY, ML);

        const hasTax    = tax > 0;
        const hasMarkup = markup > 0;
        const headers   = ['Recurso', 'Servico', 'Resource Group', 'Data', 'Regiao', 'MOSP', 'CSP FOB',
            ...(hasTax    ? ['c/ Impostos'] : []),
            ...(hasMarkup ? ['c/ Markup']   : []),
        ];
        const tableRows = visibleRows.map(r => [
            trunc(r.resource, 26),
            trunc(r.service,  20),
            trunc(r.rg,       16),
            r.date,
            trunc(r.region,   10),
            fmtU(r.mosp),
            fmtU(r.fob),
            ...(hasTax    ? [fmtU(r.tax)]    : []),
            ...(hasMarkup ? [fmtU(r.markup)] : []),
        ]);

        doc.autoTable({
            startY: 38,
            head: [headers],
            body: tableRows,
            styles: { fontSize: 6, cellPadding: 1.5, overflow: 'ellipsize' },
            headStyles: { fillColor: TEAL, textColor: WHITE, fontStyle: 'bold', fontSize: 6.5 },
            alternateRowStyles: { fillColor: LIGHT },
            margin: { left: ML, right: MR },
            tableWidth: CW,
            columnStyles: {
                0: {cellWidth: 32},
                1: {cellWidth: 28},
                2: {cellWidth: 22},
                3: {cellWidth: 17},
                4: {cellWidth: 14},
                5: {halign:'right', cellWidth: 17},
                6: {halign:'right', cellWidth: 17},
                ...(hasTax    ? {7: {halign:'right', cellWidth: 17}} : {}),
                ...(hasMarkup ? {8: {halign:'right', cellWidth: 17}} : {}),
            },
            didDrawPage: data => {
                const pg = doc.getCurrentPageInfo().pageNumber;
                const fy = H - 14;
                doc.setDrawColor(226, 232, 240); doc.setLineWidth(0.3);
                doc.line(ML, fy, W - MR, fy);
                doc.setTextColor(160, 165, 170); doc.setFont('helvetica','normal'); doc.setFontSize(6.5);
                doc.text('TD SYNNEX  |  Documento Confidencial  |  Valores estimados sujeitos a alteracao', ML, fy + 5);
                doc.text('Pag. ' + pg, W - MR, fy + 5, {align: 'right'});
            },
        });
    }

    // Footer on pages 1–3 (and page 4 cover if no detail)
    const totalPages = doc.getNumberOfPages();
    const detailStart = visibleRows.length > 0 ? totalPages - (doc.getNumberOfPages() - 3) : 99;
    for (let p = 1; p <= totalPages; p++) {
        if (p >= 4 && visibleRows.length > 0) continue; // handled by autoTable didDrawPage
        doc.setPage(p);
        if (p > 1) { // page 1 already has its own footer
            const fy = H - 14;
            doc.setDrawColor(226, 232, 240); doc.setLineWidth(0.3);
            doc.line(ML, fy, W - MR, fy);
            doc.setTextColor(160, 165, 170); doc.setFont('helvetica','normal'); doc.setFontSize(6.5);
            doc.text('TD SYNNEX  |  Documento Confidencial  |  Valores estimados sujeitos a alteracao', ML, fy + 5);
            doc.text('Pag. ' + p + ' / ' + totalPages, W - MR, fy + 5, {align: 'right'});
        }
    }

    const fname = 'Proposta_Azure_CSP_' +
        (pdfData.clientName ? pdfData.clientName.replace(/[^a-zA-Z0-9]/g, '_') + '_' : '') +
        new Date().toISOString().slice(0, 10) + '.pdf';
    doc.save(fname);
}
</script>
<?php endif; ?>
</body>
</html>