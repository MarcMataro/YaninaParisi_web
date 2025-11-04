<?php
/**
 * get_availability.php
 * --------------------
 * Endpoint API que retorna la disponibilitat per a un dia concret.
 *
 * Inputs (GET):
 *  - date (YYYY-MM-DD) -> data a comprovar (obligatori)
 *  - debug (0|1)        -> si s'envia, s'inclou informació detallada per depuració
 *
 * Sortida (JSON):
 *  - success: boolean
 *  - date: data consultada
 *  - slots: [{ time: 'HH:MM', available: bool }, ...]
 *  - occupied: [ 'HH:MM', ... ]
 *  - fully_booked: boolean
 *  - session_count: int (nombre de sessions no cancel·lades al dia)
 *  - debug_sessions: opcional, files DB per depuració (si debug=1)
 *
 * Implementació (resum):
 *  1) Es validen dades d'entrada
 *  2) Es carreguen les sessions del dia des de la classe Session
 *  3) Es filtren les sessions 'Cancelada'
 *  4) Per cada slot horari (09:00..20:00, excepte 13:00 i 14:00) es calcula
 *     en PHP si hi ha solapament amb alguna sessió (comprovació segura
 *     independent del format de la BDD).
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../classes/connexio.php';
require_once __DIR__ . '/../classes/sessions.php';

$date = isset($_GET['date']) ? trim((string)$_GET['date']) : '';

$out = ['success' => false, 'date' => $date];

try {
    if (!Session::validarData($date)) {
        throw new Exception('Data no vàlida. Ús: YYYY-MM-DD');
    }

    $conn = Connexio::getInstance();
    $pdo = $conn->getConnexio();
    $sessModel = new Session($pdo);

    // Generate slots and determine availability by comparing against sessions from DB
    $slots = [];

    // Fetch sessions for the date (raw rows)
    $stmt = $sessModel->cercarPerData($date);
    $sessions = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];

    // filter out cancelled sessions
    $sessions = array_filter($sessions, function($r){
        return (!isset($r['estat_sessio']) || $r['estat_sessio'] !== 'Cancelada');
    });

    // session count for convenience
    $session_count = count($sessions);

    // helper to parse time strings (with or without seconds)
    $parseTime = function($dateStr, $timeStr) {
        $formats = ['Y-m-d H:i:s','Y-m-d H:i'];
        foreach ($formats as $fmt) {
            $dt = DateTime::createFromFormat($fmt, $dateStr . ' ' . $timeStr);
            if ($dt) return $dt;
        }
        // as fallback, try strtotime
        $ts = strtotime($dateStr . ' ' . $timeStr);
        if ($ts !== false) return new DateTime('@' . $ts);
        return false;
    };

    for ($h = 9; $h <= 20; $h++) {
        if ($h === 13 || $h === 14) continue; // lunch
        $slotStartStr = sprintf('%02d:00:00', $h);
        $slotEndStr = sprintf('%02d:00:00', $h + 1);

        $slotStart = $parseTime($date, $slotStartStr);
        $slotEnd = $parseTime($date, $slotEndStr);

        $available = true;

        if ($slotStart && $slotEnd) {
            foreach ($sessions as $s) {
                // session times may be stored as HH:MM or HH:MM:SS
                $sStart = $parseTime($date, $s['hora_inici'] ?? '00:00');
                $sEnd = $parseTime($date, $s['hora_fi'] ?? '00:00');
                if (!$sStart || !$sEnd) continue;
                // check overlap: session_start < slot_end AND session_end > slot_start
                if ($sStart < $slotEnd && $sEnd > $slotStart) {
                    $available = false;
                    break;
                }
            }
        }

        $slots[] = [ 'time' => sprintf('%02d:00', $h), 'available' => $available ];
    }

    $occupied = array_map(function($s){ return $s['time']; }, array_filter($slots, function($s){ return !$s['available']; }));
    $fully_booked = count($occupied) === count($slots);

    $out['success'] = true;
    $out['slots'] = $slots;
    $out['occupied'] = $occupied;
    $out['fully_booked'] = $fully_booked;
    $out['session_count'] = $session_count;

    // If debug flag is set, also return raw sessions for that date to help debugging
        // 9) Incloure sessions ordenades en la resposta (només camps rellevants)
        $sanitized = [];
        foreach ($sessions as $s) {
            $sanitized[] = [
                'id_sessio' => $s['id_sessio'] ?? null,
                'hora_inici' => $s['hora_inici'] ?? null,
                'hora_fi' => $s['hora_fi'] ?? null,
                'nom_complet_pacient' => $s['nom_complet_pacient'] ?? null,
                'tipus_sessio' => $s['tipus_sessio'] ?? null,
                'estat_sessio' => $s['estat_sessio'] ?? null
            ];
        }
        $out['sessions'] = $sanitized;

        // 10) Si es demana mode debug, també incloure files completes per depuració
        if (isset($_GET['debug']) && $_GET['debug']) {
            $stmt = $sessModel->cercarPerData($date);
            $rows = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
            $out['debug_sessions'] = $rows;
        }

    echo json_encode($out, JSON_UNESCAPED_UNICODE);
    exit;

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    exit;
}

?>
