<div class="space-y-4">
    @forelse ($student->planAssignments()->latest('created_at')->get() as $assignment)
        <div class="border rounded-lg p-4"
            :class="{
                'border-green-300 bg-green-50 dark:bg-green-950/30 dark:border-green-800': {{ $assignment->is_active ? 'true' : 'false' }},
                'border-gray-300 bg-gray-50 dark:bg-neutral-800 dark:border-neutral-700': !{{ $assignment->is_active ? 'true' : 'false' }}
            }">

            <div class="flex items-start justify-between gap-4">
                <div class="flex-1">
                    <div class="flex items-center gap-2 mb-2">
                        <h4 class="font-semibold text-gray-900 dark:text-neutral-100">
                            {{ $assignment->name }}
                        </h4>
                        <span class="text-xs px-2 py-1 rounded"
                            :class="{
                                'bg-green-200 text-green-800 dark:bg-green-900/50 dark:text-green-200': {{ $assignment->is_active ? 'true' : 'false' }},
                                'bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-300': !{{ $assignment->is_active ? 'true' : 'false' }}
                            }">
                            {{ $assignment->is_active ? __('common.active') : __('common.inactive') }}
                        </span>
                    </div>

                    <p class="text-sm text-gray-600 dark:text-neutral-400 mb-2">
                        {{ $assignment->meta['version'] ?? '-' }}
                    </p>

                    <div class="text-xs text-gray-500 dark:text-neutral-500">
                        <p>
                            <strong>Inicio:</strong> {{ $assignment->starts_at?->format('d/m/Y') ?? '-' }}
                            @if ($assignment->ends_at)
                                | <strong>Fin:</strong> {{ $assignment->ends_at->format('d/m/Y') }}
                            @endif
                        </p>
                    </div>
                </div>

                <div class="flex gap-2">
                    <flux:button size="sm" variant="ghost" as="a"
                        href="{{ route('tenant.student.download-plan', $assignment->uuid) }}"
                        target="_blank">
                        {{ __('common.download') }}
                    </flux:button>
                </div>
            </div>
        </div>
    @empty
        <div class="bg-gray-50 dark:bg-neutral-800 border border-gray-200 dark:border-neutral-700 rounded-lg p-4">
            <p class="text-sm text-gray-600 dark:text-neutral-400">
                {{ __('students.no_training_plans') }}
            </p>
        </div>
    @endforelse
</div>
