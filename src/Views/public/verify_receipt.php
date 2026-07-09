<?php
// Vue publique de vérification d'un reçu (Inscription & Scolarité)
// Style Premium, responsive et autonome
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars((string) __('lang')) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= __('verification_title') ?? 'Vérification de Reçu' ?></title>
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
            max-width: 650px;
            animation: slideUp 0.5s ease-out forwards;
            margin: auto;
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

        .section-title {
            font-size: 14px;
            font-weight: 700;
            color: #1e293b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 25px 25px 10px 25px;
            padding-bottom: 5px;
            border-bottom: 2px solid #e2e8f0;
        }

        .details-list {
            padding: 0 25px 10px 25px;
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

        .history-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            background: #f8fafc;
            border-radius: 8px;
            margin-bottom: 8px;
            border: 1px solid #e2e8f0;
        }

        .history-date {
            font-size: 12px;
            color: #64748b;
            font-weight: 600;
        }

        .history-amount {
            font-size: 14px;
            font-weight: 700;
            color: #0f172a;
        }
    </style>
</head>
<body>

    <div class="verify-card">
        
        <?php if ($isValid): ?>
            <!-- CAS 1: REÇU VERIFIE ET AUTHENTIQUE -->
            <div class="status-header verified">
                <span class="school-badge"><?= h($settings['school_name'] ?? 'Établissement Scolaire') ?></span>
                <div>
                    <span class="status-icon"><i class="bi bi-patch-check-fill"></i></span>
                </div>
                <h3 class="fw-bold m-0"><?= __('verified_authentic') ?? 'Reçu Authentique' ?></h3>
                <small class="opacity-75"><?= __('verified_authentic_sub') ?? 'Ce paiement est certifié valide par l\'établissement' ?></small>
            </div>

            <div class="section-title"><i class="bi bi-receipt"></i> Informations du Paiement</div>
            <div class="details-list">
                <div class="detail-row">
                    <span class="detail-label"><?= __('jeton') ?? 'Jeton de vérification' ?></span>
                    <span class="detail-value text-primary font-monospace"><?= h($code) ?></span>
                </div>
                <?php if (!empty($payment['receipt_number']) || !empty($payment['id'])): ?>
                <div class="detail-row">
                    <span class="detail-label"><?= __('receipt_number') ?? 'Numéro de reçu' ?></span>
                    <span class="detail-value">
                        <?= h($payment['receipt_number'] ?? '#' . $payment['id']) ?>
                    </span>
                </div>
                <?php endif; ?>
                <div class="detail-row">
                    <span class="detail-label"><?= __('nature_payment') ?? 'Nature' ?></span>
                    <span class="detail-value">
                        <?= $receiptType === 'inscription' ? (__('registration_payment_option') ?? 'Frais d\'inscription') : (__('tuition_payment_option') ?? 'Frais de scolarité') ?>
                    </span>
                </div>
                <div class="detail-row">
                    <span class="detail-label"><?= __('amount_paid') ?? 'Montant payé' ?></span>
                    <span class="detail-value amount"><?= number_format($payment['amount'], 0, '.', ' ') ?> FCFA</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label"><?= __('payment_date') ?? 'Date de paiement' ?></span>
                    <span class="detail-value">
                        <?= date('d/m/Y', strtotime($payment['payment_date'] ?? $payment['created_at'])) ?>
                        <?php if (isset($payment['created_at'])) echo " à " . date('H:i', strtotime($payment['created_at'])); ?>
                    </span>
                </div>
                <div class="detail-row">
                    <span class="detail-label"><?= __('payment_method_label') ?? 'Méthode' ?></span>
                    <span class="detail-value"><?= h($payment['payment_method']) ?></span>
                </div>
                <?php if (!empty($payment['reference'])): ?>
                    <div class="detail-row">
                        <span class="detail-label"><?= __('col_reference') ?? 'Référence' ?></span>
                        <span class="detail-value font-monospace"><?= h($payment['reference']) ?></span>
                    </div>
                <?php endif; ?>
                <div class="detail-row">
                    <span class="detail-label">Année Scolaire</span>
                    <span class="detail-value"><?= h($settings['display_school_year'] ?? '') ?></span>
                </div>
            </div>

            <div class="section-title"><i class="bi bi-person-badge"></i> Informations de l'Élève</div>
            <div class="details-list">
                <div class="detail-row">
                    <span class="detail-label"><?= __('student') ?? 'Élève' ?></span>
                    <span class="detail-value"><?= h($payment['student_nom'] ?? '') ?> <?= h($payment['student_prenom'] ?? '') ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label"><?= __('matricule') ?? 'Matricule' ?></span>
                    <span class="detail-value font-monospace"><?= h($payment['matricule'] ?? 'N/A') ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label"><?= __('born_on') ?? 'Né(e) le' ?></span>
                    <span class="detail-value">
                        <?= !empty($payment['date_naissance']) ? date('d/m/Y', strtotime($payment['date_naissance'])) : 'N/A' ?>
                        <?= !empty($payment['lieu_naissance']) ? ' ' . (__('born_at') ?? 'à') . ' ' . h($payment['lieu_naissance']) : '' ?>
                    </span>
                </div>
                <div class="detail-row">
                    <span class="detail-label"><?= __('class') ?? 'Classe' ?></span>
                    <span class="detail-value"><?= h($payment['classe_nom'] ?: 'N/A') ?></span>
                </div>
            </div>

            <?php if (!empty($enroll)): ?>
            <div class="section-title"><i class="bi bi-wallet2"></i> Dernière Opération</div>
            <div class="details-list">
                <div class="detail-row">
                    <span class="detail-label">Scolarité Nette (Annuelle)</span>
                    <span class="detail-value"><?= number_format($enroll['scolarite_nette'] ?? 0, 0, '.', ' ') ?> FCFA</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Total Cumulé Payé</span>
                    <span class="detail-value text-success"><?= number_format($enroll['total_paye'] ?? 0, 0, '.', ' ') ?> FCFA</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Reste à Payer</span>
                    <span class="detail-value text-danger"><?= number_format($enroll['reste_a_payer'] ?? 0, 0, '.', ' ') ?> FCFA</span>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($paymentHistory) && count($paymentHistory) > 1): ?>
            <div class="section-title"><i class="bi bi-clock-history"></i> Historique des versements (Année en cours)</div>
            <div class="details-list">
                <?php foreach ($paymentHistory as $hist): ?>
                    <div class="history-item <?= ($hist['id'] == $paymentId) ? 'border-primary shadow-sm bg-white' : '' ?>">
                        <div class="history-date">
                            <i class="bi bi-calendar-check"></i> <?= date('d/m/Y', strtotime($hist['payment_date'])) ?>
                            <?php if ($hist['id'] == $paymentId): ?>
                                <span class="badge bg-primary ms-2">Ce reçu</span>
                            <?php endif; ?>
                        </div>
                        <div class="history-amount text-success">
                            <?= number_format($hist['amount'], 0, '.', ' ') ?> FCFA
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

        <?php else: ?>
            <!-- CAS 2: REÇU INVALIDE OU CODE ERRONÉ -->
            <div class="status-header invalid">
                <span class="school-badge"><?= __('security_warning_badge') ?? 'Alerte Sécurité' ?></span>
                <div>
                    <span class="status-icon"><i class="bi bi-shield-x"></i></span>
                </div>
                <h3 class="fw-bold m-0"><?= __('receipt_invalid') ?? 'Reçu Invalide' ?></h3>
                <small class="opacity-75">
                    <?php 
                    if ($errorCase === 'cancelled') echo "Ce reçu a été annulé dans le système.";
                    elseif ($errorCase === 'missing_code') echo "Aucun code fourni.";
                    else echo "Ce reçu n'existe pas dans le système.";
                    ?>
                </small>
            </div>

            <div class="details-list text-center py-5">
                <i class="bi bi-exclamation-triangle text-danger fs-1"></i>
                <h5 class="fw-bold mt-3 text-dark"><?= __('counterfeit_risk') ?? 'Risque de falsification' ?></h5>
                <p class="text-muted small px-3">
                    <?= __('counterfeit_help') ?? 'Veuillez vous rapprocher de la direction financière de l\'établissement.' ?>
                </p>
                <?php if (!empty($code)): ?>
                    <div class="mt-4 p-2 bg-light rounded font-monospace text-danger border border-danger border-opacity-10 small" style="word-break: break-all;">
                        <?= __('code_searched') ?? 'Code :' ?> <?= h($code) ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="verify-footer">
            <?= __('official_validation_platform') ?? 'Plateforme officielle de validation' ?><br>
            <span class="extra-small opacity-50"><?= __('generated_on') ?? 'Généré le' ?> <?= date('d/m/Y H:i:s') ?></span>
        </div>

    </div>

</body>
</html>
