@ECHO OFF
REM Read .env settings
@SET projectRoot=
for /f "delims== tokens=1,2" %%G in (%~dp0%projectRoot%.env) do set %%G=%%H

docker cp liquid_devbox_php_%COMPOSE_PROJECT_NAME%:/windows/unison.exe .
docker cp liquid_devbox_php_%COMPOSE_PROJECT_NAME%:/windows/unison-fsmonitor.exe .

REM FOR /f "delims=" %%A IN ('docker port magento2devbox_web_%COMPOSE_PROJECT_NAME% 5000') DO SET "CMD_OUTPUT=%%A"
REM FOR /f "tokens=1,* delims=:" %%A IN ("%CMD_OUTPUT%") DO SET "UNISON_PORT=%%B"

@SET UNISON_PORT=30%PROJECT_ID%5
@SET LOCAL_ROOT=%~dp0%projectRoot%
@SET REMOTE_ROOT=socket://localhost:%UNISON_PORT%//var/www/liquid

echo %LOCAL_ROOT%
@SET IGNORE=

rem Magento files not worth pulling locally.
@REM @SET IGNORE=%IGNORE% -ignore "Path var/cache"
@REM @SET IGNORE=%IGNORE% -ignore "Path var/composer_home"
@REM @SET IGNORE=%IGNORE% -ignore "Path var/log"
@REM @SET IGNORE=%IGNORE% -ignore "Path var/page_cache"
@REM @SET IGNORE=%IGNORE% -ignore "Path var/session"
@REM @SET IGNORE=%IGNORE% -ignore "Path var/tmp"
@REM @SET IGNORE=%IGNORE% -ignore "Path var/.setup_cronjob_status"
@REM @SET IGNORE=%IGNORE% -ignore "Path var/.update_cronjob_status"
@REM @SET IGNORE=%IGNORE% -ignore "Path pub/media/catalog/product/cache"
@REM @SET IGNORE=%IGNORE% -ignore "Path pub/static/adminhtml"
@REM @SET IGNORE=%IGNORE% -ignore "Path pub/static/frontend"
@REM @SET IGNORE=%IGNORE% -ignore "Path var/view_preprocessed"
@REM @SET IGNORE=%IGNORE% -ignore "Path generated"
REM @SET IGNORE=%IGNORE% -ignore "Path app/etc/env.php"

REM @SET IGNORE=%IGNORE% -ignore "Path dev/tests"
@SET IGNORE=%IGNORE% -ignore "Path node_modules"
rem Other files not worth pushing to the container.
@SET IGNORE=%IGNORE% -ignore "Path .git"
@SET IGNORE=%IGNORE% -ignore "Path vendor/*/.git"
@SET IGNORE=%IGNORE% -ignore "Path vendor/*/*/.git"
@SET IGNORE=%IGNORE% -ignore "Path vendor/*/*/*/.git"
@SET IGNORE=%IGNORE% -ignore "Path vendor/*/.git/*"
@SET IGNORE=%IGNORE% -ignore "Path .gitignore"
@SET IGNORE=%IGNORE% -ignore "Path .gitattributes"
@SET IGNORE=%IGNORE% -ignore "Path .idea"
@SET IGNORE=%IGNORE% -ignore "Path unison.exe"
@SET IGNORE=%IGNORE% -ignore "Path unison-fsmonitor.exe"
@SET IGNORE=%IGNORE% -ignore "Name {.*.swp}"
@SET IGNORE=%IGNORE% -ignore "Name {.unison.*}"
@SET IGNORE=%IGNORE% -ignore "Path {/dev/tests/}"

@set UNISONARGS=%LOCAL_ROOT% %REMOTE_ROOT% -prefer %LOCAL_ROOT% -preferpartial "Path var -> %REMOTE_ROOT%" -auto -batch -fastcheck=true -ui=graphic %IGNORE%

rem *** Check for sync readiness ***
SET loopcount=1000
@REM :loop_sync_ready
@REM     IF EXIST %~dp0..\shared\state\enable_sync GOTO exitloop_sync_ready
@REM     echo 'enable sync does not exist'
@REM     timeout 5
@REM     @SET /a loopcount=loopcount-1
@REM     @IF %loopcount%==0 GOTO exitloop_sync_ready
@REM     @GOTO loop_sync_ready
@REM
@REM :exitloop_sync_ready

IF NOT EXIST  %LOCAL_ROOT%/vendor (
   rem **** Pulling files from container (faster quiet mode) ****
   ~dp0%projectRoot%unison %UNISONARGS% -silent >NUL:
)

rem **** Entering file watch mode ****
:loop_sync
    %~dp0%projectRoot%unison %UNISONARGS% -repeat watch
    timeout 5
    @GOTO loop_sync
PAUSE
