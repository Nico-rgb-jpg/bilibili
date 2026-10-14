@echo off
chcp 65001 >nul
title Bilibili
cd /d "%~dp0"

where php >nul 2>nul
if errorlevel 1 (
    if exist "C:\xampp\php\php.exe" (
        set "PHP=C:\xampp\php\php.exe"
    ) else (
        echo.
        echo  PHP est introuvable sur cet ordinateur.
        echo.
        echo  Solution la plus simple : installe XAMPP ^(https://www.apachefriends.org^)
        echo  dans C:\xampp, puis relance ce fichier.
        echo.
        pause
        exit /b 1
    )
) else (
    set "PHP=php"
)

echo.
echo  Bilibili demarre sur http://localhost:8000
echo  Laisse cette fenetre ouverte. Ferme-la pour arreter le site.
echo.
start "" http://localhost:8000
"%PHP%" -S localhost:8000 -t public
pause
