<?php

declare(strict_types=1);

/**
 * Sistema de Análise de Migração de Recursos Azure
 * 
 * Página principal para upload e análise de arquivos
 */

// Configurações de erro para desenvolvimento
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

// Autoloader do Composer
require_once __DIR__ . '/../vendor/autoload.php';

use AzureMigration\FileParser;
use AzureMigration\AzureResourceAnalyzer;
use AzureMigration\ReportGenerator;

// Configurações
$uploadDir = __DIR__ . '/../uploads';
$reportsDir = __DIR__ . '/../reports';

// Inicialização das classes
$fileParser = new FileParser();
$analyzer = new AzureResourceAnalyzer();
$reportGenerator = new ReportGenerator($reportsDir);

// Limpa arquivos antigos
$fileParser->cleanOldFiles($uploadDir, 24);
$reportGenerator->cleanOldReports(7);

// Variáveis para a view
$error = null;
$success = null;
$analysisResults = null;
$generatedReports = [];

// Processa o formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Verifica qual ação foi solicitada
    $action = $_POST['action'] ?? 'analyze';
    
    if ($action === 'analyze' && isset($_FILES['file'])) {
        // Upload e análise do arquivo
        $file = $_FILES['file'];
        
        // Valida o arquivo
        $validation = $fileParser->validateFile($file);
        
        if (!$validation['valid']) {
            $error = $validation['error'];
        } else {
            // Move o arquivo
            $moveResult = $fileParser->moveUploadedFile($file, $uploadDir);
            
            if (!$moveResult['success']) {
                $error = $moveResult['error'];
            } else {
                // Parse do arquivo
                $parseResult = $fileParser->parseFile($moveResult['path']);
                
                if (!$parseResult['success']) {
                    $error = $parseResult['error'];
                } else {
                    // Análise dos recursos
                    $analysisResults = $analyzer->analyzeResources($parseResult['data']);
                    $analysisResults['recommendations'] = $analyzer->generateRecommendations($analysisResults);
                    
                    // Armazena na sessão para geração de relatórios
                    session_start();
                    $_SESSION['analysisResults'] = $analysisResults;
                    $_SESSION['clientName'] = $_POST['clientName'] ?? null;
                    
                    $success = "Análise concluída! {$analysisResults['summary']['total']} recursos analisados.";
                }
                
                // Remove o arquivo temporário
                unlink($moveResult['path']);
            }
        }
    } elseif ($action === 'generate_pdf') {
        session_start();
        if (isset($_SESSION['analysisResults'])) {
            // Get custom notes from form
            $customNotes = [];
            if (isset($_POST['customNotes'])) {
                $customNotes = json_decode($_POST['customNotes'], true) ?? [];
            }
            
            $result = $reportGenerator->generatePDF(
                $_SESSION['analysisResults'],
                $_SESSION['clientName'] ?? null,
                $customNotes
            );
            
            if ($result['success']) {
                // Download do arquivo
                header('Content-Type: application/pdf');
                header('Content-Disposition: attachment; filename="' . $result['filename'] . '"');
                header('Content-Length: ' . filesize($result['path']));
                readfile($result['path']);
                exit;
            } else {
                $error = $result['error'];
            }
        } else {
            $error = 'Nenhuma análise disponível. Faça o upload de um arquivo primeiro.';
        }
        $analysisResults = $_SESSION['analysisResults'] ?? null;
        
    } elseif ($action === 'generate_excel') {
        session_start();
        if (isset($_SESSION['analysisResults'])) {
            // Get custom notes from form
            $customNotes = [];
            if (isset($_POST['customNotes'])) {
                $customNotes = json_decode($_POST['customNotes'], true) ?? [];
            }
            
            $result = $reportGenerator->generateExcel(
                $_SESSION['analysisResults'],
                $_SESSION['clientName'] ?? null,
                $customNotes
            );
            
            if ($result['success']) {
                // Download do arquivo
                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment; filename="' . $result['filename'] . '"');
                header('Content-Length: ' . filesize($result['path']));
                readfile($result['path']);
                exit;
            } else {
                $error = $result['error'];
            }
        } else {
            $error = 'Nenhuma análise disponível. Faça o upload de um arquivo primeiro.';
        }
        $analysisResults = $_SESSION['analysisResults'] ?? null;
    }
} else {
    session_start();
    $analysisResults = $_SESSION['analysisResults'] ?? null;
}

