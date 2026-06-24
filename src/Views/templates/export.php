<?php
/**
 * Template d'export générique — Impression HTML autonome
 *
 * Variables attendues :
 *   $exportTitle   (string)   — Titre principal du document
 *   $exportSubtitle (string)  — Sous-titre / description courte
 *   $exportColumns (array)    — Tableau des en-têtes de colonnes
 *   $exportRows    (array)    — Tableau de tableaux de valeurs (une ligne = un tableau)
 *
 * Ce template se décompose en une page HTML complète, indépendante du layout principal.
 * Il charge les informations de l'établissement depuis SettingsStore et LogoManager.
 */

use App\Core\Database;
use App\Services\SettingsStore;
use App\Core\LogoManager;

// --- Chargement des données de l'établissement ---
$db          = Database::getInstance()->getConnection();
$settingsStore = new SettingsStore($db);
$logoManager   = LogoManager::getInstance($db);

$school_name     = $settingsStore->get('school_name', 'Établissement Scolaire');
$school_code     = trim((string) $settingsStore->get('school_code', ''));
$school_phone    = $settingsStore->get('school_phone', '');
$school_email    = $settingsStore->get('school_email', '');
$school_address  = $settingsStore->get('school_address', '');
$school_identity = $school_code !== '' ? $school_code : $school_name;

$logoBase64 = $logoManager->hasLogo() ? $logoManager->getLogoBase64() : '';
$fallbackLetter = $logoManager->getFallbackLetter();

// --- Sécurité : s'assurer que les variables d'export existent ---
$exportTitle    = $exportTitle    ?? 'Export';
$exportSubtitle = $exportSubtitle ?? '';
$exportColumns  = $exportColumns  ?? [];
$exportRows     = $exportRows     ?? [];

