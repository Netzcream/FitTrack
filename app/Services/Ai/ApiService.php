<?php

namespace App\Services\Ai;

use OpenAI\Laravel\Facades\OpenAI;

class ApiService
{
    /**
     * Llama al endpoint /v1/responses con un input de texto y devuelve un array.
     *
     * @param  string $prompt   Texto de entrada (lo que querés preguntar)
     * @param  array  $options  Opcionales (modelo, temperature, etc.)
     * @return array{ text: string|null, raw: array }
     */
    public function respond(string $prompt, array $options = []): array
    {
        // Defaults seguros y fáciles de refinar luego
        $model = $options['model'] ?? 'gpt-5-nano';
        $temperature = $options['temperature'] ?? 0.7;

        $params = [
            'model' => $model,
            'input' => $prompt,
            'reasoning' => ['effort' => 'minimal'],
             'max_output_tokens' => $options['max_output_tokens'] ?? 64,
        ];

        if (!in_array($model, ['gpt-5-nano'])) {
            $params['temperature'] = $temperature;
        }


        $response = OpenAI::responses()->create($params);


        // El SDK devuelve objetos; los convertimos y extraemos el texto principal
        $raw = json_decode(json_encode($response), true);

        // La estructura típica trae el texto en: output[0].content[0].text
        $text = $raw['output'][0]['content'][0]['text'] ?? null;

        return [
            'text' => $text,
            'raw'  => $raw, // por si querés inspeccionar tokens/usage/tool calls, etc.
        ];
    }
}
