<?php
require_once __DIR__ . '/includes/functions.php';
unset($_SESSION['client_id'], $_SESSION['client_username']);
header('Location: index.php');
exit;
