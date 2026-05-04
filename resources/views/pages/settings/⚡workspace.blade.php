<?php

use App\Models\AppSetting;
use Flux\Flux;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Workspace settings')] class extends Component {
    public bool $registrationEnabled = true;

    public function mount(): void
    {
        $this->registrationEnabled = (bool) AppSetting::get('registration_enabled', true);
    }

    public function updatedRegistrationEnabled(bool $value): void
    {
        AppSetting::set('registration_enabled', $value);

        Flux::toast(
            variant: 'success',
            text: $value
                ? __('Registration enabled.')
                : __('Registration disabled.'),
        );
    }
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <x-pages::settings.layout :heading="__('Workspace')" :subheading="__('Workspace-wide configuration.')">
        <flux:card>
            <flux:field variant="inline">
                <div>
                    <flux:label>{{ __('Public registration') }}</flux:label>
                    <flux:description>{{ __('Allow anyone to create an account from the welcome page. Disable this once your team is set up.') }}</flux:description>
                </div>
                <flux:switch wire:model.live="registrationEnabled" />
            </flux:field>
        </flux:card>
    </x-pages::settings.layout>
</section>
