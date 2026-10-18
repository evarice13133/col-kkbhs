<?php
$escape = static fn($value): string => htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
$money = static fn($amount): string => number_format((float) $amount, 0, ',', ' ') . ' FCFA';
$schoolName = $settings['school_name'] ?? 'NotesMaster';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Relevé financier des élèves démissionnaires</title>
    <style>
        @page { size: A4 landscape; margin: 12mm; }
        body { font-family: Helvetica, Arial, sans-serif; color: #1e293b; font-size: 9px; }
        h1 { margin: 0; color: #0f172a; font-size: 19px; }
        h2 { margin: 0; font-size: 13px; color: #1d4ed8; }
        .header { border-bottom: 2px solid #1d4ed8; padding-bottom: 10px; margin-bottom: 14px; }
        .meta { color: #64748b; margin-top: 5px; }
        .student { margin: 14px 0; page-break-inside: avoid; }
        .student-heading { background: #f1f5f9; padding: 7px; border-left: 3px solid #dc2626; }
        .student-heading strong { font-size: 11px; }
        .subheading { margin: 9px 0 4px; font-weight: bold; color: #334155; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 7px; }
        th, td { border: 1px solid #cbd5e1; padding: 4px 5px; text-align: left; vertical-align: top; }
        th { background: #e2e8f0; font-weight: bold; }
        .number { text-align: right; white-space: nowrap; }
        .empty { color: #64748b; font-style: italic; }
        .student + .student { border-top: 1px solid #94a3b8; padding-top: 9px; }
        .totals { font-weight: bold; background: #f8fafc; }
    </style>
</head>
<body>
    <div class="header">
        <h1><?= $escape($schoolName) ?></h1>
        <h2>Relevé financier des élèves démissionnaires</h2>
        <div class="meta">
            Frais d'inscription, frais de scolarité et opérations enregistrées depuis l'inscription
            — Généré le <?= $escape($generatedAt) ?> — <?= count($students) ?> élève(s)
        </div>
    </div>

    <?php if (!$students): ?>
        <p class="empty">Aucun élève démissionnaire n'est enregistré.</p>
    <?php endif; ?>

    <?php foreach ($students as $student): ?>
        <?php
        $studentId = (int) $student['id'];
        $years = $studentFinance[$studentId]['years'] ?? [];
        krsort($years);
        ?>
        <section class="student">
            <div class="student-heading">
                <strong><?= $escape(trim(($student['nom'] ?? '') . ' ' . ($student['prenom'] ?? ''))) ?></strong>
                — Matricule : <?= $escape($student['email'] ?? 'N/A') ?>
                — Statut : <?= $escape($student['status'] ?? 'Démissionnaire') ?>
                — Classe actuelle : <?= $escape($student['current_class'] ?? 'Non définie') ?>
            </div>

            <div class="subheading">Situation financière par année scolaire</div>
            <table>
                <thead>
                    <tr>
                        <th>Année</th>
                        <th>Classe</th>
                        <th>Frais d'inscription attendus</th>
                        <th>Frais d'inscription payés</th>
                        <th>Scolarité brute</th>
                        <th>Réductions / bourses</th>
                        <th>Scolarité nette</th>
                        <th>Scolarité payée</th>
                        <th>Reste scolarité</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$years): ?>
                        <tr><td colspan="9" class="empty">Aucun frais ou versement associé n'a été trouvé.</td></tr>
                    <?php else: ?>
                        <?php foreach ($years as $year): ?>
                            <?php
                            $studentStatus = strtolower((string) ($year['student_status'] ?? ''));
                            $registrationFeePolicy = $year['registration_fee_policy'] ?? 'all';
                            $registrationExpected = (float) $year['frais_inscription'];
                            if ($registrationFeePolicy === 'new_only' && $studentStatus !== 'nouveau') {
                                $registrationExpected = 0.0;
                            } elseif ($registrationFeePolicy === 'by_status' && $studentStatus !== 'nouveau') {
                                $registrationExpected = (float) $year['frais_inscription_reinscription'];
                            }
                            $discounts = (float) $year['total_reductions'] + (float) $year['total_bourses'];
                            $netTuition = max(0.0, (float) $year['frais_scolarite_brut'] - $discounts);
                            ?>
                            <tr>
                                <td><?= $escape($year['academic_year']) ?></td>
                                <td><?= $escape($year['class_name'] ?: '—') ?></td>
                                <td class="number"><?= $money($registrationExpected) ?></td>
                                <td class="number"><?= $money($year['registration_paid']) ?></td>
                                <td class="number"><?= $money($year['frais_scolarite_brut']) ?></td>
                                <td class="number"><?= $money($discounts) ?></td>
                                <td class="number"><?= $money($netTuition) ?></td>
                                <td class="number"><?= $money($year['tuition_paid']) ?></td>
                                <td class="number"><?= $money($year['reste_a_payer']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <div class="subheading">Détail des opérations financières</div>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Année</th>
                        <th>Opération</th>
                        <th class="number">Montant</th>
                        <th>Moyen de paiement</th>
                        <th>Référence</th>
                        <th>Commentaire</th>
                        <th>Enregistré par</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $payments = [];
                    foreach ($years as $year) {
                        $payments = array_merge($payments, $year['payments']);
                    }
                    usort($payments, static fn(array $a, array $b): int => strcmp((string) $a['payment_date'], (string) $b['payment_date']));
                    ?>
                    <?php if (!$payments): ?>
                        <tr><td colspan="8" class="empty">Aucune opération financière enregistrée.</td></tr>
                    <?php else: ?>
                        <?php foreach ($payments as $payment): ?>
                            <tr>
                                <td><?= $escape($payment['payment_date']) ?></td>
                                <td><?= $escape($payment['academic_year'] ?? '') ?></td>
                                <td><?= $payment['type'] === 'inscription' ? "Frais d'inscription" : "Frais de scolarité" ?></td>
                                <td class="number"><?= $money($payment['amount']) ?></td>
                                <td><?= $escape($payment['payment_method']) ?></td>
                                <td><?= $escape($payment['reference'] ?: '—') ?></td>
                                <td><?= $escape($payment['commentaire'] ?: '—') ?></td>
                                <td><?= $escape(trim(($payment['creator_nom'] ?? '') . ' ' . ($payment['creator_prenom'] ?? '')) ?: '—') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
    <?php endforeach; ?>
</body>
</html>
