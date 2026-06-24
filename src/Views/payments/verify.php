<?php
// Vue publique de vérification d'un reçu scolaire
// Style Premium, responsive et autonome (sans sidebar d'administration)
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars((string) __('lang')) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= __('verification_title') ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;700;900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary: #2563eb;
            --success: #16a34a;
            --danger: #dc2626;
            --bg-gradient: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            --card-bg: rgba(255, 255, 255, 0.95);
        }

        body {
            font-family: 'Outfit', sans-serif;
            background: var(--bg-gradient);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            color: #334155;
        }

        .verify-card {
            background: var(--card-bg);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.1);
            overflow: hidden;
            width: 100%;
            max-width: 580px;
            animation: slideUp 0.5s ease-out forwards;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .status-header {
            padding: 30px 20px;
            text-align: center;
            color: #fff;
            position: relative;
        }

        .status-header.verified {
            background: linear-gradient(135deg, #15803d 0%, #16a34a 100%);
        }

        .status-header.invalid {
            background: linear-gradient(135deg, #b91c1c 0%, #dc2626 100%);
        }

        .status-icon {
            font-size: 64px;
            margin-bottom: 10px;
            display: inline-block;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.08); }
            100% { transform: scale(1); }
        }

        .school-badge {
            background: rgba(255, 255, 255, 0.2);
            padding: 4px 12px;
            border-radius: 100px;
            font-size: 11px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1px;
            display: inline-block;
            margin-bottom: 8px;
        }

        .details-list {
            padding: 25px;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #f1f5f9;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            font-weight: 500;
            color: #64748b;
            font-size: 13px;
        }

        .detail-value {
            font-weight: 700;
            color: #0f172a;
            font-size: 14px;
            text-align: right;
        }

        .detail-value.amount {
            color: var(--primary);
            font-size: 16px;
        }

        .verify-footer {
            background: #f8fafc;
            padding: 20px;
            text-align: center;
            border-top: 1px solid #f1f5f9;
            font-size: 11px;
            color: #64748b;
        }
    </style>
</head>
<body>

    <div class="verify-card">
        
        <?php if ($payment): ?>
            <!-- CAS 1: REÇU VERIFIE ET AUTHENTIQUE -->
            <div class="status-header verified">
                <span class="school-badge"><?= h($settings['school_name'] ?? 'Établissement Scolaire') ?></span>
                <div>
                    <span class="status-icon"><i class="bi bi-patch-check-fill"></i></span>
                </div>
                <h3 class="fw-bold m-0"><?= __('verified_authentic') ?></h3>
                <small class="opacity-75"><?= __('verified_authentic_sub') ?></small>
            </div>

            <div class="details-list">
                <div class="detail-row">
                    <span class="detail-label"><?= __('jeton') ?></span>
                    <span class="detail-value text-primary font-monospace"><?= h($payment['verification_code']) ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label"><?= __('receipt_number') ?></span>
                    <span class="detail-value">#<?= h($payment['id']) ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label"><?= __('student') ?></span>
                    <span class="detail-value"><?= h($payment['student_nom']) ?> <?= h($payment['student_prenom']) ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label"><?= __('matricule') ?></span>
                    <span class="detail-value font-monospace"><?= h($payment['matricule']) ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label"><?= __('born_on') ?></span>
                    <span class="detail-value">
                        <?= !empty($payment['date_naissance']) ? date('d/m/Y', strtotime($payment['date_naissance'])) : 'N/A' ?>
                        <?= !empty($payment['lieu_naissance']) ? ' ' . __('born_at') . ' ' . h($payment['lieu_naissance']) : '' ?>
                    </span>
                </div>
                <?php if (!empty($payment['adresse'])): ?>
                    <div class="detail-row">
                        <span class="detail-label"><?= __('address') ?></span>
                        <span class="detail-value"><?= h($payment['adresse']) ?></span>
                    </div>
                <?php endif; ?>
                <div class="detail-row">
                    <span class="detail-label"><?= __('class') ?></span>
                    <span class="detail-value"><?= h($payment['classe_nom'] ?: 'N/A') ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label"><?= __('nature_payment') ?></span>
                    <span class="detail-value">
                        <?= $payment['type'] === 'inscription' ? __('registration_payment_option') : __('tuition_payment_option') ?>
                    </span>
                </div>
                <div class="detail-row">
                    <span class="detail-label"><?= __('amount_paid') ?></span>
                    <span class="detail-value amount"><?= number_format($payment['amount'], 0, '.', ' ') ?> FCFA</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label"><?= __('payment_date') ?></span>
                    <span class="detail-value"><?= date('d/m/Y', strtotime($payment['payment_date'])) ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label"><?= __('payment_method_label') ?></span>
                    <span class="detail-value"><?= h($payment['payment_method']) ?></span>
                </div>
                <?php if (!empty($payment['reference'])): ?>
                    <div class="detail-row">
                        <span class="detail-label"><?= __('col_reference') ?></span>
                        <span class="detail-value font-monospace"><?= h($payment['reference']) ?></span>
                    </div>
                <?php endif; ?>
            </div>

        <?php else: ?>
            <!-- CAS 2: REÇU INVALIDE OU CODE ERRONÉ -->
            <div class="status-header invalid">
                <span class="school-badge"><?= __('security_warning_badge') ?></span>
                <div>
                    <span class="status-icon"><i class="bi bi-shield-x"></i></span>
                </div>
                <h3 class="fw-bold m-0"><?= __('receipt_invalid') ?></h3>
                <small class="opacity-75"><?= __('receipt_invalid_sub') ?></small>
            </div>

            <div class="details-list text-center py-5">
                <i class="bi bi-exclamation-triangle text-danger fs-1"></i>
                <h5 class="fw-bold mt-3 text-dark"><?= __('counterfeit_risk') ?></h5>
                <p class="text-muted small px-3">
                    <?= __('counterfeit_help') ?>
                </p>
                <?php if (!empty($code)): ?>
                    <div class="mt-4 p-2 bg-light rounded font-monospace text-danger border border-danger border-opacity-10 small">
                        <?= __('code_searched') ?> <?= h($code) ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="verify-footer">
            <?= __('official_validation_platform') ?><br>
            <span class="extra-small opacity-50"><?= __('generated_on') ?> <?= date('d/m/Y H:i:s') ?></span>
        </div>

    </div>

</body>
</html>
