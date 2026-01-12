# Guía de Uso del Servicio de IA (ApiService)

## Descripción

`ApiService` es un wrapper sobre el SDK de OpenAI Laravel que facilita las llamadas a la API de OpenAI. Proporciona métodos simples para:
- Generar respuestas de chat
- Mantener conversaciones multi-turno
- Analizar y clasificar texto
- Generar embeddings
- Moderar contenido
- Generar JSON estructurado

## Configuración

### 1. Variables de entorno (.env)

```env
OPENAI_API_KEY=sk-proj-xxxxxxxxxxxxxxxx
OPENAI_ORGANIZATION=org-xxxxxxxxxxxxxxxx  # Opcional
OPENAI_BASE_URL=https://api.openai.com/v1  # Opcional, para usar endpoints personalizados
```

### 2. Configuración (config/openai.php)

Ya está configurado. Usa las variables de entorno definidas arriba.

## Uso Básico

### 1. Respuesta Simple

```php
use App\Services\Ai\ApiService;

$aiService = app(ApiService::class);

$response = $aiService->respond('¿Qué ejercicios recomiendas para pecho?');

echo $response['text']; // La respuesta generada
print_r($response['raw']); // Información completa (tokens usados, modelo, etc.)
```

### 2. Con Opciones Personalizadas

```php
$response = $aiService->respond(
    '¿Qué ejercicios recomiendas para pecho?',
    [
        'model' => 'gpt-4o',  // Modelo más avanzado
        'temperature' => 0.9,  // Más creativo (0.0 - 2.0)
        'max_tokens' => 1000,  // Respuesta más larga
        'system' => 'Eres un entrenador personal experto en fitness.',  // Context
    ]
);
```

### 3. Conversación Multi-turno

```php
$messages = [
    ['role' => 'system', 'content' => 'Eres un entrenador personal experto.'],
    ['role' => 'user', 'content' => '¿Qué ejercicios recomiendas para pecho?'],
    ['role' => 'assistant', 'content' => 'Te recomiendo press de banca, fondos y aperturas.'],
    ['role' => 'user', 'content' => '¿Y para espalda?'],
];

$response = $aiService->chat($messages);
```

### 4. Análisis de Texto

#### Análisis de Sentimiento

```php
$result = $aiService->analyze(
    'Me encanta este gimnasio, los entrenadores son increíbles!',
    'sentiment'
);
// Devuelve: positivo, negativo o neutral
```

#### Categorización

```php
$result = $aiService->analyze(
    'Necesito ayuda para ganar masa muscular',
    'category',
    ['system' => 'Categoriza en: nutrición, entrenamiento, recuperación']
);
```

#### Resumen

```php
$result = $aiService->analyze(
    'Texto largo que quieres resumir...',
    'summary'
);
```

### 5. Generar JSON Estructurado

```php
$response = $aiService->generateJson(
    'Genera un plan de entrenamiento de 3 días para principiantes en formato JSON con: dia, ejercicios (nombre, sets, reps)'
);

if ($response['data']) {
    // $response['data'] contiene el array PHP parseado del JSON
    foreach ($response['data'] as $dia) {
        // Procesar...
    }
}
```

### 6. Embeddings para Búsqueda Semántica

```php
$embedding = $aiService->embed('Press de banca con barra');

// $embedding['embedding'] es un array de ~1500 números (vector)
// Útil para guardar en BD y hacer búsqueda semántica
```

### 7. Moderación de Contenido

```php
$result = $aiService->moderate('Texto potencialmente inapropiado');

if ($result['flagged']) {
    // Contenido flagged como inapropiado
    print_r($result['categories']); // Categorías problemáticas
}
```

## Modelos Disponibles (Enero 2026)

### Chat Completions

- `gpt-4o` - Más capaz y reciente
- `gpt-4o-mini` - **Recomendado**: Económico, rápido y muy capaz (default)
- `gpt-4-turbo` - Anterior generación GPT-4
- `gpt-3.5-turbo` - Más económico, menos capaz

### Embeddings

- `text-embedding-3-small` - **Recomendado**: Rápido y económico (default)
- `text-embedding-3-large` - Mayor dimensionalidad, más preciso

## Ejemplos de Uso en FitTrack

### 1. Generador de Rutinas IA

```php
// En un controller o servicio
public function generateWorkout(Student $student)
{
    $aiService = app(ApiService::class);
    
    $prompt = "Genera una rutina de entrenamiento para un alumno con estas características:
    - Nivel: {$student->experience_level}
    - Objetivo: {$student->goal}
    - Días disponibles: {$student->available_days}
    
    Formato JSON con: nombre_rutina, dias (array), ejercicios por día (nombre, sets, reps, descanso)";
    
    $response = $aiService->generateJson($prompt, [
        'model' => 'gpt-4o-mini',
        'max_tokens' => 1500,
    ]);
    
    return $response['data'];
}
```

