@props([
    'student',
    'compact' => false,
])

@php
    $profile = $student->gamificationProfile ?? null;

    if (!$profile) {
        return;
    }

    $currentLevel = $profile->current_level;
    $nextLevel = $currentLevel + 1;
    $progress = $profile->level_progress_percent;
    $tierName = $profile->tier_name;
    $tierIcon = gamification_tier_icon($profile->current_tier);
    $badgeClass = gamification_badge_class($profile->current_tier);
    $currentXp = $profile->total_xp - $profile->xp_for_current_level;
    $requiredXp = $profile->xp_for_next_level - $profile->xp_for_current_level;
@endphp

<style>
    .level-badge {
        background: linear-gradient(135deg, var(--ftt-color-base), var(--ftt-color-dark));
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15), 0 0 20px rgba(var(--ftt-color-base-rgb, 99, 102, 241), 0.2);
    }
    .level-glow {
        filter: drop-shadow(0 0 8px rgba(var(--ftt-color-base-rgb, 99, 102, 241), 0.4));
    }
</style>

<div class="flex items-center gap-4 w-full {{ $compact ? 'text-sm' : '' }}"
     x-data="{
         progress: {{ $progress }},
         currentLevel: {{ $currentLevel }},
         currentXp: {{ $currentXp }},
         requiredXp: {{ $requiredXp }},
         tierName: '{{ $tierName }}',
         showTierUpModal: false,
         tierUpData: {},
         levelUpEmojis: [],
         nextEmojiId: 0,

         init() {
             // Escuchar eventos de Livewire para actualización instantánea
             Livewire.on('xp-gained', (event) => {
                 // Livewire envuelve los datos en un array, extraer el primer elemento
                 const data = Array.isArray(event) ? event[0] : event;
                 this.handleXpGained(data);
             });

             Livewire.on('level-up', (event) => {
                 // Livewire envuelve los datos en un array, extraer el primer elemento
                 const data = Array.isArray(event) ? event[0] : event;
                 this.handleLevelUp(data);
             });
         },

         handleXpGained(data) {
             // Usar los datos del servidor en lugar de calcular localmente
             // Esto evita inconsistencias y muestra el progreso real
             this.progress = data.progress || this.progress;
             this.currentXp = data.currentXp || this.currentXp;
             this.requiredXp = data.requiredXp || this.requiredXp;
         },

         handleLevelUp(data) {
             const barEnd = $refs.progressBar.getBoundingClientRect();
             const barCenter = barEnd.left + (barEnd.width / 2);
             const originY = barEnd.top + barEnd.height / 2;
             const emojis = ['🎉', '👏', '😊', '🎺', '🥳', '🎊', '✨', '🌟', '💪', '🔥', '⭐', '🏆'];

             // Crear explosión más dramática de emojis
             for (let i = 0; i < 25; i++) {
                 const angle = Math.random() * Math.PI * 2; // 360 grados completos
                 const distance = 200 + Math.random() * 350; // Mucho más lejos
                 const duration = 2000 + Math.random() * 2500;

                 const particle = {
                     id: this.nextEmojiId++,
                     emoji: emojis[Math.floor(Math.random() * emojis.length)],
                     originX: barCenter,
                     originY,
                     targetX: Math.cos(angle) * distance,
                     targetY: Math.sin(angle) * distance - 150,
                     duration,
                     delay: i * 40,
                     rotation: Math.random() * 1440 - 720,
                     scale: 1.5 + Math.random() * 1.2
                 };

                 this.levelUpEmojis.push(particle);

                 setTimeout(() => {
                     this.levelUpEmojis = this.levelUpEmojis.filter(p => p.id !== particle.id);
                 }, duration + particle.delay + 100);
             }

             // Actualizar todos los valores con los datos del evento (sin refresh para evitar parpadeo)
             this.currentLevel = data.newLevel || this.currentLevel;
             this.progress = data.newProgress || 0;
             this.currentXp = data.newCurrentXp || 0;
             this.requiredXp = data.newRequiredXp || this.requiredXp;
             this.tierName = data.newTierName || this.tierName;

             // Si cambió de tier, mostrar modal especial después de un pequeño delay
             if (data.tierChanged === true) {
                 setTimeout(() => {
                     this.tierUpData = {
                         newTierName: data.newTierName,
                         oldTier: data.oldTier,
                         newTier: data.newTier
                     };
                     this.showTierUpModal = true;
                 }, 1500);
             }
         }
     }">

    {{-- Level-up emoji particles --}}
    <template x-for="emoji in levelUpEmojis" :key="emoji.id">
        <div
            class="fixed pointer-events-none z-50 text-3xl"
            :style="{
                left: `${emoji.originX}px`,
                top: `${emoji.originY}px`,
                '--emoji-tx': `${emoji.targetX}px`,
                '--emoji-ty': `${emoji.targetY}px`,
                '--emoji-rot': `${emoji.rotation}deg`,
                '--emoji-scale': emoji.scale,
                '--emoji-delay': `${emoji.delay}ms`,
                '--emoji-duration': `${emoji.duration}ms`,
                animation: `emojiFloat var(--emoji-duration) cubic-bezier(0.34, 1.56, 0.64, 1) var(--emoji-delay) forwards`
            }"
            x-text="emoji.emoji">
        </div>
    </template>

    <style>
        @keyframes emojiFloat {
            0% {
                transform: translate(0, 0) scale(0.5) rotate(0deg);
                opacity: 1;
            }
            60% {
                opacity: 1;
            }
            100% {
                transform: translate(var(--emoji-tx), var(--emoji-ty)) scale(var(--emoji-scale, 1.2)) rotate(var(--emoji-rot));
                opacity: 0;
            }
        }
    </style>

    {{-- Nivel actual --}}
    <div class="flex flex-col items-center shrink-0">
        <div class="level-badge flex items-center justify-center w-12 h-12 rounded-xl text-white font-black text-lg" x-text="currentLevel">
        </div>
        <span class="text-[10px] font-semibold text-gray-500 dark:text-gray-400 mt-1 uppercase tracking-wide" x-text="'Nv. ' + currentLevel"></span>
        <span class="text-[8px] font-semibold text-gray-600 dark:text-gray-500 mt-0.5 uppercase tracking-wide" x-text="tierName"></span>
    </div>

    {{-- Barra de progreso --}}
    <div class="flex-1 min-w-0" x-ref="progressBar">
        <div class="relative h-2.5 bg-gradient-to-r from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-600 rounded-full overflow-visible shadow-inner border border-gray-300 dark:border-gray-600">
            {{-- Barra de progreso --}}
            <div
                class="h-full rounded-full transition-all duration-700 ease-out relative"
                :style="`width: ${progress}%; background: var(--ftt-color-base);`">
            </div>

            {{-- Runner posicionado en el progreso actual --}}
            <div class="absolute -top-5 transition-all duration-700 ease-out"
                 :style="`left: max(-4px, calc(${Math.min(100, progress)}% - 22px));`">
                <x-icons.lucide.runner class="w-7 h-7" style="color: var(--ftt-color-base); filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));" />
            </div>
        </div>

        {{-- Texto de progreso --}}
        <div class="flex items-center justify-between mt-1.5 px-1">
            <span class="text-xs font-semibold text-gray-700 dark:text-gray-300 level-glow" x-text="currentXp.toLocaleString() + ' XP'">
            </span>
            <span class="text-xs font-bold" style="color: var(--ftt-color-base)" x-text="requiredXp.toLocaleString() + ' XP'">
            </span>
        </div>
    </div>

    {{-- Próximo nivel --}}
    <div class="flex flex-col items-center shrink-0">
        <div class="flex items-center justify-center w-12 h-12 rounded-xl border-2 border-dashed text-gray-400 dark:text-gray-500 font-black text-lg transition-all duration-300 hover:border-solid hover:scale-105" style="border-color: var(--ftt-color-base)" x-text="currentLevel + 1">
        </div>
        <span class="text-[10px] font-semibold text-gray-500 dark:text-gray-400 mt-1 uppercase tracking-wide" x-text="'Nv. ' + (currentLevel + 1)"></span>
    </div>

    {{-- Modal de cambio de tier --}}
    <div x-show="showTierUpModal" @click="showTierUpModal = false" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4" x-transition>
        <div @click.stop class="bg-white dark:bg-neutral-900 rounded-2xl shadow-2xl overflow-hidden max-w-md w-full transform transition-all" x-transition.scale>
            {{-- Header con degradado --}}
            <div class="h-32 bg-gradient-to-br from-purple-500 via-purple-600 to-indigo-600 relative overflow-hidden flex items-center justify-center">
                <div class="absolute inset-0 opacity-20">
                    <div class="absolute top-2 left-4 text-4xl animate-bounce">🌟</div>
                    <div class="absolute top-8 right-6 text-5xl animate-bounce" style="animation-delay: 0.1s;">✨</div>
                    <div class="absolute bottom-4 left-8 text-4xl animate-bounce" style="animation-delay: 0.2s;">💪</div>
                </div>
                <div class="relative text-center">
                    <div class="text-6xl mb-2">🎉</div>
                    <h3 class="text-white font-black text-xl">¡PROMOCIÓN!</h3>
                </div>
            </div>

            {{-- Contenido --}}
            <div class="p-8 text-center space-y-6">
                <div class="space-y-2">
                    <p class="text-gray-600 dark:text-gray-400 text-sm font-semibold uppercase tracking-widest">Felicidades</p>
                    <p class="text-2xl font-black text-gray-900 dark:text-white">
                        Ascendiste a
                    </p>
                </div>

                {{-- Tier badge --}}
                <div class="flex justify-center">
                    <div class="bg-gradient-to-br from-purple-500 to-indigo-600 rounded-2xl px-8 py-4 text-white font-black text-3xl shadow-lg" x-text="tierUpData.newTierName">
                    </div>
                </div>

                <p class="text-gray-600 dark:text-gray-400 text-sm leading-relaxed">
                    Tu dedicación y esfuerzo te han llevado a este nuevo nivel. ¡Sigue así!
                </p>

                {{-- Botón de cierre --}}
                <button @click="showTierUpModal = false" class="w-full bg-gradient-to-r from-purple-500 to-indigo-600 hover:from-purple-600 hover:to-indigo-700 text-white font-bold py-3 px-4 rounded-xl transition-all duration-200 transform hover:scale-105">
                    Continuar
                </button>
            </div>
        </div>
    </div>
</div>
