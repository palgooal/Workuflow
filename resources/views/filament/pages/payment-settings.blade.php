<x-filament-panels::page>
    <x-filament::section>
        <form wire:submit="save">
            {{ $this->form }}

            <div class="mt-6 flex flex-wrap gap-3">
                <x-filament::button type="submit" color="success" icon="heroicon-o-check">
                    حفظ الإعدادات
                </x-filament::button>

                <x-filament::button
                    type="button"
                    wire:click="testConnection"
                    color="info"
                    icon="heroicon-o-signal"
                >
                    اختبار الاتصال
                </x-filament::button>

                <x-filament::button
                    type="button"
                    wire:click="createReceiverAddress"
                    color="warning"
                    icon="heroicon-o-map-pin"
                >
                    إنشاء Receiver Address
                </x-filament::button>
            </div>
        </form>
    </x-filament::section>
</x-filament-panels::page>
