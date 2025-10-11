<div
    x-data="{
        show: false,
        message: '',
        type: 'info',
        timeout: null,
        close() { this.show = false; clearTimeout(this.timeout) },
        trigger(e) {
            this.message = e.detail.message;
            this.type = e.detail.type ?? 'info';
            this.show = true;
            clearTimeout(this.timeout);
            this.timeout = setTimeout(() => this.show = false, 4000);
        }
    }"
    x-on:toast.window="trigger($event)"
    class="fixed bottom-5 right-5 z-50 flex flex-col gap-2 items-end"
>
    <template x-if="show">
        <div
            x-transition
            x-bind:class="{
                'bg-gray-100 border-gray-200 text-gray-800 dark:bg-white/10 dark:border-white/20 dark:text-white': type === 'default',
                'bg-teal-100 border-teal-200 text-teal-800 dark:bg-teal-800/10 dark:border-teal-900 dark:text-teal-500': type === 'success',
                'bg-blue-100 border-blue-200 text-blue-800 dark:bg-blue-800/10 dark:border-blue-900 dark:text-blue-500': type === 'info',
                'bg-yellow-100 border-yellow-200 text-yellow-800 dark:bg-yellow-800/10 dark:border-yellow-900 dark:text-yellow-500': type === 'warning',
                'bg-red-100 border-red-200 text-red-800 dark:bg-red-800/10 dark:border-red-900 dark:text-red-500': type === 'error',
            }"
            class="max-w-xs w-full border text-sm rounded-lg shadow-lg"
            role="alert"
            tabindex="-1"
        >
            <div class="flex p-4">
                <span x-text="message"></span>

                <button type="button"
                    class="ms-auto inline-flex shrink-0 justify-center items-center size-5 rounded-lg opacity-50 hover:opacity-100 focus:outline-hidden focus:opacity-100"
                    x-on:click="close()"
                    aria-label="Close">
                    <span class="sr-only">Close</span>
                    <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 6 6 18"></path>
                        <path d="m6 6 12 12"></path>
                    </svg>
                </button>
            </div>
        </div>
    </template>
</div>
