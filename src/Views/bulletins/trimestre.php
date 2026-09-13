<?php
/**
 * 1. INITIALISATION DES DONNÉES
 * Variables transmises par le BulletinController.
 */
$embeddedBatch = $embeddedBatch ?? false;
$institution = $institution ?? [];
$activeYear = $activeYear ?? [];
$student = $student ?? [];
$rows = $rows ?? [];
$groupedRows = $groupedRows ?? [];
$classStats = $classStats ?? [];
$discipline = $discipline ?? [];
$rank = $rank ?? null;
$effectif = $effectif ?? 0;
$average = $average ?? null;
$mention = $mention ?? '';
$total_coefficients = $total_coefficients ?? 0;
$total_coef_valide = $total_coef_valide ?? 0;
$term = $term ?? 1;                           // Numéro du trimestre (1, 2 ou 3)
$evaluationLabels = $evaluationLabels ?? ['S1', 'S2', 'Trim']; // Libellés des colonnes (Séquences + Trimestre)
$seqAverages = $seqAverages ?? [];             // Moyennes séquentielles pour le rappel
$seqRanks = $seqRanks ?? [];                // Rangs séquentiels pour le rappel
$globalAppreciation = $globalAppreciation ?? '-';

$lang = \App\Core\Session::get('app_lang', 'fr');
$i = $institution;
$contactLabels = [
    'bp' => __('bp'),
    'tel' => __('tel'),
    'fax' => __('fax'),
    'email' => __('email'),
    'web' => __('web')
];
$contact = trim($contactLabels['tel'] . ': ' . ($i['school_phone'] ?? '') . ' | ' . ($i['school_city'] ?? ''));
$studentLastName = function_exists('mb_strtoupper') ? mb_strtoupper((string) ($student['nom'] ?? ''), 'UTF-8') : strtoupper((string) ($student['nom'] ?? ''));
$schoolDisplayName = function_exists('mb_strtoupper') ? mb_strtoupper((string) ($i['school_name'] ?? $i['school_code'] ?? ''), 'UTF-8') : strtoupper((string) ($i['school_name'] ?? $i['school_code'] ?? ''));
$schoolCodeWatermark = function_exists('mb_strtoupper') ? mb_strtoupper(trim((string) ($i['school_code'] ?? '')), 'UTF-8') : strtoupper(trim((string) ($i['school_code'] ?? '')));
$birthDate = $student['date_naissance'] ?? null;
$birthPlace = trim((string) ($student['lieu_naissance'] ?? ''));
$birthPlace = $birthPlace !== '' ? $birthPlace : '-';
$isRedoublant = !empty($student['is_redoublant']);

/**
 * 2. LOGIQUE DE TAILLE DE POLICE DYNAMIQUE
 */
$subjectCount = count($rows);
$baseFontSize = 14;
$pageMargin = '0.5cm';
$pageContentHeight = '287mm';
$lineHeight = 1.3;
$logoSize = '95px';

if ($subjectCount >= 10 && $subjectCount <= 12) {
    $baseFontSize = 12;
    $pageMargin = '0.3cm';
    $pageContentHeight = '291mm';
    $lineHeight = 1.2;
} elseif ($subjectCount >= 13 && $subjectCount <= 15) {
    $baseFontSize = 11;
    $pageMargin = '0.25cm';
    $pageContentHeight = '292mm';
    $lineHeight = 1.15;
} elseif ($subjectCount > 15) {
    $baseFontSize = 10;
    $pageMargin = '0.2cm';
    $pageContentHeight = '293mm';
    $lineHeight = 1.1;
    $logoSize = '80px';
}

/**
 * 3. FONCTIONS D'AFFICHAGE (HELPERS)
 */
if (!function_exists('formatNote')) {
    function formatNote($val)
    {
        if ($val === null || $val === '-') {
            return '-';
        }
        $n = (float) $val;
        $fmt = number_format($n, 2, ',', ' ');
        if ($n >= 10) {
            return '<span class="vert">' . $fmt . '</span>';
        }
        return '<span class="rouge">' . $fmt . '</span>';
    }
}

