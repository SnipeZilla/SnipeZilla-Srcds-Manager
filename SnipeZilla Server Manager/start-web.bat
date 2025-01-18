
CD /d "%~dp0web"
START /MIN "SnipeZilla Web Server" "%~dp0bin\php.exe" -c "%~dp0bin\php.ini" -S localhost:8888
START http://localhost:8888/index.php
EXIT
