<?php
$statisticsRows = [];
$statisticsLabels = (array) ($evaluationLabels ?? []);

if (($bulletinPeriod ?? '') === 'trimestre') {
    $firstTermLabels = array_values(array_filter(array_map(static function ($label) {
        $value = trim((string) ($label ?? ''));
        return $value !== '' ? $value : null;
    }, (array) ($previousTermEvaluationLabels ?? []))));
    $firstTermValues = array_values((array) ($previousTermSeqAverages ?? []));
    $firstTermRanks = array_values((array) ($previousTermSeqRanks ?? []));

    $secondTermLabels = array_values(array_filter(array_map(static function ($label) {
        $value = trim((string) ($label ?? ''));
        return $value !== '' ? $value : null;
    }, (array) ($evaluationLabels ?? []))));
    $secondTermValues = array_values((array) ($seqAverages ?? []));
    $secondTermRanks = array_values((array) ($seqRanks ?? []));

    if ((int) ($term ?? 1) === 1) {
        $firstTermLabels = $secondTermLabels;
        $firstTermValues = $secondTermValues;
        $firstTermRanks = $secondTermRanks;
        $secondTermLabels = [];
        $secondTermValues = [];
        $secondTermRanks = [];
    }

    $statisticsLabels = array_merge($firstTermLabels, $secondTermLabels, [__('average')]);
    $statisticsValues = array_merge($firstTermValues, $secondTermValues, [$average ?? null]);
    $statisticsRanks = array_merge($firstTermRanks, $secondTermRanks, [$rank ?? null]);
} elseif (($bulletinPeriod ?? '') === 'annuel') {
    $statisticsLabels = [__('trimester_short') . ' 1', __('trimester_short') . ' 2', __('trimester_short') . ' 3', __('annual')];
    $statisticsValues = array_values((array) ($termAverages ?? []));
    $statisticsRanks = array_values((array) ($termRanks ?? []));
    $statisticsValues[] = $average ?? null;
    $statisticsRanks[] = $rank ?? null;
} else {
    $statisticsValues = [$average ?? null];
    $statisticsRanks = [$rank ?? null];
}

foreach ($statisticsLabels as $index => $label) {
    $statisticsRows[] = [
        'label' => $label,
        'average' => $statisticsValues[$index] ?? null,
        'rank' => $statisticsRanks[$index] ?? null,
    ];
}
$statisticsTopRows = array_values($statisticsRows);
$showSecondTerm = ($bulletinPeriod ?? '') !== 'trimestre' || (int) ($term ?? 1) >= 2;
$visibleTopRows = $showSecondTerm ? $statisticsTopRows : array_slice($statisticsTopRows, 0, 3);
$firstTermColumnCount = max(2, count((array) ($previousTermEvaluationLabels ?? [])));
$secondTermColumnCount = max(1, count((array) ($evaluationLabels ?? [])));
$firstEvaluationAverage = $statisticsTopRows[0]['average'] ?? null;
$secondEvaluationAverage = $statisticsTopRows[1]['average'] ?? null;
$currentTermAverage = $average ?? null;
$currentEvaluationValues = [];
if (($bulletinPeriod ?? '') === 'trimestre') {
    $currentEvaluationValues = (int) ($term ?? 1) === 1
        ? (array) ($firstTermValues ?? [])
        : (array) ($secondTermValues ?? []);
}
$currentEvaluationValues = array_values(array_filter($currentEvaluationValues, static function ($value) {
    return $value !== null && $value !== '' && is_numeric($value);
}));
if ($currentEvaluationValues !== []) {
    $currentTermAverage = round(array_sum(array_map('floatval', $currentEvaluationValues)) / count($currentEvaluationValues), 2);
}
$previousTermAverage = $previousTermAverage ?? $firstEvaluationAverage;
$currentTermAverageColor = ((float) ($currentTermAverage ?? 0)) < 10 ? '#d62828' : '#1f9d55';
$statisticsAverageStyle = static function ($value) {
    if ($value === null || !is_numeric($value)) {
        return '';
    }

    $numericValue = (float) $value;
    $color = $numericValue < 10 ? '#d62828' : '#1f9d55';

    return 'color: ' . $color . '; font-weight: 700;';
};
$statisticsDisplayValue = static function ($value) {
    return $value === null || $value === '' ? '-' : formatSimple($value);
};