if (!function_exists('formatSimple')) {
    function formatSimple($val)
    {
        if ($val === null || $val === '-') {
            return '-';
        }
        return number_format((float) $val, 2, ',', ' ');
    }
}

if (!function_exists('formatBulletinDate')) {
    function formatBulletinDate($val)
    {
        if ($val === null || $val === '') {
            return '-';
        }
        $timestamp = strtotime((string) $val);
        if ($timestamp === false) {
            return '-';
        }
        return date('d/m/Y', $timestamp);
    }
}

/**
 * 4. BLOC CSS POUR LE RENDU PDF
 * Utilise le fichier partiel bulletin_header.php
 */
if (isset($styleOnly)) {
    $styleOnly = true;
    include __DIR__ . '/bulletin_header.php';
    return;
}
?>
<?php if (!$embeddedBatch && empty($isPdf)): ?>
    <!DOCTYPE html>
    <html lang="fr">

    <head>
        <meta charset="UTF-8">
        <title><?= htmlspecialchars((string) ($pdf_filename ?? ('bulletin-trimestre-' . (int) $term))) ?></title>
        <style>
            <?php $styleOnly = true;
            include __DIR__ . '/bulletin_header.php'; ?>
        </style>
    </head>

    <body>
        <!-- BARRE D'OUTILS (Non visible à l'impression) -->
        <div class="pv-toolbar">
            <div class="pv-toolbar-title">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"
                    style="vertical-align:-3px; margin-right:5px;">
                    <path
                        d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2zM9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5v2z" />
                    <path
                        d="M4.603 14.087a.81.81 0 0 1-.438-.42c-.195-.388-.13-.776.08-1.102.198-.307.526-.568.897-.787a7.68 7.68 0 0 1 1.482-.645 19.697 19.697 0 0 0 1.062-2.227 7.269 7.269 0 0 1-.43-1.295c-.086-.4-.119-.796-.046-1.136.075-.354.274-.672.65-.823.192-.077.4-.12.602-.077a.7.7 0 0 1 .471.215c.15.18-.162 1.305-.162 1.305v.006c-.316.427-.58.111-.58.111s.54.407.728.846c.155.362.29.74.405 1.134.208.718.36 1.4.453 1.954.555.15 1.144.33 1.705.513.29.096.55.195.74.296.262.138.45.321.492.51.042.19.014.39-.115.546-.129.155-.327.24-.546.269-.219.03-.466-.02-.713-.102a4.954 4.954 0 0 1-1.396-.757c-.88-.705-1.58-1.748-1.9-2.235-.351.054-.7.108-1.049.157-.428.06-1.08.125-1.764.125-.453.03-.9.08-1.332.146-.356.055-.705.12-1.05.19-.24.049-.49.123-.715.22z" />
                </svg>
                Mode Impression & Export
            </div>
            <div class="pv-toolbar-hint">
                <?= __('pv_print_hint') ?>
            </div>
            <div>
                <a href="/bulletins?class_id=<?= (int) $student['class_id'] ?>" class="pv-btn pv-btn-back">
                    &larr; <?= __('back') ?>
                </a>
                <button class="pv-btn pv-btn-print" onclick="window.print()">
                    <?= __('pv_print_btn') ?>
                </button>
                <a href="<?= $_SERVER['REQUEST_URI'] . (strpos($_SERVER['REQUEST_URI'], '?') !== false ? '&' : '?') ?>format=pdf"
                    class="pv-btn pv-btn-download">
                    <i class="bi bi-file-pdf"></i> <?= __('pv_download_btn') ?>
                </a>
            </div>
        </div>
    <?php endif; ?>

    <div class="bulletin-wrapper">
        <div class="bulletin-sheet">
        <?php
        // Inclure le partiel d'entête HTML
        $bulletinType = strtoupper(__('trimester')) . ' ' . (int) $term;
        include __DIR__ . '/bulletin_header_html.php';
        ?>

        <?php $bulletinPeriod = 'trimestre'; include __DIR__ . '/bulletin_grades_table.php'; ?>

        <div class="bulletin-footer">
            <span class="bulletin-name"><?= htmlspecialchars($bulletinType ?: __('report_card')) ?></span>
            <span class="bulletin-copyright">© NoteMaster - Douala-Cameroun. <?= htmlspecialchars($i['school_code'] ?? '') ?>.camertech.com</span>
        </div>
    </div>
    </div>

    <?php if (!$embeddedBatch): ?>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const downloadBtns = document.querySelectorAll('.pv-btn-download');
            downloadBtns.forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    showPdfGuidance();
                });
            });
        });

        function showPdfGuidance() {
            let modal = document.getElementById('pdf-guidance-modal');
            if (!modal) {
                modal = document.createElement('div');
                modal.id = 'pdf-guidance-modal';
                modal.innerHTML = `
                    <div class="pdf-modal-backdrop" onclick="closePdfGuidance()"></div>
                    <div class="pdf-modal-card">
                        <div class="pdf-modal-header">
                            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="#ffd700" viewBox="0 0 16 16" style="vertical-align: middle;">
                                <path d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2zM9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5v2z"/>
                                <path d="M4.603 14.087a.81.81 0 0 1-.438-.42c-.195-.388-.13-.776.08-1.102.198-.307.526-.568.897-.787a7.68 7.68 0 0 1 1.482-.645 19.697 19.697 0 0 0 1.062-2.227 7.269 7.269 0 0 1-.43-1.295c-.086-.4-.119-.796-.046-1.136.075-.354.274-.672.65-.823.192-.077.4-.12.602-.077a.7.7 0 0 1 .471.215c.15.18-.162 1.305-.162 1.305v.006c-.316.427-.58.111-.58.111s.54.407.728.846c.155.362.29.74.405 1.134.208.718.36 1.4.453 1.954.555.15 1.144.33 1.705.513.29.096.55.195.74.296.262.138.45.321.492.51.042.19.014.39-.115.546-.129.155-.327.24-.546.269-.219.03-.466-.02-.713-.102a4.954 4.954 0 0 1-1.396-.757c-.88-.705-1.58-1.748-1.9-2.235-.351.054-.7.108-1.049.157-.428.06-1.08.125-1.764.125-.453.03-.9.08-1.332.146-.356.055-.705.12-1.05.19-.24.049-.49.123-.715.22z"/>
                            </svg>
                            <h2>Enregistrement PDF Premium</h2>
                        </div>
                        <div class="pdf-modal-body">
                            <p>Pour exporter votre bulletin avec une <strong>qualité d'impression absolue</strong> (textes parfaits, aucun décalage de tableau, aucune coupure de page) :</p>
                            <div class="pdf-step">
                                <span class="pdf-step-num">1</span>
                                <span class="pdf-step-text">La fenêtre d'impression système va s'ouvrir.</span>
                            </div>
                            <div class="pdf-step">
                                <span class="pdf-step-num">2</span>
                                <span class="pdf-step-text">Dans la case <strong>"Destination"</strong>, sélectionnez <strong>"Enregistrer au format PDF"</strong>.</span>
                            </div>
                            <div class="pdf-step">
                                <span class="pdf-step-num">3</span>
                                <span class="pdf-step-text">Cliquez sur le bouton bleu <strong>"Enregistrer"</strong>.</span>
                            </div>
                        </div>
                        <div class="pdf-modal-footer">
                            <button class="pdf-modal-btn cancel" onclick="closePdfGuidance()">Annuler</button>
                            <button class="pdf-modal-btn confirm" onclick="launchPdfPrint()">Lancer l'Enregistrement PDF</button>
                        </div>
                    </div>
                `;
                document.body.appendChild(modal);
            }
            modal.style.display = 'block';
        }

        function closePdfGuidance() {
            const modal = document.getElementById('pdf-guidance-modal');
            if (modal) {
                modal.style.display = 'none';
            }
        }

        function launchPdfPrint() {
            closePdfGuidance();
            setTimeout(() => {
                window.print();
            }, 300);
        }
        </script>
    </body>

    </html>
<?php endif; ?>