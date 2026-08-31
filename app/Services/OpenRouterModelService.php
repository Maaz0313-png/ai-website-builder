<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class OpenRouterModelService
{
    public function fetchFreeModels(): array
    {
        return Cache::remember('openrouter_free_models', 3600, function () {
            try {
                $apiKey = config('services.openrouter.key');
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $apiKey,
                ])->timeout(10)->get('https://openrouter.ai/api/v1/models');

                if (!$response->successful()) {
                    return $this->fallbackModels();
                }

                $models = $response->json('data', []);
                $freeModels = array_filter($models, function ($model) {
                    return isset($model['pricing']['prompt']) && $model['pricing']['prompt'] === '0';
                });

                return array_values(array_map(function ($model) {
                    return [
                        'id' => $model['id'],
                        'name' => $model['name'] ?? $model['id'],
                        'context_length' => $model['context_length'] ?? null,
                    ];
                }, $freeModels));
            } catch (\Throwable) {
                return $this->fallbackModels();
            }
        });
    }

    private function fallbackModels(): array
    {
        return [
            ['id' => 'z-ai/glm-5.2:free', 'name' => 'Z.ai: GLM 5.2 (Free)', 'context_length' => 256000],
            ['id' => 'minimax/minimax-m3:free', 'name' => 'MiniMax: MiniMax M3 (Free)', 'context_length' => 1048576],
            ['id' => 'nvidia/nemotron-3.5-lightning:free', 'name' => 'NVIDIA: Nemotron 3.5 Lightning (Free)', 'context_length' => 1000000],
            ['id' => 'thinkingmachines/inkling-small:free', 'name' => 'Thinking Machines: Inkling Small (Free)', 'context_length' => 1048576],
            ['id' => 'thinkingmachines/inkling:free', 'name' => 'Thinking Machines: Inkling (Free)', 'context_length' => 1048576],
            ['id' => 'poolside/laguna-s-2.1:free', 'name' => 'Poolside: Laguna S 2.1 (Free)', 'context_length' => 262144],
            ['id' => 'poolside/laguna-xs-2.1:free', 'name' => 'Poolside: Laguna XS 2.1 (Free)', 'context_length' => 262144],
            ['id' => 'cohere/north-mini-code:free', 'name' => 'Cohere: North Mini Code (Free)', 'context_length' => 256000],
            ['id' => 'nvidia/nemotron-3.5-content-safety:free', 'name' => 'NVIDIA: Nemotron 3.5 Content Safety (Free)', 'context_length' => 128000],
            ['id' => 'nvidia/nemotron-3-ultra-550b-a55b:free', 'name' => 'NVIDIA: Nemotron 3 Ultra (Free)', 'context_length' => 1000000],
            ['id' => 'nvidia/nemotron-3-nano-omni-30b-a3b-reasoning:free', 'name' => 'NVIDIA: Nemotron 3 Nano Omni (Free)', 'context_length' => 256000],
            ['id' => 'google/gemma-4-26b-a4b-it:free', 'name' => 'Google: Gemma 4 26B A4B (Free)', 'context_length' => 262144],
            ['id' => 'google/gemma-4-31b-it:free', 'name' => 'Google: Gemma 4 31B (Free)', 'context_length' => 262144],
            ['id' => 'minimax/minimax-m2.7:free', 'name' => 'MiniMax: MiniMax M2.7 (Free)', 'context_length' => 196608],
            ['id' => 'nvidia/nemotron-3-super-120b-a12b:free', 'name' => 'NVIDIA: Nemotron 3 Super (Free)', 'context_length' => 262144],
            ['id' => 'liquid/lfm-2.5-2.6b:free', 'name' => 'LiquidAI: LFM2.5-2.6B (Free)', 'context_length' => 65536],
            ['id' => 'stealth/ox-alpha', 'name' => 'Stealth: Ox Alpha (Free)', 'context_length' => 1048576],
            ['id' => 'openrouter/free', 'name' => 'OpenRouter: Free Models Router', 'context_length' => 200000],
        ];
    }
}