$statisticsPassed = 0;
$statisticsTotal = 0;
foreach ((array) ($rows ?? []) as $statisticsSubject) {
    $statisticsValue = $statisticsSubject['note'] ?? $statisticsSubject['term_note'] ?? $statisticsSubject['annual_note'] ?? null;
    if ($statisticsValue !== null) {
        $statisticsTotal++;
        if ((float) $statisticsValue >= 10) {
            $statisticsPassed++;
        }
    }
}

$statisticsProfile = (array) ($classStats['profile'] ?? []);
$statisticsTotalStudents = (int) ($statisticsProfile['total'] ?? $effectif ?? 0);
$statisticsPercentage = static function ($value) use ($statisticsTotalStudents) {
    return $statisticsTotalStudents > 0 ? formatSimple(((int) $value / $statisticsTotalStudents) * 100) . '%' : '0%';
};
$statisticsDiscipline = (array) ($discipline ?? []);
$statisticsAbsences = (array) ($statisticsDiscipline['absences'] ?? []);
$statisticsCouncilDecision = $councilDecision ?? __('average_level');
$statisticsColumnCount = count($visibleTopRows) + 2;
$statisticsSideColumnWidth = 18;
$statisticsEvaluationColumnWidth = (100 - (2 * $statisticsSideColumnWidth)) / max(1, $statisticsColumnCount - 2);
?>
<style>
    .bulletin-statistics { width: 100%; margin: 1px 0 0; font-size: 9px; line-height: 1.05; page-break-inside: avoid; }
    .bulletin-statistics table { width: 100%; margin: 0 !important; border-collapse: collapse; table-layout: fixed; }
    .bulletin-statistics td { border: 1px solid #14347a; padding: 0 2px; height: 10px; font-size: 9px; line-height: 1; }
    .bulletin-statistics th { padding: 0 1px; font-size: 9px; line-height: 1; }
    .bulletin-sheet .bulletin-statistics td,
    .bulletin-sheet .bulletin-statistics th { padding: 0 2px !important; font-size: 9px !important; line-height: 1 !important; }
    .bulletin-statistics .statistics-head { background: #14347a; color: #fff; font-weight: 700; text-align: center; text-transform: uppercase; }
    .bulletin-statistics .statistics-label { text-align: left; font-weight: 700; white-space: normal; overflow-wrap: anywhere; word-break: break-word; }
    .bulletin-statistics .statistics-value { text-align: center; font-weight: 700; }
    .bulletin-statistics .statistics-remarks { width: 23%; height: 100%; min-height: 74px; vertical-align: top; text-align: left; }
    .bulletin-statistics .statistics-main-cell { width: 77%; vertical-align: top; padding: 0; }
    .bulletin-statistics .statistics-remarks-cell { width: 23%; vertical-align: top; padding: 0; }
    .bulletin-statistics .statistics-footer-spacer { width: 28%; padding: 0; border: 0; }
    .bulletin-statistics .statistics-footer-label { width: 28%; text-align: left; font-weight: 700; }
    .bulletin-statistics .statistics-decision-title { height: 18px; padding: 2px 3px !important; }
    .bulletin-statistics .statistics-decision-content { height: 48px; padding: 2px 5px !important; vertical-align: middle; text-align: left; }
    .bulletin-statistics .statistics-decision-content strong { display: block; line-height: 14px; white-space: nowrap; }
    .bulletin-statistics .statistics-decision-content input { vertical-align: middle; margin: 0 3px 0 8px; }
    @media print { .bulletin-statistics { page-break-inside: avoid; } }
</style>

<div class="bulletin-statistics">
<table class="statistics-single-table">
    <colgroup>
        <?php if (!$showSecondTerm): ?>
            <col style="width:11%;"><col style="width:11%;"><col style="width:11%;"><col style="width:11%;">
            <col style="width:7%;"><col style="width:7%;"><col style="width:7%;"><col style="width:9%;"><col style="width:26%;">
        <?php else: ?>
            <col style="width:7%;"><col style="width:8%;"><col style="width:8%;"><col style="width:8%;"><col style="width:8%;">
            <col style="width:8%;"><col style="width:8%;"><col style="width:16%;"><col style="width:29%;">
        <?php endif; ?>
    </colgroup>
    <tr>
        <td class="statistics-head"></td>
        <?php if (($bulletinPeriod ?? '') === 'annuel'): ?>
            <td class="statistics-head" colspan="4"><?= __('evaluation_list') ?></td>
        <?php else: ?>
            <td class="statistics-head" colspan="<?= $showSecondTerm ? $firstTermColumnCount : 3 ?>"><?= __('first_term') ?></td>
            <?php if ($showSecondTerm): ?>
                <td class="statistics-head" colspan="<?= $secondTermColumnCount + 1 ?>"><?= __('second_term') ?></td>
            <?php endif; ?>
        <?php endif; ?>
        <td class="statistics-head" colspan="4"><?= __('subjects_passed') ?></td>
    </tr>
    <tr>
        <td class="statistics-label"><?= __('test_label') ?></td>
        <?php if (($bulletinPeriod ?? '') === 'trimestre' && (int) ($term ?? 1) > 1): ?>
            <?php foreach (array_pad($firstTermLabels, $firstTermColumnCount, '') as $label): ?>
                <td class="statistics-value"><?= htmlspecialchars((string) $label) ?></td>
            <?php endforeach; ?>
            <?php foreach (array_pad($secondTermLabels, $secondTermColumnCount, '') as $label): ?>
                <td class="statistics-value"><?= htmlspecialchars((string) $label) ?></td>
            <?php endforeach; ?>
            <td class="statistics-value"><?= __('average') ?></td>
            <td class="statistics-value" colspan="4"><?= __('out_of') ?></td>
        <?php else: ?>
            <?php foreach ($visibleTopRows as $statisticsRow): ?>
                <td class="statistics-value"><?= htmlspecialchars((string) $statisticsRow['label']) ?></td>
            <?php endforeach; ?>
            <td class="statistics-value" colspan="4"><?= __('out_of') ?></td>
        <?php endif; ?>
    </tr>
    <tr>
        <td class="statistics-label"><?= __('average') ?></td>
        <?php if (($bulletinPeriod ?? '') === 'trimestre' && (int) ($term ?? 1) > 1): ?>
            <?php foreach (array_pad($firstTermValues, $firstTermColumnCount, null) as $value): ?>
                <td class="statistics-value" style="<?= $statisticsAverageStyle($value) ?>"><?= formatSimple($value) ?></td>
            <?php endforeach; ?>
            <?php foreach (array_pad($secondTermValues, $secondTermColumnCount, null) as $value): ?>
                <td class="statistics-value" style="<?= $statisticsAverageStyle($value) ?>"><?= formatSimple($value) ?></td>
            <?php endforeach; ?>
            <td class="statistics-value" style="<?= $statisticsAverageStyle($currentTermAverage) ?>"><?= $statisticsDisplayValue($currentTermAverage) ?></td>
            <td class="statistics-value" colspan="4" rowspan="4"><?= $statisticsPassed ?> / <?= $statisticsTotal ?></td>
        <?php else: ?>
            <?php foreach ($visibleTopRows as $statisticsRow): ?>
                <td class="statistics-value" style="<?= $statisticsAverageStyle($statisticsRow['average']) ?>"><?= formatSimple($statisticsRow['average']) ?></td>
            <?php endforeach; ?>
            <td class="statistics-value" colspan="4" rowspan="4"><?= $statisticsPassed ?> / <?= $statisticsTotal ?></td>
        <?php endif; ?>
    </tr>
    <tr>
        <td class="statistics-label"><?= __('rank') ?></td>
        <?php if (($bulletinPeriod ?? '') === 'trimestre' && (int) ($term ?? 1) > 1): ?>
            <?php foreach (array_pad($firstTermRanks, $firstTermColumnCount, null) as $rankValue): ?>
                <td class="statistics-value"><?= htmlspecialchars((string) ($rankValue ?? '-')) ?></td>
            <?php endforeach; ?>
            <?php foreach (array_pad($secondTermRanks, $secondTermColumnCount, null) as $rankValue): ?>
                <td class="statistics-value"><?= htmlspecialchars((string) ($rankValue ?? '-')) ?></td>
            <?php endforeach; ?>
            <td class="statistics-value"><?= htmlspecialchars((string) ($rank ?? '-')) ?></td>
        <?php else: ?>
            <?php foreach ($visibleTopRows as $statisticsRow): ?>
                <td class="statistics-value"><?= htmlspecialchars((string) ($statisticsRow['rank'] ?? '-')) ?></td>
            <?php endforeach; ?>
        <?php endif; ?>
    </tr>
    <tr>
        <td class="statistics-label" rowspan="2"><?= __('terms_results') ?></td>
        <?php if ($showSecondTerm): ?>
            <td class="statistics-value" rowspan="2"><?= __('first_term') ?></td>
            <td class="statistics-value" rowspan="2"><?= $statisticsDisplayValue($previousTermAverage) ?></td>
            <td class="statistics-value" rowspan="2"><?= __('second_term') ?></td>
            <td class="statistics-value" rowspan="2" style="color: <?= $currentTermAverageColor ?>; font-weight: 700;">
                <?= $statisticsDisplayValue($currentTermAverage) ?>
            </td>
        <?php else: ?>
            <td class="statistics-value" rowspan="2"><?= __('first_term') ?></td>
            <td class="statistics-value"><?= $statisticsDisplayValue($firstEvaluationAverage) ?></td>
            <td class="statistics-value" rowspan="2" style="color: <?= $currentTermAverageColor ?>; font-weight: 700;">
                <?= $statisticsDisplayValue($currentTermAverage) ?>
            </td>
        <?php endif; ?>
    </tr>
    <tr>
        <?php if (($showSecondTerm ?? false)): ?>
        <?php else: ?>
            <td class="statistics-value"><?= $statisticsDisplayValue($secondEvaluationAverage) ?></td>
        <?php endif; ?>
    </tr>

    <tr>
                <td class="statistics-head" colspan="3"><?= __('discipline') ?></td>
                <td class="statistics-head" colspan="2"><?= __('class_council_decision') ?></td>
                <td class="statistics-head" colspan="2"><?= __('class_performance') ?></td>
                <td class="statistics-head" colspan="2"><?= __('principal_remarks_sign') ?></td>
            </tr>
                <tr>
                    <td class="statistics-label"><?= __('lateness') ?></td>
                    <td colspan="2" class="statistics-value"><?= (int) ($statisticsDiscipline['late_count'] ?? $statisticsDiscipline['retards'] ?? 0) ?></td>
                    <td><?= __('council_decision') ?></td>
                    <td class="statistics-value"><?= htmlspecialchars((string) $statisticsCouncilDecision) ?></td>
                    <td><?= __('highest_average') ?></td>
                    <td class="statistics-value"><?= formatSimple($classStats['max'] ?? null) ?></td>
                    <td class="statistics-remarks" colspan="2" rowspan="<?= ($bulletinPeriod ?? '') === 'annuel' ? 17 : 15 ?>"></td>
                </tr>
                <tr>
                    <td rowspan="2" class="statistics-label"><?= __('absences') ?></td>
                    <td>J</td>
                    <td class="statistics-value"><?= (int) ($statisticsAbsences['justified'] ?? $statisticsDiscipline['absences_justified'] ?? 0) ?></td>
                    <td><?= __('poor') ?></td>
                    <td class="statistics-value"><?= $statisticsPercentage($statisticsProfile['below'] ?? 0) ?></td>
                    <td><?= __('lowest_average') ?></td>
                    <td class="statistics-value"><?= formatSimple($classStats['min'] ?? null) ?></td>
                </tr>
                <tr>
                    <td>N.J</td>
                    <td class="statistics-value"><?= (int) ($statisticsAbsences['unjustified'] ?? $statisticsDiscipline['absences_unjustified'] ?? 0) ?></td>
                    <td><?= __('average_level') ?></td>
                    <td class="statistics-value"><?= $statisticsPercentage($statisticsProfile['middle'] ?? 0) ?></td>
                    <td><?= __('class_average') ?></td>
                    <td class="statistics-value"><?= formatSimple($classStats['average'] ?? null) ?></td>
                </tr>
                <tr>
                    <td class="statistics-label"><?= __('punishment') ?></td>
                    <td colspan="2" class="statistics-value"><?= (int) ($statisticsDiscipline['exclusion_days'] ?? 0) ?></td>
                    <td><?= __('fairly_good') ?></td>
                    <td class="statistics-value"><?= $statisticsPercentage($statisticsProfile['passed'] ?? 0) ?></td>
                    <td rowspan="2"><?= __('subjects_above_average') ?></td>
                    <td rowspan="2" class="statistics-value"><?= (int) ($statisticsProfile['passed'] ?? 0) ?></td>
                </tr>
                <tr>
                    <td class="statistics-label"><?= __('warnings') ?></td>
                    <td><?= __('conduct') ?></td>
                    <td class="statistics-value"><?= htmlspecialchars((string) ($statisticsDiscipline['warning_conduct'] ?? '')) ?></td>
                    <td><?= __('good') ?></td>
                    <td class="statistics-value"><?= $statisticsPercentage($statisticsProfile['passed'] ?? 0) ?></td>
                </tr>
                <tr>
                    <td></td>
                    <td><?= __('academic') ?></td>
                    <td class="statistics-value"><?= htmlspecialchars((string) ($statisticsDiscipline['warning_work'] ?? '')) ?></td>
                    <td><?= __('very_good') ?></td>
                    <td class="statistics-value"><?= $statisticsPercentage($statisticsProfile['passed'] ?? 0) ?></td>
                    <td rowspan="2">% <?= __('passed') ?></td>
                    <td rowspan="2" class="statistics-value"><?= formatSimple($classStats['success_rate'] ?? 0) ?>%</td>
                </tr>
                <tr>
                    <td rowspan="4" class="statistics-label"><?= __('dismissed') ?></td>
                    <td><?= __('conduct') ?></td>
                    <td class="statistics-value"><?= htmlspecialchars((string) ($statisticsDiscipline['blame_conduct'] ?? '')) ?></td>
                    <td><?= __('excellent') ?></td>
                    <td class="statistics-value"><?= $statisticsPercentage($statisticsProfile['passed'] ?? 0) ?></td>
                </tr>
                <tr>
                    <td><?= __('absences') ?></td>
                    <td class="statistics-value"><?= htmlspecialchars((string) ($statisticsDiscipline['warning_conduct'] ?? '')) ?></td>
                    <td></td>
                    <td class="statistics-value"></td>
                    <td></td>
                    <td class="statistics-value"></td>
                </tr>
                <tr>
                    <td><?= __('fees_owing') ?></td>
                    <td class="statistics-value"></td>
                    <td></td>
                    <td class="statistics-value"></td>
                    <td></td>
                    <td class="statistics-value"></td>
                </tr>
                <tr>
                    <td><?= __('academic') ?></td>
                    <td class="statistics-value"></td>
                    <td></td>
                    <td class="statistics-value"></td>
                    <td></td>
                    <td class="statistics-value"></td>
                </tr>
    </tr>

    <?php if (($bulletinPeriod ?? '') !== 'annuel'): ?>
    <tr>
                <td class="statistics-footer-label" colspan="2"><?= __('fees_owing') ?>:</td><td colspan="5">&nbsp;</td>
    </tr>
    <?php endif; ?>
    <?php if (($bulletinPeriod ?? '') === 'annuel'): ?>
    <tr>
                <td class="statistics-label" colspan="2" rowspan="3"><?= function_exists('mb_convert_case') ? mb_convert_case(__('decision_end_of_year_title'), MB_CASE_TITLE, 'UTF-8') : ucwords(strtolower(__('decision_end_of_year_title'))) ?></td>
                <td class="statistics-footer-label" colspan="2"><?= __('promoted_to') ?> :</td><td colspan="3">................</td>
    </tr>
    <tr>
                <td class="statistics-footer-label" colspan="2"><?= __('authorized_to_repeat') ?> :</td><td colspan="3">................</td>
    </tr>
    <tr>
                <td class="statistics-footer-label" colspan="2"><?= __('must_recompose') ?> :</td><td colspan="3">................</td>
    </tr>
    <?php endif; ?>
    <?php if (($bulletinPeriod ?? '') !== 'annuel'): ?>
    <tr>
                <td class="statistics-footer-label" colspan="2"><?= __('parent_name_signature') ?>:</td><td colspan="5">&nbsp;</td>
    </tr>
    <tr>
                <td class="statistics-footer-label" colspan="2"><?= __('next_term_begins') ?></td><td colspan="5">&nbsp;</td>
    </tr>
    <?php endif; ?>
    <?php if (($bulletinPeriod ?? '') === 'annuel'): ?>
    <?php endif; ?>
</table>
</div>