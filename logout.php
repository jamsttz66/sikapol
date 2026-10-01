<?php
require_once __DIR__ . '/config.php';

if (currentUser()) {
    logout();
}
session_start();
setFlash('success', 'Anda telah keluar dari sistem.');
redirect('login.php');
