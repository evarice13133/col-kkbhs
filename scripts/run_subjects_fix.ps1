# Script PowerShell pour exécuter la migration non destructive et collecter les vérifications
# Usage: exécuter depuis la racine du projet PowerShell en tant qu'utilisateur ayant accès à PHP et à la base.

$projectRoot = Split-Path -Path $MyInvocation.MyCommand.Definition -Parent
Set-Location $projectRoot

$log = Join-Path $projectRoot "logs\subjects_fix_$(Get-Date -Format 'yyyyMMdd_HHmmss').log"
New-Item -ItemType Directory -Path (Split-Path $log) -Force | Out-Null

function Log { param($m) "$(Get-Date -Format o) - $m" | Tee-Object -FilePath $log -Append }

Log "Début du script d'exécution de la migration subjects"

# Vérifier présence de php
$php = & php -v 2>$null
if ($LASTEXITCODE -ne 0) {
    Log "PHP CLI introuvable. Veuillez installer PHP CLI et réessayer."
    Write-Host "PHP CLI introuvable. Installer PHP et relancer." -ForegroundColor Red
    exit 1
}
Log "PHP CLI trouvé: $(php -v | Select-Object -First 1)"

# Exécuter la migration
$migration = Join-Path $projectRoot "migrations\20260826_fix_subjects_autoinc.php"
if (-not (Test-Path $migration)) {
    Log "Migration introuvable: $migration"
    Write-Host "Migration non trouvée." -ForegroundColor Red
    exit 1
}

Log "Exécution de la migration: $migration"
& php $migration 2>&1 | Tee-Object -FilePath $log -Append
$exit = $LASTEXITCODE
if ($exit -ne 0) {
    Log "Migration échouée (exit code $exit). Voir log pour détails."
    Write-Host "Migration échouée. Consulter $log" -ForegroundColor Yellow
    exit $exit
}
Log "Migration terminée. Poursuite des vérifications SQL (requiert accès MySQL via CLI)."

# Vérifications via MySQL CLI si disponible
$mysql = Get-Command mysql -ErrorAction SilentlyContinue
if (-not $mysql) {
    Log "MySQL CLI introuvable. seules les actions côté PHP ont été exécutées."
    Write-Host "MySQL CLI introuvable. Les vérifications SQL doivent être exécutées manuellement." -ForegroundColor Yellow
    exit 0
}

# Demander paramètres DB
$dbHost = Read-Host "DB Host (ex: localhost)"
$dbName = Read-Host "DB Name"
$dbUser = Read-Host "DB User"
$dbPass = Read-Host "DB Pass (sera demandé en clair)"

# Exécuter vérifications
$queries = @(
"SHOW CREATE TABLE subjects;",
"SELECT COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT, EXTRA FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '$dbName' AND TABLE_NAME = 'subjects';",
"SELECT MAX(id) AS max_id, COUNT(*) AS total_rows FROM subjects;",
"SELECT id, COUNT(*) AS cnt FROM subjects GROUP BY id HAVING cnt > 1;",
"SELECT TABLE_NAME, COLUMN_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE REFERENCED_TABLE_SCHEMA = '$dbName' AND REFERENCED_TABLE_NAME = 'subjects';"
)

foreach ($q in $queries) {
    Log "-- SQL: $q"
    & mysql -h $dbHost -u $dbUser -p$dbPass -D $dbName -e $q 2>&1 | Tee-Object -FilePath $log -Append
}

Log "Vérifications terminées. Consulter le log: $log"
Write-Host "Terminé. Voir $log" -ForegroundColor Green
