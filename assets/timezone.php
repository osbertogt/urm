<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Aplica la zona horaria del cliente si existe
 */
if (!empty($_SESSION['timezone'])) {
    date_default_timezone_set($_SESSION['timezone']);
} else {
    date_default_timezone_set('UTC'); // fallback seguro
}

/**
 * Fecha/hora actual convertida a zona del cliente
 */
function now_client($format = 'Y-m-d H:i:s') {
    return date($format);
}

/**
 * Convierte una fecha UTC a la zona del cliente
 */
function date_to_client($date, $format = 'Y-m-d H:i:s') {
    if (!$date) return null;
    return date($format, strtotime($date));
}
