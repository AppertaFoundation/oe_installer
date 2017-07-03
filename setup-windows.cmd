@echo off

echo ***************************************************************
echo **** THIS SCRIPT MUST BE RUN AS ADMINISTRATOR FROM cmd.exe ****
echo ****                                                       ****
echo ****     Otherwise you will get multiple failures!!!       ****
echo ****                                                       ****
echo ****                                                       ****
echo ****                                                       ****
echo ****         This will take about 10 minutes               ****
echo ****                                                       ****
echo ****                                                       ****
echo ****       Reboot your computer when it is done            ****
echo ****                                                       ****
echo ***************************************************************


echo .
echo .
echo installing chocolatey package manager

SET DIR=%~dp0%

::download install.ps1
%systemroot%\System32\WindowsPowerShell\v1.0\powershell.exe -NoProfile -ExecutionPolicy Bypass -Command "((new-object net.webclient).DownloadFile('https://chocolatey.org/install.ps1','install.ps1'))"
::run installer
%systemroot%\System32\WindowsPowerShell\v1.0\powershell.exe -NoProfile -ExecutionPolicy Bypass -Command "& '%DIR%install.ps1' %*"
del install.ps1

goto :appinstall


:: ********************** Start of refresh PATH **********************
:refreshEnv

echo | set /p dummy="Refreshing environment variables from registry for cmd.exe. Please wait..."

goto main

:: Set one environment variable from registry key
:SetFromReg
    "%WinDir%\System32\Reg" QUERY "%~1" /v "%~2" > "%TEMP%\_envset.tmp" 2>NUL
    for /f "usebackq skip=2 tokens=2,*" %%A IN ("%TEMP%\_envset.tmp") do (
        echo/set "%~3=%%B"
    )
    goto :EOF

:: Get a list of environment variables from registry
:GetRegEnv
    "%WinDir%\System32\Reg" QUERY "%~1" > "%TEMP%\_envget.tmp"
    for /f "usebackq skip=2" %%A IN ("%TEMP%\_envget.tmp") do (
        if /I not "%%~A"=="Path" (
            call :SetFromReg "%~1" "%%~A" "%%~A"
        )
    )
    goto :EOF

:main
    echo/@echo off >"%TEMP%\_env.cmd"

    :: Slowly generating final file
    call :GetRegEnv "HKLM\System\CurrentControlSet\Control\Session Manager\Environment" >> "%TEMP%\_env.cmd"
    call :GetRegEnv "HKCU\Environment">>"%TEMP%\_env.cmd" >> "%TEMP%\_env.cmd"

    :: Special handling for PATH - mix both User and System
    call :SetFromReg "HKLM\System\CurrentControlSet\Control\Session Manager\Environment" Path Path_HKLM >> "%TEMP%\_env.cmd"
    call :SetFromReg "HKCU\Environment" Path Path_HKCU >> "%TEMP%\_env.cmd"

    :: Caution: do not insert space-chars before >> redirection sign
    echo/set "Path=%%Path_HKLM%%;%%Path_HKCU%%" >> "%TEMP%\_env.cmd"

    :: Cleanup
    del /f /q "%TEMP%\_envset.tmp" 2>nul
    del /f /q "%TEMP%\_envget.tmp" 2>nul

    :: capture user / architecture
    SET "OriginalUserName=%USERNAME%"
    SET "OriginalArchitecture=%PROCESSOR_ARCHITECTURE%"

    :: Set these variables
    call "%TEMP%\_env.cmd"

    :: reset user / architecture
    SET "USERNAME=%OriginalUserName%"
    SET "PROCESSOR_ARCHITECTURE=%OriginalArchitecture%"

    echo | set /p dummy="Finished."
    echo .

    goto :EOF

:: ******************* END of Refresh PATH ***************************


:appinstall

call :refreshEnv
REM %ALLUSERSPROFILE%\chocolatey\bin\Refreshenv.cmd

echo .
echo .
echo .
echo .
echo installing git for windows
echo .
echo .
echo .
echo .
echo .
choco upgrade git -y -params "/GitAndUnixToolsOnPath"


echo .
echo .
echo .
echo .
echo .
echo installing vagrant
echo .
echo .
echo .
echo .
echo .

choco upgrade vagrant -y

pause

echo .
echo .
echo .
echo .
echo .
echo .
echo installing VirtualBox
echo .
echo .
echo .
echo .
echo .
echo .
choco upgrade virtualbox -y
::-params "/NoExtensionPack"

pause

:: reload PATH
call :refreshEnv

echo .
echo .
echo .
echo .
echo .
echo Cloning Installer
echo .
echo .
echo .
echo .

cd \ && git clone https://github.com/openeyes/oe_installer openeyes

pause

echo .
echo .
echo .
echo .
echo .
echo .installing openeyes VM
echo .
echo ********************************************************
echo ***********  Accept the security pop-ups! **************
echo ********************************************************
echo .
echo .
echo .

cd \openeyes
vagrant up
pause
vagrant up

echo ***************************************************************
echo ****         YOU SHOULD REBOOT YOUR COMPUTER NOW           ****
echo ****                                                       ****
echo ****   To Start the OE VM open an Admin command prompt     ****
echo ****   and type: cd \openeyes                              ****
echo ****             vagrant up                                ****
echo ****                                                       ****
echo ****                                                       ****
echo ****   To Stop the OE VM open an Admin command prompt      ****
echo ****   and type: cd \openeyes                              ****
echo ****             vagrant halt                              ****
echo ****                                                       ****
echo ***************************************************************


pause
