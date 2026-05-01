<?php
// config/database.php  (replace the 'connections' array with this)

return [

    'default' => env('DB_CONNECTION', 'mysql'),

    'connections' => [

        // ── Central portal DB (users, tenants, roles) ──────────────────────
        'mysql' => [
            'driver'    => 'mysql',
            'host'      => env('DB_HOST', '127.0.0.1'),
            'port'      => env('DB_PORT', '3306'),
            'database'  => env('DB_DATABASE', 'bluearrow_portal'),
            'username'  => env('DB_USERNAME', 'akif'),
            'password'  => env('DB_PASSWORD', 'Guddoo@71'),
            'charset'   => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix'    => '',
            'strict'    => true,
            'engine'    => null,
        ],

        // ── BAMC Accounting (existing PostgreSQL) ──────────────────────────
        'bamc' => [
            'driver'   => 'pgsql',
            'host'     => env('BAMC_DB_HOST', '127.0.0.1'),
            'port'     => env('BAMC_DB_PORT', '5432'),
            'database' => env('BAMC_DB_DATABASE', 'bamc_accounting'),
            'username' => env('BAMC_DB_USERNAME', 'postgres'),
            'password' => env('BAMC_DB_PASSWORD', ''),
            'charset'  => 'utf8',
            'prefix'   => '',
            'schema'   => 'public',
        ],

        // ── CRM data (existing MySQL) ──────────────────────────────────────
        'crm' => [
            'driver'    => 'mysql',
            'host'      => env('CRM_DB_HOST', '127.0.0.1'),
            'port'      => env('CRM_DB_PORT', '3306'),
            'database'  => env('CRM_DB_DATABASE', 'crm_db'),
            'username'  => env('CRM_DB_USERNAME', 'root'),
            'password'  => env('CRM_DB_PASSWORD', ''),
            'charset'   => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix'    => '',
            'strict'    => true,
        ],
		
		'legacy' => [
			'driver'    => 'mysql',
			'host'      => env('LEGACY_DB_HOST', '127.0.0.1'),
			'port'      => env('LEGACY_DB_PORT', '3306'),
			'database'  => env('LEGACY_DB_DATABASE', 'bamc'),
			'username'  => env('LEGACY_DB_USERNAME', 'akif'),
			'password'  => env('LEGACY_DB_PASSWORD', 'Guddoo@71'),
			'charset'   => 'utf8mb4',
			'collation' => 'utf8mb4_unicode_ci',
			'prefix'    => '',
			'strict'    => false,
		],

    ],

    'migrations' => 'migrations',

];
