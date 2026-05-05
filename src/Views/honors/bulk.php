<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= __('honor_roll_title') ?> - <?= htmlspecialchars($classInfo['nom']) ?></title>
    <style>
        @page {
            size: A4 landscape;
            margin: 5mm;
        }
        body {
            margin: 0;
            padding: 0;
            background-color: #f0f0f0;
            width: 100%;
        }
        @media print {
            body { background-color: #fff; }
            .no-print { display: none !important; }
        }
        .no-print {
            background: #1a1a2e;
            color: #fff;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 1000;
            font-family: sans-serif;
        }
        .btn {
            padding: 8px 16px;
            background: #0d6efd;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            font-size: 13px;
            font-weight: bold;
        }
        .btn-secondary {
            background: rgba(255,255,255,0.2);
        }
    </style>
</head>
<body>
    <div class="no-print">
        <div>
            <strong><?= __('honor_roll_generation') ?></strong> - <?= htmlspecialchars($classInfo['nom']) ?> (<?= count($honors) ?> <?= __('students_short') ?>)
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="/honors?class_id=<?= (int) $classId ?>" class="btn btn-secondary"><?= __('back') ?></a>
            <button class="btn" onclick="window.print()"><?= __('pv_print_btn') ?></button>
        </div>
    </div>

    <?php $rank = 1; foreach ($honors as $studentId => $student): ?>
        <?php 
            if (!isset($student['id'])) {
                $student['id'] = $studentId;
            }
            $student['rank'] = $rank++;
            include __DIR__ . '/certificate.php'; 
        ?>
    <?php endforeach; ?>
</body>
</html>
