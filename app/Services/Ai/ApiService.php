<?php

namespace App\Services\Ai;

use OpenAI\Laravel\Facades\OpenAI;
use Illuminate\Support\Facades\Log;

class ApiService
{
    /**
     * Llama al endpoint de Chat Completions de OpenAI y devuelve la respuesta.
     *
     * @param  string $prompt   Texto de entrada (lo que querés preguntar)
     * @param  array  $options  Opcionales (model, temperature, max_tokens, etc.)
     * @return array{ text: string|null, raw: array }
     */
    public function respond(string $prompt, array $options = []): array
    {
        // Defaults seguros - modelos válidos de OpenAI
        $model = $options['model'] ?? 'gpt-4o-mini'; // Modelo económico y rápido
        $temperature = $options['temperature'] ?? 0.7;
        $maxTokens = $options['max_tokens'] ?? 500;

        $params = [
            'model' => $model,
            'messages' => [
                ['role' => 'user', 'content' => $prompt],
            ],
            'temperature' => $temperature,
            'max_tokens' => $maxTokens,
        ];

        // Si se proporciona un system prompt
        if (isset($options['system'])) {
            array_unshift($params['messages'], [
                'role' => 'system',
                'content' => $options['system']
            ]);
        }

        // Si se solicita JSON mode (gpt-4o, gpt-4o-mini, gpt-4-turbo)
        if (isset($options['response_format']) && $options['response_format'] === 'json_object') {
            $params['response_format'] = ['type' => 'json_object'];
        }

        Log::info('[AI Service] Llamando a OpenAI API', [
            'model' => $model,
            'temperature' => $temperature,
            'max_tokens' => $maxTokens,
            'prompt_length' => strlen($prompt),
        ]);

        // Reintentos automáticos para timeouts
        $maxRetries = 2;
        $retryDelay = 2; // segundos

        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            try {
                if ($attempt > 1) {
                    Log::info('[AI Service] Reintentando llamada a OpenAI', [
                        'attempt' => $attempt,
                        'max_retries' => $maxRetries,
                    ]);
                    sleep($retryDelay);
                }

                $response = OpenAI::chat()->create($params);

                // El SDK devuelve objetos; los convertimos a array
                $raw = json_decode(json_encode($response), true);

                // Extraemos el texto de la respuesta
                $text = $raw['choices'][0]['message']['content'] ?? null;

                Log::info('[AI Service] Respuesta recibida de OpenAI', [
                    'model' => $model,
                    'has_text' => !empty($text),
                    'text_length' => $text ? strlen($text) : 0,
                    'finish_reason' => $raw['choices'][0]['finish_reason'] ?? null,
                    'usage' => $raw['usage'] ?? [],
                    'attempt' => $attempt,
                ]);

                return [
                    'text' => $text,
                    'raw'  => $raw, // incluye usage (tokens), model, etc.
                ];

            } catch (\Exception $e) {
                $isTimeout = str_contains($e->getMessage(), 'timed out') ||
                            str_contains($e->getMessage(), 'cURL error 28');

                $isLastAttempt = $attempt === $maxRetries;

                Log::error('[AI Service] Error en llamada a OpenAI', [
                    'error_message' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'error_code' => $e->getCode(),
                    'model' => $model,
                    'attempt' => $attempt,
                    'is_timeout' => $isTimeout,
                    'will_retry' => $isTimeout && !$isLastAttempt,
                ]);

                // Si no es un timeout o ya agotamos los reintentos, lanzar el error
                if (!$isTimeout || $isLastAttempt) {
                    throw $e;
                }

                // Si es timeout y no es el último intento, continuar con el siguiente intento
            }
        }

