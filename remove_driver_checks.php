<?php

// Script para eliminar todas las verificaciones de driver MySQL de las migraciones

$migrations = glob('database/migrations/*.php');
$tenantMigrations = glob('database/migrations/tenant/*.php');
$allMigrations = array_merge($migrations, $tenantMigrations);

$patterns = [
    // Patrón 1: Variable $driver y condicionales completos
    [
        'search' => '/\s*\$driver = \$connection->getDriverName\(\);.*?(?=\n\s{4}\}|\n\s{8}\}\);)/s',
        'replace' => ''
    ],
    // Patrón 2: Condicional if con driver === mysql
    [
        'search' => '/\n\s*if \(\$driver === [\'"]mysql[\'"]\) \{\s*\n/s',
        'replace' => "\n"
    ],
    // Patrón 3: elseif con driver === mysql
    [
        'search' => '/\n\s*\} elseif \(\$driver === [\'"]mysql[\'"]\) \{\s*\n/s',
        'replace' => "\n"
    ],
    // Patrón 4: else if con driver === mysql
    [
        'search' => '/\n\s*\} else if \(\$driver === [\'"]mysql[\'"]\) \{\s*\n/s',
        'replace' => "\n"
    ],
    // Patrón 5: Cerrar llaves del if/elseif
    [
        'search' => '/\n\s*\}\s*\n\s*\}\);/s',
        'replace' => "\n        });"
    ],
];

$changed = [];

foreach ($allMigrations as $file) {
    $content = file_get_contents($file);
    $original = $content;

    // Buscar si contiene verificaciones de driver
    if (strpos($content, 'getDriverName') === false && strpos($content, "=== 'mysql'") === false) {
        continue;
    }

    // Aplicar transformaciones manuales más específicas
    $content = transformMigration($content);

    if ($content !== $original) {
        file_put_contents($file, $content);
        $changed[] = $file;
        echo "✓ " . basename($file) . "\n";
    }
}

echo "\n" . count($changed) . " archivos modificados\n";

function transformMigration($content) {
    // Patrón: Eliminar todo el bloque de $driver = ... hasta el cierre del Schema o Blueprint

    // Caso 1: Personal access tokens, jobs, permissions, etc (fulltext indexes)
    $content = preg_replace_callback(
        '/(\s+)(\/\/ [^\n]+\n)?\s*\$driver = \$connection->getDriverName\(\);\s*\n\s*\n\s*if \(\$driver === [\'"]sqlite[\'"]\) \{.*?\} elseif \(\$driver === [\'"]mysql[\'"]\) \{\s*\n(.*?)\n\s+\}\s*\n(\s+\}\);)/s',
        function($matches) {
            // Mantener solo el código MySQL, sin condicionales
            $indent = $matches[1];
            $mysqlCode = $matches[2];
            $closing = $matches[3];
            return $indent . trim($mysqlCode) . "\n" . $closing;
        },
        $content
    );

    // Caso 2: Force string migrations con DB::getDriverName()
    $content = preg_replace(
        '/\s*\$driver = DB::getDriverName\(\);\s*\n\s*if \(\$driver === [\'"]mysql[\'"]\) \{\s*\n/',
        "\n",
        $content
    );

    // Caso 3: Schema::getConnection()->getDriverName()
    $content = preg_replace(
        '/\s*\$driver = Schema::getConnection\(\)->getDriverName\(\);\s*\n.*?if.*?mysql.*?\{\s*\n/',
        "\n",
        $content
    );

    // Eliminar cierres de llaves sobrantes del if
    $content = preg_replace(
        '/\n\s+\}\s*\n(\s+)(DB::statement|Schema::)/s',
        "\n\n$1$2",
        $content
    );

    return $content;
}
