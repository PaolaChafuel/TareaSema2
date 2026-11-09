@echo off
cd /d "%~dp0"
if not exist "C:\xampp\php\php.exe" (
 echo No se encontro PHP en C:\xampp\php\php.exe
 echo Instala XAMPP o configura la ruta de PHP.
 pause
 exit /b 1
)
echo Iniciando CRUD de Clientes en http://localhost:8000
start "" "http://localhost:8000"
"C:\xampp\php\php.exe" -S localhost:8000 -t public
pause
