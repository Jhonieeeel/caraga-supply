#Requires -RunAsAdministrator

# Lets this PC open the app at http://ocdcaragasupply.com instead of the IP.
# Run it once on the server and on every PC that should use the name
# (or add the same record to the office router/DNS and skip the PCs).

param(
    [string]$ServerIp = "10.20.2.39",
    [string]$Domain   = "ocdcaragasupply.com"
)

$hosts = "$env:SystemRoot\System32\drivers\etc\hosts"
$entry = "$ServerIp`t$Domain www.$Domain"

$lines = Get-Content $hosts | Where-Object { $_ -notmatch "\s(www\.)?$([regex]::Escape($Domain))(\s|$)" }
Set-Content -Path $hosts -Value ($lines + $entry) -Encoding ascii
Write-Host "hosts: $entry" -ForegroundColor Green

ipconfig /flushdns | Out-Null

# On the server itself, reload Apache so it picks up the new ServerName.
if (Get-Service -Name "Apache2.4" -ErrorAction SilentlyContinue) {
    Restart-Service -Name "Apache2.4"
    Write-Host "Apache restarted: $((Get-Service Apache2.4).Status)" -ForegroundColor Green
}

Write-Host "Open http://$Domain"
