<?php
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'nima_smm');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_PORT', getenv('DB_PORT') ?: '3306');

define('SITE_NAME', 'Nima SMM');
define('SITE_URL', getenv('SITE_URL') ?: 'https://nima-smm.onrender.com');
define('PROVIDER_API_URL', 'https://smmpakpanel.com/api/v2');
define('PROVIDER_API_KEY', '924d90d325b79930a235cc83af4311fe');

session_start();
