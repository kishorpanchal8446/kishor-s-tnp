<?php
require_once dirname(__DIR__) . '/config/auth.php';
initSession();
session_destroy();
header('Location: ../login.php?role=company');
exit;
