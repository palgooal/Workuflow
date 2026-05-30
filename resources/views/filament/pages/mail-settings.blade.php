<x-filament-panels::page>
    <x-filament::section>
        <form wire:submit="save">
            {{ $this->form }}

            <div class="mt-6 flex gap-3">
                <x-filament::button type="submit" color="success" icon="heroicon-o-check">
                    حفظ الإعدادات
                </x-filament::button>

                <x-filament::button
                    type="button"
                    wire:click="sendTest"
                    color="info"
                    icon="heroicon-o-paper-airplane"
                >
                    إرسال بريد تجريبي
                </x-filament::button>
            </div>
        </form>
    </x-filament::section>
</x-filament-panels::page>
