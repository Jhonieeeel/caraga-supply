#Requires -RunAsAdministrator

$ErrorActionPreference = 'Continue'
$env:Path = [System.Environment]::GetEnvironmentVariable("Path","Machine") + ";" + [System.Environment]::GetEnvironmentVariable("Path","User")

$ProjectPath = "C:\Users\DT-AS26-MIN013-UAYAN\AppData\Local\Programs\Warp\caraga-supply"
$MysqlBin    = "C:\Program Files\MySQL\MySQL Server 8.4\bin"
$MysqlData   = "$env:USERPROFILE\mysql-data"
$NssmExe     = "C:\Users\DT-AS26-MIN013-UAYAN\AppData\Local\Microsoft\WinGet\Packages\NSSM.NSSM_Microsoft.Winget.Source_8wekyb3d8bbwe\nssm-2.24-101-g897c7ad\win64\nssm.exe"
$PhpExe      = "C:\Users\DT-AS26-MIN013-UAYAN\AppData\Local\Microsoft\WinGet\Packages\PHP.PHP.8.4_Microsoft.Winget.Source_8wekyb3d8bbwe\php.exe"
$LogFile     = "$ProjectPath\deploy\install-services.log"

Start-Transcript -Path $LogFile -Force | Out-Null

Write-Host "== Stopping any manually-started MySQL process ==" -ForegroundColor Cyan
Get-CimInstance Win32_Process -Filter "Name = 'mysqld.exe'" | ForEach-Object {
    Write-Host "Stopping mysqld.exe (PID $($_.ProcessId))"
    Stop-Process -Id $_.ProcessId -Force -ErrorAction SilentlyContinue
}
Start-Sleep -Seconds 2

Write-Host "== Granting the service account access to the MySQL data directory ==" -ForegroundColor Cyan
icacls "$MysqlData" /grant "NT AUTHORITY\SYSTEM:(OI)(CI)F" /T | Out-Null

Write-Host "== Installing MySQL as a Windows Service ==" -ForegroundColor Cyan
$existing = Get-Service -Name "MySQL84" -ErrorAction SilentlyContinue
if ($existing) {
    Write-Host "MySQL84 service already exists, skipping install."
} else {
    & "$MysqlBin\mysqld.exe" --install MySQL84 --datadir="$MysqlData"
}
sc.exe config MySQL84 start= auto | Out-Null
Start-Service -Name "MySQL84" -ErrorAction SilentlyContinue
Start-Sleep -Seconds 2
$mysqlService = Get-Service -Name "MySQL84" -ErrorAction SilentlyContinue
if ($mysqlService) {
    Write-Host "MySQL84 service status: $($mysqlService.Status)" -ForegroundColor Green
} else {
    Write-Host "ERROR: MySQL84 service was not created." -ForegroundColor Red
}

Write-Host "== Installing the Laravel app as a Windows Service (via NSSM) ==" -ForegroundColor Cyan
$serviceName = "CaragaSupply"

if (Get-Service -Name $serviceName -ErrorAction SilentlyContinue) {
    Write-Host "Existing $serviceName service found, stopping and removing it first."
    & $NssmExe stop $serviceName
    Start-Sleep -Seconds 2
    & $NssmExe remove $serviceName confirm
    Start-Sleep -Seconds 1
}

Write-Host "Installing service..."
& $NssmExe install $serviceName $PhpExe "artisan serve --host=127.0.0.1 --port=8000"
& $NssmExe set $serviceName AppDirectory $ProjectPath
& $NssmExe set $serviceName AppStdout "$ProjectPath\storage\logs\service-stdout.log"
& $NssmExe set $serviceName AppStderr "$ProjectPath\storage\logs\service-stderr.log"
& $NssmExe set $serviceName Start SERVICE_AUTO_START
& $NssmExe set $serviceName DependOnService MySQL84

$installedService = Get-Service -Name $serviceName -ErrorAction SilentlyContinue
if (-not $installedService) {
    Write-Host "ERROR: $serviceName service was not created by NSSM. Aborting before start." -ForegroundColor Red
} else {
    Start-Service -Name $serviceName -ErrorAction SilentlyContinue
    Start-Sleep -Seconds 2
    $installedService = Get-Service -Name $serviceName -ErrorAction SilentlyContinue
    Write-Host "$serviceName service status: $($installedService.Status)" -ForegroundColor Green
}

Write-Host "== Verifying the app responds ==" -ForegroundColor Cyan
Start-Sleep -Seconds 3
try {
    $r = Invoke-WebRequest -Uri "http://127.0.0.1:8000/" -UseBasicParsing -TimeoutSec 10
    Write-Host "App responded with status $($r.StatusCode)" -ForegroundColor Green
} catch {
    Write-Host "App did not respond yet: $($_.Exception.Message)" -ForegroundColor Yellow
    Write-Host "Check logs at $ProjectPath\storage\logs\service-stderr.log"
}

Write-Host ""
Write-Host "== Done ==" -ForegroundColor Green
Write-Host "Log written to: $LogFile"
Write-Host "App: http://127.0.0.1:8000"
Write-Host ""
Write-Host "Manage them with:"
Write-Host "  Get-Service MySQL84, CaragaSupply"
Write-Host "  Stop-Service CaragaSupply ; Start-Service CaragaSupply"
Write-Host "  Or via services.msc"

Stop-Transcript | Out-Null
