@echo off
:: Double-click on any office PC so it can open http://ocdcaragasupply.com
:: (points the name at the supply server, 10.20.2.39). Safe to run twice.

set "SERVER_IP=10.20.2.39"
set "DOMAIN=ocdcaragasupply.com"

:: Re-launch as administrator if needed (Windows will ask "Yes"/admin password).
net session >nul 2>&1
if %errorlevel% neq 0 (
    powershell -NoProfile -Command "Start-Process -FilePath '%~f0' -Verb RunAs"
    exit /b
)

powershell -NoProfile -ExecutionPolicy Bypass -Command ^
  "$h = \"$env:SystemRoot\System32\drivers\etc\hosts\";" ^
  "$keep = Get-Content $h | Where-Object { $_ -notmatch '\s(www\.)?%DOMAIN:.=\.%(\s|$)' };" ^
  "Set-Content -Path $h -Value ($keep + \"%SERVER_IP%`t%DOMAIN% www.%DOMAIN%\") -Encoding ascii;" ^
  "ipconfig /flushdns | Out-Null"

if %errorlevel% neq 0 (
    echo.
    echo FAILED - the hosts file could not be changed. Check antivirus or admin rights.
) else (
    echo.
    echo Done. Open http://%DOMAIN% in the browser.
)
echo.
pause
