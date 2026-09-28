<?php
// Convierte una fecha (aaaa-mm-dd, con o sin hora, o dd/mm/aaaa) a dd-mm-aaaa.
// Si trae hora (distinta de 00:00) se agrega como HH:MM. Si no se reconoce, se devuelve tal cual.
function formatoFecha($valor) {
    $valor = trim((string)$valor);
    if ($valor === '' || strpos($valor, '0000-00-00') === 0) {
        return '';
    }

    foreach (['Y-m-d H:i:s', 'Y-m-d H:i', 'Y-m-d\TH:i', 'Y-m-d\TH:i:s'] as $fmt) {
        $d = DateTime::createFromFormat($fmt, $valor);
        if ($d) {
            return $d->format('H:i') === '00:00' ? $d->format('d-m-Y') : $d->format('d-m-Y H:i');
        }
    }
    foreach (['Y-m-d', 'd/m/Y', 'd-m-Y'] as $fmt) {
        $d = DateTime::createFromFormat('!' . $fmt, $valor);
        if ($d) {
            return $d->format('d-m-Y');
        }
    }
    return $valor;
}
