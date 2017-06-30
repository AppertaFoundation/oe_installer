cmd.exe /C "@"%SystemRoot%\System32\WindowsPowerShell\v1.0\powershell.exe" -NoProfile -ExecutionPolicy Bypass -Command "iex ((New-Object System.Net.WebClient).DownloadString('https://chocolatey.org/install.ps1'))" && SET "PATH=%PATH%;%ALLUSERSPROFILE%\chocolatey\bin""
cmd.exe /C "choco install git -y -params "/GitAndUnixToolsOnPath""
cmd.exe /C "choco upgrade virtualbox -y -params "/NoExtensionPack""