        // Fallback de seguridad (no debería alcanzarse nunca)
        throw new \RuntimeException('[AI Service] Error inesperado: se agotaron los reintentos sin respuesta ni excepción.');
    }

    /**
     * Genera una respuesta con conversación multi-turno.
     *
     * @param  array  $messages Array de mensajes [['role' => 'user|assistant|system', 'content' => '...']]
     * @param  array  $options  Opcionales (model, temperature, etc.)
     * @return array{ text: string|null, raw: array }
     */
    public function chat(array $messages, array $options = []): array
    {
        $model = $options['model'] ?? 'gpt-4o-mini';
        $temperature = $options['temperature'] ?? 0.7;
        $maxTokens = $options['max_tokens'] ?? 500;

        $params = [
            'model' => $model,
            'messages' => $messages,
            'temperature' => $temperature,
            'max_tokens' => $maxTokens,
        ];

        $response = OpenAI::chat()->create($params);
        $raw = json_decode(json_encode($response), true);
        $text = $raw['choices'][0]['message']['content'] ?? null;

        return [
            'text' => $text,
            'raw'  => $raw,
        ];
    }

    /**
     * Genera un embedding de texto para búsqueda semántica.
     *
     * @param  string $text   Texto para convertir en embedding
     * @param  string $model  Modelo de embedding (por defecto text-embedding-3-small)
     * @return array{ embedding: array|null, raw: array }
     */
    public function embed(string $text, string $model = 'text-embedding-3-small'): array
    {
        $response = OpenAI::embeddings()->create([
            'model' => $model,
            'input' => $text,
        ]);

        $raw = json_decode(json_encode($response), true);
        $embedding = $raw['data'][0]['embedding'] ?? null;

        return [
            'embedding' => $embedding,
            'raw' => $raw,
        ];
    }

    /**
     * Analiza y clasifica un texto (útil para moderation, sentiment, etc.).
     *
     * @param  string $text   Texto a analizar
     * @param  string $type   Tipo de análisis (sentiment, category, etc.)
     * @param  array  $options Opcionales
     * @return array{ result: string|null, raw: array }
     */
    public function analyze(string $text, string $type = 'sentiment', array $options = []): array
    {
        $systemPrompts = [
            'sentiment' => 'Analiza el sentimiento del siguiente texto y responde únicamente con: positivo, negativo o neutral.',
            'category' => 'Clasifica el siguiente texto en una categoría y responde solo con el nombre de la categoría.',
            'summary' => 'Resume el siguiente texto en máximo 2-3 oraciones.',
        ];

        $system = $systemPrompts[$type] ?? $systemPrompts['sentiment'];

        return $this->respond($text, array_merge($options, [
            'system' => $system,
            'max_tokens' => 100,
        ]));
    }

    /**
     * Genera contenido estructurado en formato JSON.
     *
     * @param  string $prompt   Prompt con instrucciones
     * @param  array  $options  Opcionales
     * @return array{ data: array|null, text: string|null, raw: array }
     */
    public function generateJson(string $prompt, array $options = []): array
    {
        $options['system'] = $options['system'] ?? 'Responde únicamente con JSON válido, sin explicaciones adicionales.';

        $response = $this->respond($prompt, $options);

        $jsonData = null;
        if ($response['text']) {
            // Intenta extraer JSON del texto (por si viene con markdown ```json)
            $text = $response['text'];
            if (preg_match('/```json\s*(.*?)\s*```/s', $text, $matches)) {
                $text = $matches[1];
            }
            $jsonData = json_decode($text, true);
        }

        return [
            'data' => $jsonData,
            'text' => $response['text'],
            'raw' => $response['raw'],
        ];
    }

    /**
     * Modera contenido usando la API de moderación de OpenAI.
     *
     * @param  string $text Texto a moderar
     * @return array{ flagged: bool, categories: array, raw: array }
     */
    public function moderate(string $text): array
    {
        $response = OpenAI::moderations()->create([
            'input' => $text,
        ]);

        $raw = json_decode(json_encode($response), true);
        $result = $raw['results'][0] ?? [];

        return [
            'flagged' => $result['flagged'] ?? false,
            'categories' => $result['categories'] ?? [],
            'raw' => $raw,
        ];
    }
}
