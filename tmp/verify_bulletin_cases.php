<?php
function __($k) {
    $map = [
        'subjects_column' => 'Matières',
        'competences' => 'Compétences',
        'average_short' => 'Moy',
        'coef' => 'Coef',
        'score' => 'Score',
        'rank' => 'Rang',
        'appreciation' => 'Appréciation',
        'teacher_signature' => 'Enseignant',
        'test_1' => 'SEQ 1',
        'test_2' => 'SEQ 2',
        'evaluations' => 'Évaluations',
        'first_term' => 'Premier trimestre',
        'second_term' => 'Deuxième trimestre',
        'third_term' => 'Troisième trimestre',
        'trimester' => 'Trimestre',
        'subjects_passed' => 'Matières validées / sur',
        'student_avg' => 'Moyenne de l\'élève',
        'terms_results' => 'Résultats du trimestre',
        'group_default' => 'Général',
        'subtotal' => 'Sous-total',
        'grand_total' => 'TOTAL GÉNÉRAL',
        'evaluation' => 'Évaluation',
        'discipline' => 'Discipline',
        'class_council_decision' => 'Décision du conseil de classe',
        'class_performance' => 'Performance de la classe',
        'principal_remarks_sign' => 'Remarques / signature du Principal',
        'delays' => 'Retards',
        'absence_total_short' => 'Abs. totales',
        'punishment' => 'Punishment',
        'warnings' => 'Warnings',
        'dismissed' => 'Dismissed',
        'late_count' => 'Late count',
        'total_absences' => 'Total absences',
        'sanctions' => 'Sanctions',
        'consignes' => 'Consignes',
        'exclusions' => 'Exclusions',
        'good' => 'Bon',
        'very_good' => 'Très bien',
        'excellent' => 'Excellent',
    ];
    return $map[$k] ?? $k;
}
function formatNote($val) { return $val === null ? '-' : number_format((float)$val,2,',',' '); }
function formatSimple($val) { return $val === null ? '-' : number_format((float)$val,2,',',' '); }

$cases = [
    ['name'=>'term1','termAverages'=>[12.5],'termRanks'=>[2],'seqAverages'=>[12.5],'seqRanks'=>[2],'term'=>(int)1],
    ['name'=>'term1_2','termAverages'=>[12.5,11.3],'termRanks'=>[2,4],'seqAverages'=>[12.5,11.3],'seqRanks'=>[2,4],'term'=>(int)1],
    ['name'=>'term1_2_3','termAverages'=>[12.5,11.3,13.2],'termRanks'=>[2,4,1],'seqAverages'=>[12.5,11.3,13.2],'seqRanks'=>[2,4,1],'term'=>(int)1],
];

foreach ($cases as $case) {
    $groupedRows = [['label'=>'Matières','rows'=>[]]];
    $classStats = ['profile'=>['total'=>1,'unclassified'=>0,'below'=>0,'middle'=>0,'passed'=>1]];
    $termAverages = $case['termAverages'];
    $termRanks = $case['termRanks'];
    $seqAverages = $case['seqAverages'];
    $seqRanks = $case['seqRanks'];
    $term = $case['term'];
    $bulletinPeriod = 'trimestre';
    $termSequences = [['label'=>'S1','code'=>'SEQ 1'],['label'=>'S2','code'=>'SEQ 2']];
    $globalAppreciation = 'Très bon trimestre';
    $disciplineValues = ['late_count' => 0, 'warning_work' => 'X', 'blame_conduct' => '00', 'conduct' => '—', 'consignes' => 0];
    $councilDecision = 'Assez bien';
    $classMax = 15.5; $classAverage = 12.3; $classSuccessRate = 76.5; $disciplineJustified = 0; $disciplineUnjustified = 0; $disciplineLate = 0; $disciplinePunishment = 0; $globalAppreciation = 'Très bon trimestre';
    ob_start();
    include 'src/Views/bulletins/bulletin_grades_table.php';
    $html = ob_get_clean();
    echo $case['name'] . ':' . substr_count($html, 'colspan="2"') . '|' . substr_count($html, 'Premier trimestre') . '|' . substr_count($html, 'Deuxième trimestre') . '|' . substr_count($html, 'Troisième trimestre') . PHP_EOL;
}
