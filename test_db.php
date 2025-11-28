<?php
$host = 'db.bissicrnvfeaijqzfibz.supabase.co';
$db   = 'postgres';
$user = 'postgres';
$pass = 'TU_CONTRASEÑA'; // User needs to fill this, but I will read from env if possible or ask user to edit.
// Actually, I'll use getenv if I can, or just hardcode a placeholder and ask user to run it.
// Better: read .env file manually.

$env = file_get_contents('.env');
preg_match('/DB_PASSWORD=(.*)/', $env, $matches);
$pass = trim($matches[1]);

$dsn = "pgsql:host=$host;port=5432;dbname=$db";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
     echo "Connected successfully to Supabase!";
} catch (\PDOException $e) {
     echo "Connection failed: " . $e->getMessage();
}
