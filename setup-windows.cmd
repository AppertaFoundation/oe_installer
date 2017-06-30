REM install chocolatey package manager using powershell
installChocolatey.cmd
::cmd.exe /C "@"%SystemRoot%\System32\WindowsPowerShell\v1.0\powershell.exe" -NoProfile -ExecutionPolicy Bypass -Command "iex ((New-Object System.Net.WebClient).DownloadString('https://chocolatey.org/install.ps1'))" && SET "PATH=%PATH%;%ALLUSERSPROFILE%\chocolatey\bin""

REM install git for windows and set path
:: cmd.exe /C "choco install git -y -params "/GitAndUnixToolsOnPath""
choco install git -y -params "/GitAndUnixToolsOnPath"

REM install Virtual Box
::cmd.exe /C "choco upgrade virtualbox -y -params "/NoExtensionPack""
choco upgrade virtualbox -y -params "/NoExtensionPack"

REM Clone Installer
cd \
md openeyes
git clone https://github.com/openeyes/oe_installer openeyes

REM install oe box
cd openeyes
vagrant up
vagrant up
