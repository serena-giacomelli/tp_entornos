# Script para reemplazar / por / en todos los archivos PHP
# Ejecutar en PowerShell desde la carpeta del proyecto

$rutaProyecto = "C:\xampp\htdocs\tp_eg"
$buscar = "/"
$reemplazar = "/"

# Obtener todos los archivos PHP recursivamente
$archivos = Get-ChildItem -Path $rutaProyecto -Recurse -Include *.php

Write-Host "Buscando archivos PHP..." -ForegroundColor Yellow
Write-Host "Total de archivos encontrados: $($archivos.Count)" -ForegroundColor Cyan

$contador = 0

foreach ($archivo in $archivos) {
    $contenido = Get-Content -Path $archivo.FullName -Raw -Encoding UTF8
    
    if ($contenido -match [regex]::Escape($buscar)) {
        $nuevoContenido = $contenido -replace [regex]::Escape($buscar), $reemplazar
        Set-Content -Path $archivo.FullName -Value $nuevoContenido -Encoding UTF8 -NoNewline
        Write-Host "✓ Modificado: $($archivo.Name)" -ForegroundColor Green
        $contador++
    }
}

Write-Host "`n========================================" -ForegroundColor Cyan
Write-Host "Proceso completado!" -ForegroundColor Green
Write-Host "Archivos modificados: $contador" -ForegroundColor Yellow
Write-Host "========================================`n" -ForegroundColor Cyan
Write-Host "Ahora puedes volver a subir los archivos al servidor." -ForegroundColor White

pause
