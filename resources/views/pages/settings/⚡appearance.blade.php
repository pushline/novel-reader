<?php

use Livewire\Component;
use Livewire\Attributes\Title;

new #[Title('Appearance settings')] class extends Component {
    public string $theme_preference = 'dark';

    public int $reader_font_size = 18;

    public string $reader_font_family = 'serif';

    public float $reader_line_height = 1.75;

    public int $reader_content_width = 760;

    public function mount(): void
    {
        $this->fill(auth()->user()->only([
            'theme_preference',
            'reader_font_size',
            'reader_font_family',
            'reader_line_height',
            'reader_content_width',
        ]));
    }

    public function save(): void
    {
        $validated = $this->validate([
            'theme_preference' => ['required', 'in:light,dark'],
            'reader_font_size' => ['required', 'integer', 'min:16', 'max:24'],
            'reader_font_family' => ['required', 'in:serif,sans,mono'],
            'reader_line_height' => ['required', 'numeric', 'min:1.45', 'max:2.1'],
            'reader_content_width' => ['required', 'integer', 'min:620', 'max:980'],
        ]);

        auth()->user()->update($validated);

        $this->dispatch('reader-settings-saved');
    }
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading class="sr-only">{{ __('Appearance settings') }}</flux:heading>

    <x-pages::settings.layout :heading="__('Reader')" :subheading="__('Update your reading and theme preferences')">
        <form wire:submit="save" class="grid gap-6">
            <flux:radio.group wire:model="theme_preference" variant="segmented" :label="__('Theme')">
                <flux:radio value="dark" icon="moon">{{ __('Dark') }}</flux:radio>
                <flux:radio value="light" icon="sun">{{ __('Light') }}</flux:radio>
            </flux:radio.group>

            <flux:field>
                <flux:label>{{ __('Font family') }}</flux:label>
                <flux:select wire:model="reader_font_family">
                    <flux:select.option value="serif">{{ __('Serif') }}</flux:select.option>
                    <flux:select.option value="sans">{{ __('Sans') }}</flux:select.option>
                    <flux:select.option value="mono">{{ __('Mono') }}</flux:select.option>
                </flux:select>
                <flux:error name="reader_font_family" />
            </flux:field>

            <div class="grid gap-4 md:grid-cols-3">
                <flux:input wire:model="reader_font_size" type="number" min="16" max="24" :label="__('Font size')" />
                <flux:input wire:model="reader_line_height" type="number" step="0.05" min="1.45" max="2.1" :label="__('Line height')" />
                <flux:input wire:model="reader_content_width" type="number" min="620" max="980" :label="__('Content width')" />
            </div>

            <div class="flex items-center gap-3">
                <flux:button type="submit" variant="primary">{{ __('Save') }}</flux:button>
                <span x-data="{ shown: false }" x-on:reader-settings-saved.window="shown = true; setTimeout(() => shown = false, 1800)" x-show="shown" class="text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('Saved') }}
                </span>
            </div>
        </form>
    </x-pages::settings.layout>
</section>
