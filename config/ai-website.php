<?php

return [
    // Credits charged per website generation. Server-enforced only.
    'generation_cost' => env('GENERATION_COST', 1),

    // Internal Node.js compile worker (never exposed publicly).
    'compile_service_url' => env('COMPILE_SERVICE_URL', 'http://127.0.0.1:5178'),
    'compile_token' => env('COMPILE_SERVICE_TOKEN'),
    'compile_timeout' => env('COMPILE_TIMEOUT', 120),

    // Free-tier OpenRouter models used by the site builder agent.
    'openrouter_model' => env('OPENROUTER_MODEL', 'z-ai/glm-5.2:free'),
    'openrouter_backup_model' => env('OPENROUTER_BACKUP_MODEL', 'minimax/minimax-m3:free'),
];
