@props(['options' => [], 'placeholder' => 'Seçiniz'])

@php
    $options = is_array($options) ? $options : $options->toArray();
@endphp

<div class="custom-select-wrap" x-data="customSelect(@js(['options' => $options, 'placeholder' => $placeholder]))" x-id="['dropdown']" @click.outside="open = false" @@livewire:navigated.window="open = false">
    <select {{ $attributes->merge(['class' => 'custom-select-hidden']) }} x-ref="selectEl">
        @foreach($options as $val => $label)
            <option value="{{ $val }}">{{ $label }}</option>
        @endforeach
    </select>
    <button type="button" class="custom-select-trigger"
        :id="$id('dropdown')"
        aria-haspopup="listbox"
        :aria-expanded="open"
        @click="open = !open"
        :aria-activedescendant="open ? $id('dropdown-option-' + value) : null">
        <span class="custom-select-value" x-text="getLabel()"></span>
        <span class="custom-select-chevron" :class="{ 'custom-select-chevron--open': open }">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="16" height="16">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </span>
    </button>
    <div class="custom-select-dropdown"
        x-ref="dropdown"
        x-show="open"
        x-transition:enter="custom-select-enter"
        x-transition:enter-start="custom-select-enter-start"
        x-transition:enter-end="custom-select-enter-end"
        x-transition:leave="custom-select-leave"
        x-transition:leave-start="custom-select-leave-start"
        x-transition:leave-end="custom-select-leave-end"
        x-cloak
        role="listbox"
        :aria-labelledby="$id('dropdown')">
        @foreach($options as $val => $label)
            <div role="option"
                :id="$id('dropdown-option-{{ $val }}')"
                class="custom-select-option"
                :class="{ 'custom-select-option--active': value == '{{ $val }}' }"
                @click="choose('{{ $val }}')"
                tabindex="-1">
                {{ $label }}
            </div>
        @endforeach
    </div>
</div>
