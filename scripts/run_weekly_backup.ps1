param(
    [string]$PhpPath = "php"
)

$scriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path
$projectRoot = Split-Path -Parent $scriptDir
$phpScript = Join-Path $scriptDir "run_weekly_backup.php"

Push-Location $projectRoot
try {
    & $PhpPath $phpScript
    exit $LASTEXITCODE
}
finally {
    Pop-Location
}