// --- Envoi des headers HTTP ---
header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($exportTitle, ENT_QUOTES, 'UTF-8') ?> — <?= htmlspecialchars($school_name, ENT_QUOTES, 'UTF-8') ?></title>
    <style>
        /* === RESET & BASE === */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 11pt;
            color: #1a1a2e;
            background: #f0f4f8;
            padding: 20px;
        }

        /* === DOCUMENT WRAPPER === */
        .doc-wrapper {
            max-width: 960px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 24px rgba(0,0,0,.12);
            overflow: hidden;
        }

        /* === EN-TÊTE ÉTABLISSEMENT === */
        .doc-header {
            background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 100%);
            color: #ffffff;
            padding: 24px 32px;
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .logo-circle {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            border: 3px solid rgba(255,255,255,.4);
            overflow: hidden;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255,255,255,.15);
            font-size: 28px;
            font-weight: 900;
            color: #fff;
            letter-spacing: -1px;
        }

        .logo-circle img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .school-info { flex: 1; }
        .school-name {
            font-size: 17pt;
            font-weight: 800;
            letter-spacing: -0.5px;
            line-height: 1.1;
        }

        .school-meta {
            margin-top: 6px;
            font-size: 8.5pt;
            opacity: .82;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .school-meta span { display: flex; align-items: center; gap: 4px; }

        .export-badge {
            background: rgba(255,255,255,.18);
            border: 1px solid rgba(255,255,255,.3);
            border-radius: 8px;
            padding: 10px 18px;
            text-align: center;
            flex-shrink: 0;
        }
        .export-badge .badge-label { font-size: 7.5pt; opacity: .75; text-transform: uppercase; letter-spacing: 1px; }
        .export-badge .badge-date  { font-size: 10pt; font-weight: 700; margin-top: 2px; }

        /* === TITRE DU DOCUMENT === */
        .doc-title-bar {
            padding: 18px 32px 14px;
            border-bottom: 2px solid #e5e9f0;
        }
        .doc-title     { font-size: 15pt; font-weight: 800; color: #1e3a5f; }
        .doc-subtitle  { font-size: 9.5pt; color: #64748b; margin-top: 3px; }

        .doc-stats {
            display: flex;
            gap: 16px;
            margin-top: 10px;
            flex-wrap: wrap;
        }
        .stat-pill {
            background: #f1f5fb;
            border: 1px solid #dde3ee;
            border-radius: 20px;
            padding: 4px 14px;
            font-size: 8.5pt;
            color: #475569;
            font-weight: 600;
        }
        .stat-pill span { color: #2563eb; font-weight: 800; }

        /* === TABLEAU === */
        .doc-table-wrapper {
            padding: 20px 32px 28px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10pt;
        }

        thead th {
            background: #1e3a5f;
            color: #ffffff;
            font-weight: 700;
            padding: 10px 14px;
            text-align: left;
            font-size: 9pt;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: none;
        }

        thead th:first-child { border-radius: 6px 0 0 0; }
        thead th:last-child  { border-radius: 0 6px 0 0; }

        tbody tr { border-bottom: 1px solid #e5e9f0; }
        tbody tr:last-child { border-bottom: none; }
        tbody tr:nth-child(even) { background: #f8fafc; }
        tbody tr:hover { background: #eff6ff; }

        tbody td {
            padding: 9px 14px;
            color: #334155;
            vertical-align: middle;
        }

        tbody td:first-child { font-weight: 600; color: #1e3a5f; }

        .no-data {
            text-align: center;
            padding: 40px;
            color: #94a3b8;
            font-style: italic;
        }

        /* === PIED DE PAGE === */
        .doc-footer {
            background: #f8fafc;
            border-top: 1px solid #e5e9f0;
            padding: 12px 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 8pt;
            color: #94a3b8;
        }

        /* === BOUTON IMPRESSION (masqué à l'impression) === */
        .print-actions {
            padding: 16px 32px;
            border-top: 1px solid #e5e9f0;
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }

        .btn-print {
            background: linear-gradient(135deg, #1e3a5f, #2563eb);
            color: #fff;
            border: none;
            border-radius: 20px;
            padding: 9px 22px;
            font-size: 10pt;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 7px;
            transition: opacity .2s;
        }

        .btn-print:hover { opacity: .88; }

        .btn-back {
            background: #f1f5fb;
            color: #475569;
            border: 1px solid #dde3ee;
            border-radius: 20px;
            padding: 9px 22px;
            font-size: 10pt;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 7px;
            transition: background .2s;
        }

        .btn-back:hover { background: #e2e8f0; }

        /* === IMPRESSION === */
        @media print {
            body { background: #fff; padding: 0; font-size: 10pt; }

            .doc-wrapper {
                max-width: 100%;
                border-radius: 0;
                box-shadow: none;
            }

            .print-actions { display: none !important; }

            thead th { background: #1e3a5f !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            tbody tr:nth-child(even) { background: #f8fafc !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }

            .doc-header {
                background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 100%) !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            table { page-break-inside: auto; }
            tr    { page-break-inside: avoid; page-break-after: auto; }
        }
    </style>
</head>
<body>

<div class="doc-wrapper">

    <!-- En-tête établissement -->
    <div class="doc-header">
        <div class="logo-circle">
            <?php if ($logoBase64): ?>
                <img src="<?= $logoBase64 ?>" alt="Logo">
            <?php else: ?>
                <?= htmlspecialchars($fallbackLetter, ENT_QUOTES, 'UTF-8') ?>
            <?php endif; ?>
        </div>

        <div class="school-info">
            <div class="school-name"><?= htmlspecialchars($school_name, ENT_QUOTES, 'UTF-8') ?></div>
            <div class="school-meta">
                <?php if ($school_address): ?>
                    <span>📍 <?= htmlspecialchars($school_address, ENT_QUOTES, 'UTF-8') ?></span>
                <?php endif; ?>
                <?php if ($school_phone): ?>
                    <span>📞 <?= htmlspecialchars($school_phone, ENT_QUOTES, 'UTF-8') ?></span>
                <?php endif; ?>
                <?php if ($school_email): ?>
                    <span>✉ <?= htmlspecialchars($school_email, ENT_QUOTES, 'UTF-8') ?></span>
                <?php endif; ?>
            </div>
        </div>

        <div class="export-badge">
            <div class="badge-label">Date d'export</div>
            <div class="badge-date"><?= date('d/m/Y') ?></div>
        </div>
    </div>

    <!-- Titre du document -->
    <div class="doc-title-bar">
        <div class="doc-title"><?= htmlspecialchars($exportTitle, ENT_QUOTES, 'UTF-8') ?></div>
        <?php if ($exportSubtitle): ?>
            <div class="doc-subtitle"><?= htmlspecialchars($exportSubtitle, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <div class="doc-stats">
            <div class="stat-pill">Total : <span><?= count($exportRows) ?></span> enregistrement<?= count($exportRows) > 1 ? 's' : '' ?></div>
            <div class="stat-pill">Généré le : <span><?= date('d/m/Y à H:i') ?></span></div>
        </div>
    </div>

    <!-- Boutons d'action -->
    <div class="print-actions">
        <a href="javascript:history.back()" class="btn-back">
            ← Retour
        </a>
        <button class="btn-print" onclick="window.print()">
            🖨 Imprimer / Exporter PDF
        </button>
    </div>

    <!-- Tableau de données -->
    <div class="doc-table-wrapper">
        <table>
            <?php if (!empty($exportColumns)): ?>
            <thead>
                <tr>
                    <th style="width:40px;">#</th>
                    <?php foreach ($exportColumns as $col): ?>
                        <th><?= htmlspecialchars((string)$col, ENT_QUOTES, 'UTF-8') ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <?php endif; ?>
            <tbody>
                <?php if (empty($exportRows)): ?>
                    <tr>
                        <td colspan="<?= count($exportColumns) + 1 ?>" class="no-data">
                            Aucune donnée à afficher pour cet export.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($exportRows as $i => $row): ?>
                        <tr>
                            <td style="color:#94a3b8; font-weight:400; font-size:8.5pt;"><?= $i + 1 ?></td>
                            <?php foreach ((array)$row as $cell): ?>
                                <td><?= htmlspecialchars((string)$cell, ENT_QUOTES, 'UTF-8') ?></td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pied de page -->
    <div class="doc-footer">
        <span><?= htmlspecialchars($school_name, ENT_QUOTES, 'UTF-8') ?> — Document généré automatiquement</span>
        <span><?= date('d/m/Y à H:i:s') ?></span>
    </div>

</div>

<script>
    // Déclenchement automatique de l'impression si paramètre ?autoprint=1
    if (new URLSearchParams(window.location.search).get('autoprint') === '1') {
        window.addEventListener('load', function() { window.print(); });
    }
</script>

</body>
</html>
