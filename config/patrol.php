<?php
// config/patrol.php
return [
    // Precisión (metros)
    'accuracy_max'                  => env('PATROL_ACCURACY_MAX', 50),

    // Guardado estricto
    // Si true, descarta cuando accuracy > max o fuera de radio.
    // Si false, guarda como NO VERIFICADO (comportamiento actual).
    'strict_accuracy'               => env('PATROL_STRICT_ACCURACY', false),
    'strict_radius'                 => env('PATROL_STRICT_RADIUS', false),
    'strict_assignment_transitions' => env('PATROL_STRICT_ASSIGNMENT', false),

    // (Dejamos aquí por coherencia futura; no los tocamos hoy)
    'speed_max_mps'                 => env('PATROL_SPEED_MAX_MPS', 15),
    'jump_max_m'                    => env('PATROL_JUMP_MAX_M', 150),
    'jump_window_s'                 => env('PATROL_JUMP_WINDOW_S', 10),
];
