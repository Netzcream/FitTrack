<section class="w-full">
    @include('tenant.partials.landing-heading')

    @section('title', __('tenant.landing.banners.title') . ' - ' . __('site.dashboard'))

    <x-tenant.landing.layout width="max-w-full" :heading="__('tenant.landing.banners.title')" :subheading="__('tenant.landing.banners.subtitle')">

        <form wire:submit="save" class="my-6  space-y-6 " enctype="multipart/form-data">




            <div class="my-10 max-w-lg">
                <div class="space-y-4">
                    <div class="grid md:grid-cols-2  gap-4">
                        <div class="flex items-center space-x-2">
                            <input id="show" type="checkbox" wire:model.defer="show" @checked($show)
                                class="rounded border-gray-300 text-sky-600 shadow-sm focus:ring-sky-500" />
                            <label for="show" class="text-sm text-zinc-800 dark:text-white">¿Activo?</label>
                        </div>
                    </div>



                    <div class="flex items-center gap-4">
                        <div class="flex items-center justify-end">
                            <flux:button variant="primary" type="submit" class="w-full">{{ __('site.save') }}
                            </flux:button>
                        </div>

                        <x-tenant.action-message class="me-3" on="updated">
                            {{ __('site.saved') }}
                        </x-tenant.action-message>
                    </div>

                    <flux:separator variant="subtle" />

                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <flux:input wire:model.defer="banner_text" :label="__('tenant.landing.banners.text')"
                                type="text" max-length="100" />
                        </div>
                        <div>
                            <flux:input wire:model.defer="banner_link" :label="__('tenant.landing.banners.link')"
                                type="url" max-length="256" />
                        </div>
                        <div>
                            <flux:select wire:model.defer="banner_target" :label="__('tenant.landing.banners.target')">
                                <option value="_self">{{ __('tenant.landing.banners._self') }}</option>
                                <option value="_blank">{{ __('tenant.landing.banners._blank') }}</option>
                            </flux:select>
                        </div>
                        <div class="flex items-center space-x-2">
                            <input id="banner_active" type="checkbox" wire:model.defer="banner_active"
                                class="rounded border-gray-300 text-sky-600 shadow-sm focus:ring-sky-500" />
                            <label for="banner_active" class="text-sm text-zinc-800 dark:text-white">¿Activo?</label>
                        </div>

                        <div class="col-span-2">
                            <x-preline.file-upload name="banner_image" :label="__('tenant.landing.banners.desktop_image')" :preview="$banner_image"
                                :uploadedUrl="isset($banner_uuid)
                                    ? \App\Models\LandingBanner::where('uuid', $banner_uuid)
                                        ->first()
                                        ?->getFirstMediaUrl('cover')
                                    : null" width="128" height="96" radius="rounded-md" />
                        </div>
                        <div class="col-span-2">
                            <x-preline.file-upload name="banner_image_mobile" :label="__('tenant.landing.banners.mobile_image')" :preview="$banner_image_mobile"
                                :uploadedUrl="isset($banner_uuid)
                                    ? \App\Models\LandingBanner::where('uuid', $banner_uuid)
                                        ->first()
                                        ?->getFirstMediaUrl('cover_mobile')
                                    : null" width="96" height="128" radius="rounded-md" />
                        </div>
                    </div>


                    <div class="flex gap-2 mt-4">
                        <flux:button type="button" variant="primary" wire:click="saveBanner">
                            {{ $edit_mode ? __('site.save') : __('site.add') }}
                        </flux:button>
                        @if ($edit_mode)
                            <flux:button type="button" variant="subtle" wire:click="newBanner">
                                {{ __('site.cancel') }}
                            </flux:button>
                        @endif
                    </div>
                    @if ($errors->has('max_banners'))
                        <div class="text-red-600 text-sm mt-2">
                            {{ $errors->first('max_banners') }}
                        </div>
                    @endif
                </div>
            </div>

            <div class="flex flex-col my-6 w-full">
                <div class="-m-1.5 overflow-x-auto">
                    <div class="p-1.5 min-w-full inline-block align-middle">
                        <div class="overflow-hidden rounded-lg shadow">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700">
                                <thead>
                                    <tr>
                                        <th scope="col"
                                            class="ps-6 pe-2 py-3 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-500">
                                            {{ __('Orden') }}
                                        </th>

                                        <th scope="col"
                                            class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-500">
                                            {{ __('Contenido') }}</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-500">
                                            {{ __('Activo') }}</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-end text-xs font-medium text-gray-500 uppercase dark:text-neutral-500">
                                            {{ __('Acciones') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-neutral-700">
                                    @forelse ($banners as $banner)
                                        <tr @class([
                                            'opacity-60 grayscale' =>
                                                !empty($banner['to_delete']) && $banner['to_delete'],
                                        ])>

                                            <td
                                                class="ps-6 pe-2 py-4 text-sm text-gray-800 dark:text-neutral-200 text-center w-16">
                                                <div class="flex flex-col items-center gap-1">
                                                    <button wire:click="moveBannerUp('{{ $banner['uuid'] }}')"
                                                        class="hover:text-blue-600">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4"
                                                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                            stroke-width="2" stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            class="lucide lucide-chevron-up-icon lucide-chevron-up">
                                                            <path d="m18 15-6-6-6 6" />
                                                        </svg>


                                                    </button>
                                                    <button wire:click="moveBannerDown('{{ $banner['uuid'] }}')"
                                                        class="hover:text-blue-600">

                                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4"
                                                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                            stroke-width="2" stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            class="lucide lucide-chevron-down-icon lucide-chevron-down">
                                                            <path d="m6 9 6 6 6-6" />
                                                        </svg>

                                                    </button>
                                                </div>
                                            </td>

                                            <td
                                                class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-800 dark:text-neutral-200">
                                                <div class="flex items-start gap-4">
                                                    @if (!empty($banner['image']))
                                                        <div class="flex gap-2">
                                                            <img src="{{ $banner['image'] }}" alt="desktop"
                                                                class="h-12 w-12 rounded object-cover shrink-0" />
                                                            @php
                                                                $mobile = \App\Models\LandingBanner::where(
                                                                    'uuid',
                                                                    $banner['uuid'],
                                                                )
                                                                    ->first()
                                                                    ?->getFirstMediaUrl('cover_mobile', 'thumb');
                                                            @endphp
                                                            @if ($mobile)
                                                                <img src="{{ $mobile }}" alt="mobile"
                                                                    class="h-12 w-12 rounded object-cover shrink-0" />
                                                            @endif
                                                        </div>
                                                    @else
                                                        <img src="https://placehold.co/48x48?text=N/A" alt="cover"
                                                            class="h-12 w-12 rounded object-cover shrink-0" />
                                                    @endif
                                                    <div class="flex flex-col justify-center w-64">
                                                        <span
                                                            class="font-semibold text-gray-800 dark:text-neutral-200 truncate"
                                                            title="{{ $banner['text'] }}">
                                                            {{ $banner['text'] }}
                                                        </span>
                                                        @if ($banner['link'])
                                                            <a href="{{ $banner['link'] }}" target="_blank"
                                                                rel="noopener noreferrer"
                                                                class="mt-1 text-xs text-gray-600 dark:text-neutral-400 truncate block w-64 hover:underline"
                                                                title="{{ $banner['link'] }}">
                                                                {{ $banner['link'] }}
                                                            </a>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-end text-sm font-medium">
                                                <div class="flex flex-col justify-center align-middle w-4 py-4">
                                                    <span class="font-semibold  "
                                                        title="{{ $banner['active'] ? 'Activo' : 'Deshabilitado' }}">
                                                        @if ($banner['active'])
                                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                                class="text-green-600 dark:text-green-400 h-4 w-4"
                                                                viewBox="0 0 24 24" fill="none"
                                                                stroke="currentColor" stroke-width="2"
                                                                stroke-linecap="round" stroke-linejoin="round"
                                                                class="lucide lucide-check-icon lucide-check">
                                                                <path d="M20 6 9 17l-5-5" />
                                                            </svg>
                                                        @else
                                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                                class="text-red-600 dark:text-red-400 h-4 w-4"
                                                                viewBox="0 0 24 24" fill="none"
                                                                stroke="currentColor" stroke-width="2"
                                                                stroke-linecap="round" stroke-linejoin="round"
                                                                class="lucide lucide-x-icon lucide-x">
                                                                <path d="M18 6 6 18" />
                                                                <path d="m6 6 12 12" />
                                                            </svg>
                                                        @endif
                                                    </span>

                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-end text-sm font-medium">
                                                <div class="inline-flex gap-x-2">
                                                    @if (!empty($banner['to_delete']) && $banner['to_delete'])
                                                        <flux:button size="xs" type="button"
                                                            wire:click="restoreBanner('{{ $banner['uuid'] }}')">
                                                            {{ __('Restaurar') }}
                                                        </flux:button>
                                                    @else
                                                        <flux:button size="xs" type="button"
                                                            wire:click="editBanner('{{ $banner['uuid'] }}')">
                                                            {{ __('site.edit') }}
                                                        </flux:button>
                                                        <flux:button size="xs" type="button" variant="ghost"
                                                            wire:click="deleteBanner('{{ $banner['uuid'] }}')">
                                                            {{ __('site.delete') }}
                                                        </flux:button>
                                                    @endif
                                                </div>
                                                @if (!empty($banner['to_delete']) && $banner['to_delete'])
                                                    <span
                                                        class="block mt-2 text-xs text-red-400 uppercase font-bold tracking-wider">
                                                        {{ __('Marcada para eliminar') }}
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>

                                    @empty
                                        <tr>
                                            <td colspan="2"
                                                class="px-6 py-4 text-center text-gray-500 dark:text-neutral-500">
                                                {{ __('No hay banners creados.') }}
                                            </td>
                                        </tr>
                                    @endforelse

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>







            <div class="flex items-center gap-4">
                <div class="flex items-center justify-end">
                    <flux:button variant="primary" type="submit" class="w-full">{{ __('site.save') }}</flux:button>
                </div>

                <x-tenant.action-message class="me-3" on="updated">
                    {{ __('site.saved') }}
                </x-tenant.action-message>
            </div>

        </form>

    </x-tenant.landing.layout>
</section>