### 2. Asistente de Mensajes

```php
// Sugerencias de respuesta para entrenadores
public function suggestResponse(string $studentMessage)
{
    $aiService = app(ApiService::class);
    
    $response = $aiService->respond(
        "Como entrenador personal, sugiere una respuesta profesional y útil para este mensaje del alumno: \"{$studentMessage}\"",
        [
            'system' => 'Eres un entrenador personal profesional y empático.',
            'max_tokens' => 300,
        ]
    );
    
    return $response['text'];
}
```

### 3. Análisis de Feedback

```php
// Analizar feedback de alumnos
public function analyzeFeedback(string $feedback)
{
    $aiService = app(ApiService::class);
    
    $sentiment = $aiService->analyze($feedback, 'sentiment');
    $summary = $aiService->analyze($feedback, 'summary');
    
    return [
        'sentiment' => $sentiment['text'],
        'summary' => $summary['text'],
    ];
}
```

### 4. Búsqueda Semántica de Ejercicios

```php
// Guardar embeddings en BD
public function indexExercise(Exercise $exercise)
{
    $aiService = app(ApiService::class);
    
    $text = "{$exercise->name} {$exercise->description} {$exercise->muscle_group}";
    $result = $aiService->embed($text);
    
    $exercise->embedding = json_encode($result['embedding']);
    $exercise->save();
}

// Buscar ejercicios similares
public function searchExercises(string $query)
{
    $aiService = app(ApiService::class);
    
    $queryEmbedding = $aiService->embed($query)['embedding'];
    
    // Calcular similitud coseno con embeddings guardados
    // y devolver los más similares
}
```

### 5. Moderación de Contenido de Usuario

```php
// En un FormRequest o middleware
public function validateUserContent(string $content)
{
    $aiService = app(ApiService::class);
    
    $result = $aiService->moderate($content);
    
    if ($result['flagged']) {
        throw ValidationException::withMessages([
            'content' => 'El contenido contiene material inapropiado.',
        ]);
    }
}
```

## Manejo de Errores

```php
use OpenAI\Exceptions\ErrorException;

try {
    $response = $aiService->respond('Tu pregunta');
} catch (ErrorException $e) {
    // Error de la API (rate limit, autenticación, etc.)
    Log::error('OpenAI API Error', [
        'message' => $e->getMessage(),
        'code' => $e->getCode(),
    ]);
    
    // Manejar el error apropiadamente
} catch (\Exception $e) {
    // Otro tipo de error
    Log::error('General Error', ['message' => $e->getMessage()]);
}
```

## Costos Aproximados (Enero 2026)

### gpt-4o-mini (Recomendado para producción)
- Input: $0.15 / 1M tokens
- Output: $0.60 / 1M tokens
- **Ejemplo**: 100 respuestas de ~300 palabras = ~$0.03

### gpt-4o
- Input: $2.50 / 1M tokens
- Output: $10.00 / 1M tokens
- **Ejemplo**: 100 respuestas de ~300 palabras = ~$0.50

### Embeddings (text-embedding-3-small)
- $0.02 / 1M tokens
- **Ejemplo**: 10,000 ejercicios indexados = ~$0.01

## Mejores Prácticas

1. **Usa caching**: Para respuestas repetidas, guarda en cache
2. **Limita max_tokens**: No pidas más de lo necesario
3. **Usa gpt-4o-mini por defecto**: Es 10x más barato y muy capaz
4. **System prompts**: Define bien el contexto para mejores respuestas
5. **Manejo de errores**: Siempre captura excepciones
6. **Rate limiting**: Ten en cuenta los límites de la API
7. **Logs**: Registra uso para monitorear costos
8. **Validación**: Valida siempre las respuestas JSON antes de usar

## Testing

```php
// En un test
use App\Services\Ai\ApiService;
use OpenAI\Laravel\Facades\OpenAI;

public function test_ai_service_responds()
{
    // Mock para testing
    OpenAI::fake([
        'chat.completions.create' => [
            'choices' => [
                ['message' => ['content' => 'Test response']],
            ],
        ],
    ]);
    
    $service = app(ApiService::class);
    $response = $service->respond('test');
    
    $this->assertEquals('Test response', $response['text']);
}
```

## Próximos Pasos

- [ ] Implementar caching de respuestas comunes
- [ ] Crear middleware para rate limiting
- [ ] Implementar sistema de logging de uso
- [ ] Crear panel de monitoreo de costos
- [ ] Implementar función para generar imágenes (DALL-E)
- [ ] Implementar text-to-speech para instrucciones de ejercicios
