<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pdf_filename ?? 'bulletins-classe') ?></title>
    <style>
        body {
            margin: 0;
        }

        /* BARRE D'OUTILS */
        @media print {
            .pv-toolbar {
                display: none !important;
            }
        }

        .pv-toolbar {
            position: sticky;
            top: 0;
            z-index: 100;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 20px;
            background: #1a1a2e;
            color: white;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 15px;
            font-family: Arial, Helvetica, sans-serif;
        }

        .pv-toolbar-title {
            font-weight: bold;
            font-size: 13px;
            opacity: 0.9;
        }

        .pv-toolbar-hint {
            font-size: 10px;
            opacity: 0.6;
            margin-right: auto;
        }

        .pv-btn {
            padding: 7px 18px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 12px;
            font-weight: bold;
            text-decoration: none;
            transition: opacity 0.2s;
            display: inline-block;
        }

        .pv-btn:hover {
            opacity: 0.85;
        }

        .pv-btn-print {
            background: #0d6efd;
            color: white;
        }

        .pv-btn-download {
            background: #198754;
            color: white;
            margin-left: 5px;
        }

        .pv-btn-back {
            background: rgba(255, 255, 255, 0.15);
            color: white;
            margin-right: 5px;
        }

        @media screen and (max-width: 600px) {
            .pv-toolbar {
                flex-direction: column;
                align-items: stretch;
                gap: 8px;
            }

            .pv-btn {
                width: 100%;
                text-align: center;
                margin: 2px 0 !important;
            }
        }

        <?php
        $styleOnly = true;
        $rows = $bulletins[0]['rows'] ?? [];
        include __DIR__ . '/sequence.php';
        unset($styleOnly);
        ?>
    </style>
</head>

<body>
    <?php if (empty($isPdf)): ?>
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
                <?= __('pv_print_mode_title') ?>
            </div>
            <div class="pv-toolbar-hint">
                <?= __('pv_print_hint') ?>
            </div>
            <div>
                <a href="/bulletins" class="pv-btn pv-btn-back">
                    &larr; <?= __('back') ?>
                </a>
                <button class="pv-btn pv-btn-print" onclick="window.print()">
                    <?= __('pv_print_btn') ?>
                </button>
                <a href="<?= $_SERVER['REQUEST_URI'] . (strpos($_SERVER['REQUEST_URI'], '?') !== false ? '&' : '?') ?>format=pdf" class="pv-btn pv-btn-download">
                    <i class="bi bi-file-pdf"></i> <?= __('pv_download_btn') ?>
                </a>
            </div>
        </div>
    <?php endif; ?>

    <?php foreach ($bulletins as $index => $bulletin): ?>
        <?php $embeddedBatch = true;
        extract($bulletin);
        include __DIR__ . '/sequence.php'; ?>
    <?php endforeach; ?>

    <?php if (isset($_GET['autoprint']) && $_GET['autoprint'] == '1'): ?>
        <script>
            window.addEventListener('load', function () {
                setTimeout(function () {
                    window.print();
                }, 800);
            });
        </script>
    <?php endif; ?>
</body>

</html>