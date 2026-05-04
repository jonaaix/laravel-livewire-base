<?php

use App\Models\User;
use Flux\Flux;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Users')] class extends Component {
    public string $search = '';

    public ?int $editingUserId = null;

    public string $name = '';

    public string $email = '';

    public string $password = '';

    public bool $emailVerified = true;

    public bool $isDisabled = false;

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($this->editingUserId),
            ],
            'password' => $this->editingUserId
                ? ['nullable', 'string', 'min:8']
                : ['required', 'string', 'min:8'],
            'emailVerified' => ['boolean'],
            'isDisabled' => ['boolean'],
        ];
    }

    #[Computed]
    public function users()
    {
        return User::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('email', 'like', '%'.$this->search.'%');
                });
            })
            ->orderBy('created_at')
            ->get();
    }

    public function startCreate(): void
    {
        $this->resetForm();
        $this->modal('edit-user')->show();
    }

    public function startEdit(int $userId): void
    {
        $user = User::query()->findOrFail($userId);

        $this->editingUserId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->password = '';
        $this->emailVerified = $user->email_verified_at !== null;
        $this->isDisabled = (bool) $user->is_disabled;

        $this->resetValidation();
        $this->modal('edit-user')->show();
    }

    public function save(): void
    {
        $validated = $this->validate();

        if ($this->editingUserId) {
            $user = User::query()->findOrFail($this->editingUserId);
            $isSelf = $user->is(auth()->user());

            $user->name = $validated['name'];
            $user->email = $validated['email'];
            $user->email_verified_at = $this->emailVerified
                ? ($user->email_verified_at ?? now())
                : null;

            if (! $isSelf) {
                $user->is_disabled = $this->isDisabled;
            }

            if (filled($validated['password'])) {
                $user->password = $validated['password'];
            }

            $user->save();

            Flux::toast(variant: 'success', text: __('User updated.'));
        } else {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
                'is_disabled' => $this->isDisabled,
            ]);

            $user->forceFill([
                'email_verified_at' => $this->emailVerified ? now() : null,
            ])->save();

            Flux::toast(variant: 'success', text: __('User created.'));
        }

        $this->modal('edit-user')->close();
        $this->resetForm();
        unset($this->users);
    }

    public function delete(int $userId): void
    {
        $user = User::query()->findOrFail($userId);

        if ($user->is(auth()->user())) {
            return;
        }

        $user->delete();

        unset($this->users);

        Flux::toast(variant: 'success', text: __('User deleted.'));
    }

    private function resetForm(): void
    {
        $this->editingUserId = null;
        $this->name = '';
        $this->email = '';
        $this->password = '';
        $this->emailVerified = true;
        $this->isDisabled = false;
        $this->resetValidation();
    }
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <x-pages::settings.layout :heading="__('Users')" :subheading="__('Workspace members. Verify identities and remove unwanted accounts.')" content-class="">
        <div class="mb-4 flex items-center gap-3">
            <flux:input
                wire:model.live.debounce.250ms="search"
                icon="magnifying-glass"
                :placeholder="__('Search by name or email')"
                class="max-w-sm"
            />
            <flux:spacer />
            <flux:button wire:click="startCreate" variant="primary" icon="plus">
                {{ __('New user') }}
            </flux:button>
        </div>

        <div class="overflow-hidden rounded-xl ring-1 ring-zinc-950/5 dark:ring-white/10">
            <table class="w-full text-sm">
                <thead class="bg-zinc-50 text-zinc-500 dark:bg-zinc-800/50 dark:text-zinc-400">
                    <tr>
                        <th class="px-4 py-2.5 text-left font-medium">{{ __('Name') }}</th>
                        <th class="px-4 py-2.5 text-left font-medium">{{ __('Email') }}</th>
                        <th class="px-4 py-2.5 text-left font-medium">{{ __('Status') }}</th>
                        <th class="px-4 py-2.5 text-left font-medium">{{ __('Last login') }}</th>
                        <th class="px-4 py-2.5 text-left font-medium">{{ __('Joined') }}</th>
                        <th class="px-4 py-2.5 text-right font-medium">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse ($this->users as $user)
                        @php($isSelf = $user->is(auth()->user()))
                        <tr class="bg-white dark:bg-zinc-900">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2 font-medium">
                                    {{ $user->name }}
                                    @if ($isSelf)
                                        <flux:badge size="sm" color="zinc">{{ __('You') }}</flux:badge>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <span class="truncate">{{ $user->email }}</span>
                                    @if ($user->email_verified_at)
                                        <flux:icon.check-badge class="size-4 shrink-0 text-emerald-500" title="{{ __('Email verified') }}" />
                                    @else
                                        <flux:icon.exclamation-triangle class="size-4 shrink-0 text-amber-500" title="{{ __('Email not verified') }}" />
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                @if ($user->is_disabled)
                                    <flux:badge size="sm" color="red">{{ __('Disabled') }}</flux:badge>
                                @else
                                    <flux:badge size="sm" color="emerald">{{ __('Active') }}</flux:badge>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-zinc-600 dark:text-zinc-400">
                                {{ $user->last_login_at?->diffForHumans() ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-zinc-600 dark:text-zinc-400">
                                {{ $user->created_at->diffForHumans() }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-1">
                                    <flux:button size="xs" variant="ghost" icon="pencil-square" wire:click="startEdit({{ $user->id }})">
                                        {{ __('Edit') }}
                                    </flux:button>
                                    @unless ($isSelf)
                                        <flux:button
                                            size="xs"
                                            variant="ghost"
                                            icon="trash"
                                            wire:click="delete({{ $user->id }})"
                                            wire:confirm="{{ __('Delete this user permanently?') }}"
                                        >
                                            {{ __('Delete') }}
                                        </flux:button>
                                    @endunless
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr class="bg-white dark:bg-zinc-900">
                            <td colspan="6" class="px-4 py-8 text-center text-zinc-500">
                                {{ __('No users found.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <flux:modal name="edit-user" class="md:w-96">
            <form wire:submit="save" class="space-y-6">
                <div>
                    <flux:heading size="lg">
                        {{ $editingUserId ? __('Edit user') : __('New user') }}
                    </flux:heading>
                    <flux:subheading>
                        {{ $editingUserId ? __('Update the account details.') : __('Create a new user account.') }}
                    </flux:subheading>
                </div>

                <flux:input wire:model="name" :label="__('Name')" required />
                <flux:input wire:model="email" :label="__('Email')" type="email" required />

                <flux:input
                    wire:model="password"
                    :label="__('Password')"
                    type="password"
                    :required="! $editingUserId"
                    :description="$editingUserId ? __('Leave empty to keep the current password.') : null"
                />

                <flux:field variant="inline">
                    <div>
                        <flux:label>{{ __('Email verified') }}</flux:label>
                        <flux:description>{{ __('Mark the email address as verified.') }}</flux:description>
                    </div>
                    <flux:switch wire:model="emailVerified" />
                </flux:field>

                @if ($editingUserId !== auth()->id())
                    <flux:field variant="inline">
                        <div>
                            <flux:label>{{ __('Disabled') }}</flux:label>
                            <flux:description>{{ __('Disabled accounts cannot log in.') }}</flux:description>
                        </div>
                        <flux:switch wire:model="isDisabled" />
                    </flux:field>
                @endif

                <div class="flex justify-end gap-2">
                    <flux:modal.close>
                        <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                    </flux:modal.close>
                    <flux:button type="submit" variant="primary">
                        {{ $editingUserId ? __('Save') : __('Create') }}
                    </flux:button>
                </div>
            </form>
        </flux:modal>
    </x-pages::settings.layout>
</section>
