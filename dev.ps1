<#
.SYNOPSIS
    Обвязка над docker compose для Windows — тот же набор команд, что в Makefile.

.EXAMPLE
    .\dev.ps1 init
    .\dev.ps1 artisan migrate:status
    .\dev.ps1 logs app
#>
[CmdletBinding()]
param(
    [Parameter(Position = 0)]
    [string]$Command = 'help',

    [Parameter(Position = 1, ValueFromRemainingArguments = $true)]
    [string[]]$Rest = @()
)

$ErrorActionPreference = 'Stop'
Set-Location -Path $PSScriptRoot

function Invoke-Compose {
    param([string[]]$ComposeArgs)
    # docker compose пишет прогресс («Network … Creating») в stderr. В Windows
    # PowerShell 5.1 при 'Stop' такая строка становится прерывающей ошибкой, как
    # только stderr не идёт прямо в консоль (CI, IDE, перенаправление). Успех
    # определяем по коду возврата, поэтому здесь stderr — просто вывод.
    $ErrorActionPreference = 'Continue'
    & docker compose @ComposeArgs
    if ($LASTEXITCODE -ne 0) { throw "docker compose завершился с кодом $LASTEXITCODE" }
}

function Get-EnvValue {
    param([string]$Key, [string]$Default)
    if (-not (Test-Path '.env')) { return $Default }
    $line = Select-String -Path '.env' -Pattern "^$Key=" -ErrorAction SilentlyContinue | Select-Object -First 1
    if ($null -eq $line) { return $Default }
    return ($line.Line -replace "^$Key=", '').Trim()
}

function Show-Help {
    @'
  init            Первый запуск: .env, сборка, установка Laravel, миграции
  build           Пересобрать образы
  up / down       Поднять / остановить окружение
  restart         Перезапустить
  ps / logs       Состояние / логи (logs app)
  sh              Шелл в контейнере приложения
  artisan ...     php artisan в контейнере
  composer ...    composer в контейнере
  npm ...         npm в контейнере node
  front           Поднять Vite dev server
  test            Прогнать тесты
  migrate / fresh / seed
  psql            Консоль PostgreSQL
  prod-build      Собрать боевой образ
  clean           Снести контейнеры вместе с томами
'@ | Write-Host
}

switch ($Command) {
    'help' { Show-Help }

    'init' {
        if (-not (Test-Path '.env')) {
            Copy-Item '.env.example' '.env'
            Write-Host 'создан .env из .env.example' -ForegroundColor Green
        }

        if (-not (Test-Path 'backend')) { $null = New-Item -ItemType Directory 'backend' }

        Invoke-Compose @('build')

        if (-not (Test-Path 'backend\artisan')) {
            Write-Host 'Laravel не найден — ставлю скелет в backend/' -ForegroundColor Cyan
            $bootstrap = 'composer create-project --prefer-dist --no-interaction laravel/laravel:^13.0 /tmp/skeleton && cp -rn /tmp/skeleton/. /var/www/html/ && rm -rf /tmp/skeleton'
            Invoke-Compose @('run', '--rm', '--no-deps', 'app', 'bash', '-lc', $bootstrap)
        }

        # Свежий клон: vendor/ и backend/.env в git не лежат.
        if (-not (Test-Path 'backend\.env')) {
            Copy-Item 'backend\.env.example' 'backend\.env'
            Write-Host 'создан backend/.env из backend/.env.example' -ForegroundColor Green
        }

        if (-not (Test-Path 'backend\vendor\autoload.php')) {
            Write-Host 'vendor/ пуст — ставлю зависимости' -ForegroundColor Cyan
            Invoke-Compose @('run', '--rm', '--no-deps', 'app', 'composer', 'install', '--no-interaction', '--prefer-dist')
        }

        if (-not (Select-String -Path 'backend\.env' -Pattern '^APP_KEY=.+' -Quiet)) {
            Write-Host 'генерирую APP_KEY' -ForegroundColor Cyan
            Invoke-Compose @('run', '--rm', '--no-deps', 'app', 'php', 'artisan', 'key:generate', '--force')
        }

        Invoke-Compose @('up', '-d')
        Write-Host 'жду готовности приложения…'
        Start-Sleep -Seconds 10
        Invoke-Compose @('exec', 'app', 'php', 'artisan', 'migrate', '--force')

        $appPort = Get-EnvValue 'APP_PORT' '8080'
        $vitePort = Get-EnvValue 'VITE_PORT' '5173'
        $dbPort = Get-EnvValue 'DB_PORT_FORWARD' '5432'
        Write-Host ''
        Write-Host "  приложение   http://localhost:$appPort" -ForegroundColor Green
        Write-Host "  фронтенд     .\dev.ps1 front  ->  http://localhost:$vitePort" -ForegroundColor Green
        Write-Host "  PostgreSQL   localhost:$dbPort" -ForegroundColor Green
    }

    'build'   { Invoke-Compose (@('build') + $Rest) }
    'up'      { Invoke-Compose @('up', '-d') }
    'down'    { Invoke-Compose @('down') }
    'restart' { Invoke-Compose @('restart') }
    'ps'      { Invoke-Compose @('ps') }
    'logs'    { Invoke-Compose (@('logs', '-f', '--tail=100') + $Rest) }
    'sh'      { Invoke-Compose @('exec', 'app', 'bash') }
    'artisan' { Invoke-Compose (@('exec', 'app', 'php', 'artisan') + $Rest) }
    'composer'{ Invoke-Compose (@('exec', 'app', 'composer') + $Rest) }
    'npm'     { Invoke-Compose (@('run', '--rm', '--no-deps', 'node', 'npm') + $Rest) }

    'front' {
        Invoke-Compose @('--profile', 'frontend', 'up', '-d', 'node')
        Invoke-Compose @('--profile', 'frontend', 'logs', '-f', 'node')
    }

    'test' {
        $db = Get-EnvValue 'DB_DATABASE' 'fiberglass'
        Invoke-Compose (@('exec', '-e', 'APP_ENV=testing', '-e', "DB_DATABASE=${db}_test", 'app', 'php', 'artisan', 'test') + $Rest)
    }

    'migrate' { Invoke-Compose @('exec', 'app', 'php', 'artisan', 'migrate', '--force') }
    'fresh'   { Invoke-Compose @('exec', 'app', 'php', 'artisan', 'migrate:fresh', '--seed') }
    'seed'    { Invoke-Compose @('exec', 'app', 'php', 'artisan', 'db:seed') }


    'psql' {
        $user = Get-EnvValue 'DB_USERNAME' 'fiberglass'
        $db = Get-EnvValue 'DB_DATABASE' 'fiberglass'
        Invoke-Compose @('exec', 'postgres', 'psql', '-U', $user, '-d', $db)
    }

    'prod-build' {
        $env:PHP_TARGET = 'prod'
        try { Invoke-Compose @('build', 'app') } finally { Remove-Item Env:PHP_TARGET -ErrorAction SilentlyContinue }
    }

    'clean' { Invoke-Compose @('down', '-v', '--remove-orphans') }

    default {
        Write-Host "неизвестная команда: $Command" -ForegroundColor Red
        Show-Help
        exit 1
    }
}
