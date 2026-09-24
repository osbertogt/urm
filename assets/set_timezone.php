<?php
session_start();

if (!empty($_POST['timezone'])) {
    $_SESSION['timezone'] = $_POST['timezone'];
    date_default_timezone_set($_POST['timezone']);
    echo json_encode(['ok' => true]);
} else {
    echo json_encode(['ok' => false]);
}