// Obtém metadados da base de dados
$dbMetadata = $analyzer->getDatabaseMetadata();
$dbStats = $analyzer->getDatabaseStats();

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Análise de Migração Azure</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --td-blue: #0097D7;
            --td-green: #82C341;
            --td-yellow: #FFD100;
            --td-red: #D9272E;
            --td-dark: #212529;
            --td-light: #f8f9fa;
            --td-gray: #6c757d;
        }
        
        body {
            background-color: #f4f6f9;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--td-dark);
            min-height: 100vh;
        }
        
        .navbar {
            background-color: #ffffff;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            padding: 1rem 0;
            border-top: 5px solid var(--td-blue);
        }

        .navbar-brand {
            color: var(--td-dark) !important;
            font-weight: 700;
            font-size: 1.5rem;
            letter-spacing: -0.5px;
        }

        .navbar-text {
            color: var(--td-gray) !important;
            font-size: 0.9rem;
        }
        
        .card {
            border: none;
            box-shadow: 0 0 20px rgba(0,0,0,0.03);
            border-radius: 8px;
            background: #fff;
            margin-bottom: 1.5rem;
        }
        
        .card-header {
            background: #fff;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            padding: 1.25rem 1.5rem;
            font-weight: 600;
            color: var(--td-dark);
            border-radius: 8px 8px 0 0 !important;
            display: flex;
            align-items: center;
        }

        .card-header i {
            color: var(--td-blue);
            font-size: 1.2rem;
        }
        
        .upload-zone {
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            padding: 50px 20px;
            text-align: center;
            transition: all 0.3s ease;
            background: #f8f9fa;
            cursor: pointer;
        }
        
        .upload-zone:hover, .upload-zone.dragover {
            border-color: var(--td-blue);
            background: rgba(0, 151, 215, 0.05);
        }
        
        .upload-zone i {
            font-size: 48px;
            color: var(--td-gray);
            transition: color 0.3s;
        }

        .upload-zone:hover i {
            color: var(--td-blue);
        }
        
        .stat-card {
            padding: 25px;
            border-radius: 8px;
            background: #fff;
            border-left: 5px solid transparent;
            box-shadow: 0 4px 15px rgba(0,0,0,0.03);
            height: 100%;
            transition: transform 0.2s;
        }
        
        .stat-card:hover {
            transform: translateY(-3px);
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            line-height: 1.2;
            margin-bottom: 5px;
        }
        
        .stat-label {
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--td-gray);
            font-weight: 600;
        }

        .stat-percent {
            font-size: 0.9rem;
            font-weight: 500;
            margin-top: 5px;
            display: block;
        }
        
        .stat-movable { border-left-color: var(--td-green); }
        .stat-movable .stat-number { color: var(--td-green); }
        
        .stat-restrictions { border-left-color: var(--td-yellow); }
        .stat-restrictions .stat-number { color: #e0a800; } /* Darker yellow for text */
        
        .stat-not-movable { border-left-color: var(--td-red); }
        .stat-not-movable .stat-number { color: var(--td-red); }
        
        .stat-total { border-left-color: var(--td-blue); }
        .stat-total .stat-number { color: var(--td-blue); }
        
        /* Filter Cards - Clickable */
        .filter-card {
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .filter-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.12);
        }
        
        .filter-card.active {
            box-shadow: 0 0 0 3px var(--td-blue), 0 4px 15px rgba(8,190,213,0.3);
            transform: translateY(-2px);
        }
        
        .filter-card::after {
            content: 'Clique para filtrar';
            position: absolute;
            bottom: 5px;
            right: 10px;
            font-size: 0.65rem;
            color: #aaa;
            opacity: 0;
            transition: opacity 0.2s;
        }
        
        .filter-card:hover::after {
            opacity: 1;
        }
        
        .filter-badge {
            display: none;
            position: fixed;
            top: 80px;
            right: 20px;
            background: var(--td-teal);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
            z-index: 1000;
            box-shadow: 0 3px 10px rgba(0,0,0,0.2);
            animation: slideIn 0.3s ease;
        }
        
        .filter-badge.show {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .filter-badge .clear-filter {
            background: rgba(255,255,255,0.2);
            border: none;
            color: white;
            padding: 2px 8px;
            border-radius: 10px;
            cursor: pointer;
            font-size: 0.75rem;
        }
        
        .filter-badge .clear-filter:hover {
            background: rgba(255,255,255,0.3);
        }
        
        @keyframes slideIn {
            from { transform: translateX(100px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        
        .status-badge {
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        
        .status-movable { background: rgba(130, 195, 65, 0.15); color: #5a8c2c; }
        .status-restrictions { background: rgba(255, 209, 0, 0.15); color: #b38600; }
        .status-not-movable { background: rgba(217, 39, 46, 0.15); color: #a31d22; }
        .status-unknown { background: #e9ecef; color: #495057; }
        
        .recommendation {
            padding: 20px;
            border-radius: 6px;
            margin-bottom: 15px;
            border-left: 4px solid;
            background: #fff;
            box-shadow: 0 2px 5px rgba(0,0,0,0.02);
        }
        
        .recommendation-success { border-color: var(--td-green); }
        .recommendation-warning { border-color: var(--td-yellow); }
        .recommendation-danger { border-color: var(--td-red); }
        .recommendation-info { border-color: var(--td-blue); }
        
        .table-results {
            font-size: 0.9rem;
        }
        
        .table-results thead th {
            background: #f8f9fa;
            color: var(--td-dark);
            font-weight: 600;
            border-bottom: 2px solid #dee2e6;
            padding: 15px;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 1px;
            cursor: pointer;
            user-select: none;
            position: relative;
            transition: background-color 0.2s;
        }
        
        .table-results thead th:hover {
            background: #e9ecef;
        }
        
        .table-results thead th .sort-icon {
            margin-left: 5px;
            opacity: 0.3;
            font-size: 0.7rem;
        }
        
        .table-results thead th.sort-asc .sort-icon,
        .table-results thead th.sort-desc .sort-icon {
            opacity: 1;
            color: var(--td-blue);
        }
        
        .table-results thead th.sort-asc .sort-icon::before {
            content: '\F148';
        }
        
        .table-results thead th.sort-desc .sort-icon::before {
            content: '\F128';
        }

        .table-results tbody td {
            padding: 15px;
            vertical-align: middle;
            border-bottom: 1px solid #f1f1f1;
        }
        
        .btn-azure {
            background: var(--td-blue);
            border-color: var(--td-blue);
            color: white;
            font-weight: 600;
            padding: 10px 20px;
            border-radius: 4px;
            transition: all 0.2s;
        }
        
        .btn-azure:hover {
            background: #007bb5;
            border-color: #007bb5;
            color: white;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0, 151, 215, 0.2);
        }

        .btn-azure:disabled {
            background: #a0d6ee;
            border-color: #a0d6ee;
        }

        .btn-outline-secondary {
            border-radius: 4px;
            font-weight: 500;
        }
        
        .footer {
            background: #fff;
            color: var(--td-gray);
            padding: 30px 0;
            margin-top: 60px;
            border-top: 1px solid #eee;
            font-size: 0.9rem;
        }

        .footer a {
            color: var(--td-blue);
            text-decoration: none;
        }

        .footer a:hover {
            text-decoration: underline;
        }
        
        .resource-type {
            font-family: 'Consolas', 'Monaco', monospace;
            font-size: 0.85rem;
            color: var(--td-blue);
        }
        
        .db-info {
            font-size: 0.8rem;
            color: var(--td-gray);
            margin-top: 15px;
        }
        
        .table-container {
            max-height: 600px;
            overflow-y: auto;
            scrollbar-width: thin;
        }

        /* Custom Notes Styles */
        .note-btn {
            background: none;
            border: none;
            padding: 4px 8px;
            cursor: pointer;
            color: #adb5bd;
            font-size: 1rem;
            transition: all 0.2s;
            border-radius: 4px;
        }

        .note-btn:hover {
            color: var(--td-blue);
            background: rgba(0, 151, 215, 0.1);
        }

        .note-btn.has-note {
            color: var(--td-blue);
            background: rgba(0, 151, 215, 0.1);
        }

        .note-btn.has-note::after {
            content: '';
            display: inline-block;
            width: 6px;
            height: 6px;
            background: var(--td-green);
            border-radius: 50%;
            margin-left: 3px;
            vertical-align: super;
        }

        .note-preview {
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            font-size: 0.8rem;
            color: var(--td-gray);
        }

        .note-modal-resource-info {
            background: #f8f9fa;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 15px;
        }

        .note-modal-resource-info h6 {
            color: var(--td-dark);
            margin-bottom: 10px;
            font-weight: 600;
        }

        .note-modal-resource-info small {
            color: var(--td-gray);
        }

        .note-counter {
            font-size: 0.75rem;
            color: var(--td-gray);
        }

        .clear-notes-btn {
            background: none;
            border: none;
            color: var(--td-gray);
            font-size: 0.8rem;
            cursor: pointer;
            padding: 4px 8px;
            border-radius: 4px;
            transition: all 0.2s;
        }

        .clear-notes-btn:hover {
            color: var(--td-red);
            background: rgba(217, 39, 46, 0.1);
        }

        .notes-count-badge {
            background: var(--td-blue);
            color: white;
            font-size: 0.7rem;
            padding: 2px 6px;
            border-radius: 10px;
            margin-left: 8px;
        }

        tr.has-custom-note {
            background: rgba(0, 151, 215, 0.03) !important;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light mb-5">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="bi bi-cloud-check-fill me-2" style="color: var(--td-blue);"></i>
                Azure Migration Analyzer
            </a>
            <span class="navbar-text">
                <i class="bi bi-database-check me-1"></i>
                Base atualizada: <?= htmlspecialchars($dbMetadata['lastUpdated'] ?? 'N/A') ?>
            </span>
        </div>
    </nav>

    <div class="container">
        <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            <?= htmlspecialchars($success) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <div class="row">
            <!-- Upload Section -->
            <div class="col-lg-4 mb-4">
                <div class="card">
                    <div class="card-header">
                        <i class="bi bi-upload me-2"></i>
                        Upload de Arquivo
                    </div>
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data" id="uploadForm">
                            <input type="hidden" name="action" value="analyze">
                            
                            <div class="mb-3">
                                <label for="clientName" class="form-label">Nome do Cliente (opcional)</label>
                                <input type="text" class="form-control" id="clientName" name="clientName" 
                                       placeholder="Ex: Empresa XYZ">
                            </div>
                            
                            <div class="upload-zone mb-3" id="dropZone" onclick="document.getElementById('fileInput').click()">
                                <i class="bi bi-file-earmark-spreadsheet"></i>
                                <p class="mt-3 mb-1">Arraste o arquivo aqui ou clique para selecionar</p>
                                <small class="text-muted">Formatos aceitos: .xlsx, .xls, .csv</small>
                                <input type="file" id="fileInput" name="file" accept=".xlsx,.xls,.csv" 
                                       class="d-none" onchange="handleFileSelect(this)">
                            </div>
                            
                            <div id="selectedFile" class="alert alert-info d-none">
                                <i class="bi bi-file-earmark me-2"></i>
                                <span id="fileName"></span>
                            </div>
                            
                            <button type="submit" class="btn btn-azure w-100" id="analyzeBtn" disabled>
                                <i class="bi bi-search me-2"></i>
                                Analisar Recursos
                            </button>
                        </form>

                        <hr>
                        
                        <h6 class="mb-3">
                            <i class="bi bi-info-circle me-1"></i>
                            Estrutura esperada do arquivo:
                        </h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Coluna</th>
                                        <th>Obrigatório</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><code>Resource Type</code></td>
                                        <td><span class="badge bg-danger">Sim</span></td>
                                    </tr>
                                    <tr>
                                        <td><code>Resource Name</code></td>
                                        <td><span class="badge bg-secondary">Não</span></td>
                                    </tr>
                                    <tr>
                                        <td><code>Resource Group</code></td>
                                        <td><span class="badge bg-secondary">Não</span></td>
                                    </tr>
                                    <tr>
                                        <td><code>Location</code></td>
                                        <td><span class="badge bg-secondary">Não</span></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="db-info mt-3">
                            <i class="bi bi-database me-1"></i>
                            A base possui <?= number_format($dbStats['total']) ?> tipos de recursos catalogados.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Results Section -->
            <div class="col-lg-8">
                <?php if ($analysisResults): ?>
                    <?php $summary = $analysisResults['summary']; ?>
                    
                    <!-- Summary Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3 mb-3">
                            <div class="stat-card stat-movable filter-card" data-filter="movable" onclick="filterByStatus('movable')" title="Clique para filtrar">
                                <div class="stat-number"><?= $summary['movable'] ?></div>
                                <div class="stat-label">Migráveis</div>
                                <span class="stat-percent text-success">
                                    <i class="bi bi-arrow-up-short"></i><?= $summary['movablePercent'] ?>%
                                </span>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="stat-card stat-restrictions filter-card" data-filter="restrictions" onclick="filterByStatus('restrictions')" title="Clique para filtrar">
                                <div class="stat-number"><?= $summary['movableWithRestrictions'] ?></div>
                                <div class="stat-label">Com Restrições</div>
                                <span class="stat-percent text-warning">
                                    <i class="bi bi-exclamation"></i><?= $summary['movableWithRestrictionsPercent'] ?>%
                                </span>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="stat-card stat-not-movable filter-card" data-filter="not-movable" onclick="filterByStatus('not-movable')" title="Clique para filtrar">
                                <div class="stat-number"><?= $summary['notMovable'] ?></div>
                                <div class="stat-label">Não Migráveis</div>
                                <span class="stat-percent text-danger">
                                    <i class="bi bi-x"></i><?= $summary['notMovablePercent'] ?>%
                                </span>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="stat-card stat-total filter-card" data-filter="all" onclick="filterByStatus('all')" title="Clique para mostrar todos">
                                <div class="stat-number"><?= $summary['total'] ?></div>
                                <div class="stat-label">Total</div>
                                <span class="stat-percent text-primary">
                                    <i class="bi bi-layers"></i> Recursos
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Filter Badge (floating indicator) -->
                    <div id="filterBadge" class="filter-badge">
                        <i class="bi bi-funnel-fill"></i>
                        <span id="filterText">Filtrando por: </span>
                        <button class="clear-filter" onclick="filterByStatus('all')">
                            <i class="bi bi-x"></i> Limpar
                        </button>
                    </div>

                    <!-- Export Buttons -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <h5 class="mb-0" style="font-weight: 600; color: var(--td-dark);">
                                        <i class="bi bi-file-earmark-arrow-down me-2" style="color: var(--td-blue);"></i>
                                        Exportar Relatório Executivo
                                    </h5>
                                </div>
                                <div class="col-md-6 text-md-end mt-3 mt-md-0">
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="action" value="generate_pdf">
                                        <button type="submit" class="btn btn-outline-danger me-2">
                                            <i class="bi bi-file-earmark-pdf me-1"></i>
                                            PDF
                                        </button>
                                    </form>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="action" value="generate_excel">
                                        <button type="submit" class="btn btn-outline-success">
                                            <i class="bi bi-file-earmark-excel me-1"></i>
                                            Excel
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recommendations -->
                    <?php if (!empty($analysisResults['recommendations'])): ?>
                    <div class="card mb-4">
                        <div class="card-header">
                            <i class="bi bi-lightbulb me-2"></i>
                            Recomendações
                        </div>
                        <div class="card-body">
                            <?php foreach ($analysisResults['recommendations'] as $rec): ?>
                            <div class="recommendation recommendation-<?= htmlspecialchars($rec['type']) ?>">
                                <strong><?= htmlspecialchars($rec['title']) ?></strong>
                                <p class="mb-0 mt-1"><?= htmlspecialchars($rec['message']) ?></p>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Detailed Results -->
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span>
                                <i class="bi bi-list-ul me-2"></i>
                                Análise Detalhada
                            </span>
                            <div class="d-flex align-items-center gap-2">
                                <button class="clear-notes-btn" id="clearAllNotesBtn" onclick="openClearAllNotesModal()" 
                                        style="display: none;" title="Limpar todas as notas">
                                    <i class="bi bi-trash me-1"></i>Limpar notas
                                </button>
                                <input type="text" class="form-control form-control-sm" style="width: 200px;"
                                       id="searchInput" placeholder="Filtrar recursos...">
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-container">
                                <table class="table table-hover table-results mb-0" id="resultsTable">
                                    <thead>
                                        <tr>
                                            <th data-sort="name" onclick="sortTable(0, 'string')">Nome <i class="bi bi-arrow-down-up sort-icon"></i></th>
                                            <th data-sort="type" onclick="sortTable(1, 'string')">Tipo do Recurso <i class="bi bi-arrow-down-up sort-icon"></i></th>
                                            <th data-sort="group" onclick="sortTable(2, 'string')">Resource Group <i class="bi bi-arrow-down-up sort-icon"></i></th>
                                            <th data-sort="status" onclick="sortTable(3, 'status')">Status <i class="bi bi-arrow-down-up sort-icon"></i></th>
                                            <th>Observações</th>
                                            <th style="width: 120px; text-align: center;">
                                                <span>Notas</span>
                                                <span id="notesCountBadge" class="notes-count-badge" style="display: none;">0</span>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($analysisResults['results'] as $index => $result): ?>
                                        <?php
                                        $dataStatus = match($result['status']) {
                                            'movable' => 'movable',
                                            'movable-with-restrictions' => 'restrictions',
                                            'not-movable' => 'not-movable',
                                            default => 'unknown'
                                        };
                                        // Create unique ID for the resource
                                        $resourceId = base64_encode($result['resourceName'] . '|' . $result['resourceType'] . '|' . $result['resourceGroup']);
                                        ?>
                                        <tr data-status="<?= $dataStatus ?>" data-resource-id="<?= htmlspecialchars($resourceId) ?>" 
                                            data-resource-name="<?= htmlspecialchars($result['resourceName']) ?>"
                                            data-resource-type="<?= htmlspecialchars($result['resourceType']) ?>"
                                            data-resource-group="<?= htmlspecialchars($result['resourceGroup']) ?>">
                                            <td><?= htmlspecialchars($result['resourceName']) ?></td>
                                            <td class="resource-type"><?= htmlspecialchars($result['resourceType']) ?></td>
                                            <td><?= htmlspecialchars($result['resourceGroup']) ?></td>
                                            <td>
                                                <?php
                                                $statusClass = match($result['status']) {
                                                    'movable' => 'status-movable',
                                                    'movable-with-restrictions' => 'status-restrictions',
                                                    'not-movable' => 'status-not-movable',
                                                    default => 'status-unknown'
                                                };
                                                ?>
                                                <span class="status-badge <?= $statusClass ?>">
                                                    <?= htmlspecialchars($result['statusLabel']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($result['notes']): ?>
                                                <small class="text-muted"><?= htmlspecialchars($result['notes']) ?></small>
                                                <?php else: ?>
                                                <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <button class="note-btn" onclick="openNoteModal('<?= htmlspecialchars($resourceId) ?>')" 
                                                        title="Adicionar nota personalizada" data-note-btn="<?= htmlspecialchars($resourceId) ?>">
                                                    <i class="bi bi-pencil-square"></i>
                                                </button>
                                                <div class="note-preview" data-note-preview="<?= htmlspecialchars($resourceId) ?>"></div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                <?php else: ?>
                    <!-- Empty State -->
                    <div class="card h-100 d-flex align-items-center justify-content-center">
                        <div class="card-body text-center py-5">
                            <div class="mb-4">
                                <i class="bi bi-cloud-upload display-1" style="color: #e9ecef;"></i>
                            </div>
                            <h4 class="mt-2" style="font-weight: 600; color: var(--td-dark);">Nenhuma análise realizada</h4>
                            <p class="text-muted mb-4" style="max-width: 400px; margin: 0 auto;">
                                Faça o upload de um arquivo Excel ou CSV contendo a lista de recursos Azure para iniciar a análise de viabilidade.
                            </p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Note Modal -->
    <div class="modal fade" id="noteModal" tabindex="-1" aria-labelledby="noteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="noteModalLabel">
                        <i class="bi bi-pencil-square me-2" style="color: var(--td-blue);"></i>
                        Nota Personalizada
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <div class="note-modal-resource-info">
                        <h6><i class="bi bi-box me-2"></i>Recurso</h6>
                        <p class="mb-1"><strong id="noteModalResourceName"></strong></p>
                        <small id="noteModalResourceType" class="d-block"></small>
                        <small id="noteModalResourceGroup"></small>
                    </div>
                    <div class="mb-3">
                        <label for="noteTextarea" class="form-label fw-semibold">Sua nota:</label>
                        <textarea class="form-control" id="noteTextarea" rows="4" 
                                  placeholder="Digite aqui observações, lembretes ou informações adicionais sobre este recurso..."></textarea>
                        <div class="d-flex justify-content-between mt-2">
                            <span class="note-counter"><span id="noteCharCount">0</span>/500 caracteres</span>
                            <button type="button" class="clear-notes-btn" id="clearSingleNote" onclick="clearCurrentNote()">
                                <i class="bi bi-trash me-1"></i>Limpar nota
                            </button>
                        </div>
                    </div>
                    <input type="hidden" id="currentResourceId" value="">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-azure" onclick="saveNote()">
                        <i class="bi bi-check2 me-1"></i>Salvar Nota
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Clear All Notes Confirmation Modal -->
    <div class="modal fade" id="clearAllNotesModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title">
                        <i class="bi bi-exclamation-triangle me-2 text-warning"></i>Confirmar
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">Tem certeza que deseja remover <strong>todas</strong> as notas personalizadas?</p>
                    <small class="text-muted">Esta ação não pode ser desfeita.</small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-sm btn-danger" onclick="confirmClearAllNotes()">
                        <i class="bi bi-trash me-1"></i>Limpar Todas
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h6 style="color: var(--td-dark); font-weight: 700;"><i class="bi bi-shield-check me-2" style="color: var(--td-blue);"></i>Azure Migration Analyzer</h6>
                    <p class="mb-0 small text-muted">
                        Ferramenta de análise técnica para migração de recursos Azure.<br>
                        Baseado na documentação oficial da Microsoft.
                    </p>
                </div>
                <div class="col-md-6 text-md-end">
                    <h6 style="color: var(--td-dark); font-weight: 600;"><i class="bi bi-link-45deg me-2"></i>Referência</h6>
                    <a href="https://learn.microsoft.com/en-us/azure/azure-resource-manager/management/move-support-resources" 
                       target="_blank" class="small">
                        Documentação Microsoft - Move Support Resources
                    </a>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Drag and drop functionality
        const dropZone = document.getElementById('dropZone');
        const fileInput = document.getElementById('fileInput');
        const analyzeBtn = document.getElementById('analyzeBtn');
        const selectedFile = document.getElementById('selectedFile');
        const fileName = document.getElementById('fileName');

        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, () => dropZone.classList.add('dragover'), false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, () => dropZone.classList.remove('dragover'), false);
        });

        dropZone.addEventListener('drop', handleDrop, false);

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            fileInput.files = files;
            handleFileSelect(fileInput);
        }

        function handleFileSelect(input) {
            if (input.files.length > 0) {
                const file = input.files[0];
                fileName.textContent = file.name;
                selectedFile.classList.remove('d-none');
                analyzeBtn.disabled = false;
            }
        }

        // Table filter
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.addEventListener('keyup', function() {
                const filter = this.value.toLowerCase();
                const rows = document.querySelectorAll('#resultsTable tbody tr');
                
                rows.forEach(row => {
                    // Check if row is hidden by status filter
                    if (row.dataset.statusHidden === 'true') return;
                    
                    const text = row.textContent.toLowerCase();
                    row.style.display = text.includes(filter) ? '' : 'none';
                });
            });
        }

        // Filter by status functionality
        let currentStatusFilter = 'all';

        function filterByStatus(status) {
            const rows = document.querySelectorAll('#resultsTable tbody tr');
            const filterBadge = document.getElementById('filterBadge');
            const filterText = document.getElementById('filterText');
            const filterCards = document.querySelectorAll('.filter-card');
            
            currentStatusFilter = status;
            
            // Update active card
            filterCards.forEach(card => {
                card.classList.remove('active');
                if (card.dataset.filter === status) {
                    card.classList.add('active');
                }
            });
            
            // Filter rows
            let visibleCount = 0;
            rows.forEach(row => {
                if (status === 'all') {
                    row.style.display = '';
                    row.dataset.statusHidden = 'false';
                    visibleCount++;
                } else {
                    const rowStatus = row.dataset.status;
                    if (rowStatus === status) {
                        row.style.display = '';
                        row.dataset.statusHidden = 'false';
                        visibleCount++;
                    } else {
                        row.style.display = 'none';
                        row.dataset.statusHidden = 'true';
                    }
                }
            });
            
            // Update filter badge
            if (status === 'all') {
                filterBadge.classList.remove('show');
            } else {
                const statusLabels = {
                    'movable': 'Migráveis',
                    'restrictions': 'Com Restrições',
                    'not-movable': 'Não Migráveis',
                    'unknown': 'Desconhecidos'
                };
                filterText.innerHTML = `<strong>${statusLabels[status]}</strong> (${visibleCount} recursos)`;
                filterBadge.classList.add('show');
            }
            
            // Clear search input when filtering
            if (searchInput) {
                searchInput.value = '';
            }
        }

        // Table sorting functionality
        let currentSortColumn = -1;
        let currentSortDirection = 'none';

        function sortTable(columnIndex, type) {
            const table = document.getElementById('resultsTable');
            const tbody = table.querySelector('tbody');
            const rows = Array.from(tbody.querySelectorAll('tr'));
            const headers = table.querySelectorAll('thead th');

            // Determine sort direction
            if (currentSortColumn === columnIndex) {
                if (currentSortDirection === 'asc') {
                    currentSortDirection = 'desc';
                } else if (currentSortDirection === 'desc') {
                    currentSortDirection = 'none';
                } else {
                    currentSortDirection = 'asc';
                }
            } else {
                currentSortColumn = columnIndex;
                currentSortDirection = 'asc';
            }

            // Update header classes
            headers.forEach((th, i) => {
                th.classList.remove('sort-asc', 'sort-desc');
                if (i === columnIndex && currentSortDirection !== 'none') {
                    th.classList.add('sort-' + currentSortDirection);
                }
            });

            // Sort rows
            if (currentSortDirection === 'none') {
                // Reset to original order - reload page or use data attribute
                location.reload();
                return;
            }

            const statusOrder = {
                'movable': 1,
                'migrável': 1,
                'restrictions': 2,
                'restrições': 2,
                'not-movable': 3,
                'não migrável': 3,
                'unknown': 4,
                'desconhecido': 4
            };

            rows.sort((a, b) => {
                let aValue = a.cells[columnIndex].textContent.trim().toLowerCase();
                let bValue = b.cells[columnIndex].textContent.trim().toLowerCase();

                if (type === 'status') {
                    // Find status order by checking which keyword is present
                    let aOrder = 4, bOrder = 4;
                    for (const [key, order] of Object.entries(statusOrder)) {
                        if (aValue.includes(key)) aOrder = order;
                        if (bValue.includes(key)) bOrder = order;
                    }
                    aValue = aOrder;
                    bValue = bOrder;
                }

                let comparison = 0;
                if (typeof aValue === 'number' && typeof bValue === 'number') {
                    comparison = aValue - bValue;
                } else {
                    comparison = aValue.localeCompare(bValue, 'pt-BR');
                }

                return currentSortDirection === 'asc' ? comparison : -comparison;
            });

            // Re-append sorted rows
            rows.forEach(row => tbody.appendChild(row));
        }

        // ============================================
        // Custom Notes Management
        // ============================================
        const NOTES_STORAGE_KEY = 'azure_migration_notes';
        let noteModal = null;
        let clearAllNotesModal = null;

        // Initialize notes system on page load
        document.addEventListener('DOMContentLoaded', function() {
            noteModal = new bootstrap.Modal(document.getElementById('noteModal'));
            clearAllNotesModal = new bootstrap.Modal(document.getElementById('clearAllNotesModal'));
            
            // Load and display saved notes
            loadAndDisplayNotes();
            
            // Character counter for textarea
            const noteTextarea = document.getElementById('noteTextarea');
            if (noteTextarea) {
                noteTextarea.addEventListener('input', function() {
                    const count = this.value.length;
                    document.getElementById('noteCharCount').textContent = count;
                    if (count > 500) {
                        this.value = this.value.substring(0, 500);
                        document.getElementById('noteCharCount').textContent = 500;
                    }
                });
            }
        });

        // Get all notes from localStorage
        function getNotes() {
            try {
                const notes = localStorage.getItem(NOTES_STORAGE_KEY);
                return notes ? JSON.parse(notes) : {};
            } catch (e) {
                console.error('Error loading notes:', e);
                return {};
            }
        }

        // Save notes to localStorage
        function setNotes(notes) {
            try {
                localStorage.setItem(NOTES_STORAGE_KEY, JSON.stringify(notes));
            } catch (e) {
                console.error('Error saving notes:', e);
            }
        }

        // Load and display all saved notes
        function loadAndDisplayNotes() {
            const notes = getNotes();
            let notesCount = 0;

            // Update each row with its note
            document.querySelectorAll('#resultsTable tbody tr').forEach(row => {
                const resourceId = row.dataset.resourceId;
                const noteBtn = row.querySelector(`[data-note-btn="${resourceId}"]`);
                const notePreview = row.querySelector(`[data-note-preview="${resourceId}"]`);
                
                if (notes[resourceId]) {
                    notesCount++;
                    row.classList.add('has-custom-note');
                    if (noteBtn) noteBtn.classList.add('has-note');
                    if (notePreview) notePreview.textContent = notes[resourceId];
                } else {
                    row.classList.remove('has-custom-note');
                    if (noteBtn) noteBtn.classList.remove('has-note');
                    if (notePreview) notePreview.textContent = '';
                }
            });

            // Update notes count badge
            const countBadge = document.getElementById('notesCountBadge');
            const clearAllBtn = document.getElementById('clearAllNotesBtn');
            
            if (notesCount > 0) {
                countBadge.textContent = notesCount;
                countBadge.style.display = 'inline';
                clearAllBtn.style.display = 'inline-block';
            } else {
                countBadge.style.display = 'none';
                clearAllBtn.style.display = 'none';
            }
        }

        // Open note modal for a specific resource
        function openNoteModal(resourceId) {
            const row = document.querySelector(`tr[data-resource-id="${resourceId}"]`);
            if (!row) return;

            // Fill modal with resource info
            document.getElementById('noteModalResourceName').textContent = row.dataset.resourceName;
            document.getElementById('noteModalResourceType').textContent = row.dataset.resourceType;
            document.getElementById('noteModalResourceGroup').textContent = 'Resource Group: ' + row.dataset.resourceGroup;
            document.getElementById('currentResourceId').value = resourceId;

            // Load existing note
            const notes = getNotes();
            const existingNote = notes[resourceId] || '';
            document.getElementById('noteTextarea').value = existingNote;
            document.getElementById('noteCharCount').textContent = existingNote.length;

            noteModal.show();
        }

        // Save the current note
        function saveNote() {
            const resourceId = document.getElementById('currentResourceId').value;
            const noteText = document.getElementById('noteTextarea').value.trim();
            
            const notes = getNotes();
            
            if (noteText) {
                notes[resourceId] = noteText;
            } else {
                delete notes[resourceId];
            }
            
            setNotes(notes);
            loadAndDisplayNotes();
            noteModal.hide();
            
            // Show toast notification
            showToast(noteText ? 'Nota salva com sucesso!' : 'Nota removida.');
        }

        // Clear the current note in modal
        function clearCurrentNote() {
            document.getElementById('noteTextarea').value = '';
            document.getElementById('noteCharCount').textContent = '0';
        }

        // Open clear all notes confirmation modal
        function openClearAllNotesModal() {
            clearAllNotesModal.show();
        }

        // Confirm and clear all notes
        function confirmClearAllNotes() {
            localStorage.removeItem(NOTES_STORAGE_KEY);
            loadAndDisplayNotes();
            clearAllNotesModal.hide();
            showToast('Todas as notas foram removidas.');
        }

        // Get notes for export (called from PHP)
        function getNotesForExport() {
            return getNotes();
        }

        // Simple toast notification
        function showToast(message) {
            // Create toast element
            const toast = document.createElement('div');
            toast.style.cssText = `
                position: fixed;
                bottom: 20px;
                right: 20px;
                background: #333;
                color: white;
                padding: 12px 24px;
                border-radius: 6px;
                font-size: 0.9rem;
                z-index: 9999;
                animation: slideIn 0.3s ease;
                box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            `;
            toast.innerHTML = `<i class="bi bi-check-circle me-2"></i>${message}`;
            document.body.appendChild(toast);
            
            // Remove after 3 seconds
            setTimeout(() => {
                toast.style.animation = 'slideIn 0.3s ease reverse';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        // Inject notes into export form before submission
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function(e) {
                const action = this.querySelector('input[name="action"]')?.value;
                if (action === 'generate_pdf' || action === 'generate_excel') {
                    // Add notes as hidden input
                    let notesInput = this.querySelector('input[name="customNotes"]');
                    if (!notesInput) {
                        notesInput = document.createElement('input');
                        notesInput.type = 'hidden';
                        notesInput.name = 'customNotes';
                        this.appendChild(notesInput);
                    }
                    notesInput.value = JSON.stringify(getNotes());
                }
            });
        });
    </script>
</body>
</html>
