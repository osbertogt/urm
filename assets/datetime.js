/**
 * Devuelve fecha-hora REAL del cliente en formato datetime-local
 * YYYY-MM-DDTHH:MM
 */
function getClientDateTimeLocal() {
    const d = new Date();
    return d.getFullYear() + '-' +
        String(d.getMonth() + 1).padStart(2, '0') + '-' +
        String(d.getDate()).padStart(2, '0') + 'T' +
        String(d.getHours()).padStart(2, '0') + ':' +
        String(d.getMinutes()).padStart(2, '0');
}

/**
 * Devuelve solo la fecha del cliente (YYYY-MM-DD)
 */
function getClientDate() {
    const d = new Date();
    return d.toISOString().split('T')[0];
}

/**
 * Inicializa timezone del cliente en el servidor (1 sola vez por sesión)
 */
function initClientTimezone() {
    if (sessionStorage.getItem('timezone_set')) return;

    const tz = Intl.DateTimeFormat().resolvedOptions().timeZone;

    fetch('set_timezone.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'timezone=' + encodeURIComponent(tz)
    }).then(() => {
        sessionStorage.setItem('timezone_set', '1');
    });
}
