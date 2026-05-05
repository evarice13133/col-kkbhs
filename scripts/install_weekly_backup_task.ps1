param(
    [string]$TaskName = "NotesMaster Weekly Backup",
    [string]$PhpPath = "php",
    [string]$DayOfWeek = "Sunday",
    [string]$Time = "02:00"
)

$scriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path
$runner = Join-Path $scriptDir "run_weekly_backup.ps1"
$actionArgs = "-NoProfile -ExecutionPolicy Bypass -File `"$runner`" -PhpPath `"$PhpPath`""

$trigger = New-ScheduledTaskTrigger -Weekly -DaysOfWeek $DayOfWeek -At $Time
$action = New-ScheduledTaskAction -Execute "powershell.exe" -Argument $actionArgs

if (Get-ScheduledTask -TaskName $TaskName -ErrorAction SilentlyContinue) {
    Unregister-ScheduledTask -TaskName $TaskName -Confirm:$false
}

Register-ScheduledTask -TaskName $TaskName -Action $action -Trigger $trigger -Description "Runs the NotesMaster weekly database backup and GitHub synchronization."
Write-Host "Scheduled task '$TaskName' installed for $DayOfWeek at $Time."
