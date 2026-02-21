@php

    $extraAlpineAttributes = $getExtraAlpineAttributes();
    $id = $getId();
    $isConcealed = $isConcealed();
    $isDisabled = $isDisabled();
    $statePath = $getStatePath();
    $hintActions = $getHintActions();
    $numberLength = $getNumberLength();
    $isAutofocused = $isAutofocused();
@endphp

<x-dynamic-component
    :component="$getFieldWrapperView()"
    :id="$getId()"
    :label="$getLabel()"
    :label-sr-only="$isLabelHidden()"
    :helper-text="$getHelperText()"
    :hint="$getHint()"
    :hint-icon="$getHintIcon()"
    :required="$isRequired()"
    :state-path="$getStatePath()"
>
    <div x-data="{
	    state: '{{ $getStatePath() }}',
	    length: {{$numberLength}},
	    autoFocus: {{$isAutofocused ? 'true' : 'false'}},
	    enabledIndex: 1,
	    globalDisabled: {{$isDisabled ? 'true' : 'false'}},
            init() {
                if (this.autoFocus && ! this.globalDisabled) {
                    this.$nextTick(() => this.$refs[1]?.focus());
                }
            },
            isFieldDisabled(index) {
                return this.globalDisabled || index > this.enabledIndex;
            },
            handleInput(e, index) {
                const input = e.target;
                input.value = input.value.replace(/[^0-9]/g, '').substring(0, 1);

                if (! input.value) {
                    this.enabledIndex = Math.max(1, index);
                    this.clearAfter(index);
                    this.updateState();
                    return;
                }

                this.enabledIndex = Math.min(this.length, index + 1);

                this.$nextTick(() => {
                    const next = this.$refs[index + 1];
                    if (next && ! next.disabled) {
                        next.focus();
                        next.select();
                    }
                });

                this.updateState();
            },

            handlePaste(e) {
                const paste = e.clipboardData.getData('text').replace(/[^0-9]/g, '').substring(0, this.length);
                const inputs = Array.from(Array(this.length));

                inputs.forEach((element, i) => {
                    const ref = this.$refs[(i + 1)];
                    if (! ref) {
                        return;
                    }

                    ref.value = paste[i] || '';
                });

                this.enabledIndex = Math.min(this.length, paste.length + 1);
                this.updateState();
            },

            handleBackspace(e) {
                const ref = parseInt(e.target.getAttribute('x-ref'), 10);

                if (! ref) {
                    return;
                }

                e.preventDefault();

                const hadValue = e.target.value !== '';
                e.target.value = '';

                if (hadValue) {
                    this.enabledIndex = ref;
                } else {
                    this.enabledIndex = Math.max(1, ref - 1);
                }

                this.clearAfter(this.enabledIndex);

                const focusIndex = hadValue ? ref : Math.max(1, ref - 1);
                const target = this.$refs[focusIndex];
                target?.focus();
                target?.select();

                this.updateState();
            },

            clearAfter(index) {
                for (let pointer = index + 1; pointer <= this.length; pointer++) {
                    const input = this.$refs[pointer];
                    if (input) {
                        input.value = '';
                    }
                }
            },

            updateState() {
                this.state = Array.from({ length: this.length }, (_, idx) => {
                    const el = this.$refs[idx + 1];
                    return el?.value ?? '';
                }).join('');

                @this.set('{{ $getStatePath() }}', this.state);
            },
        }">
        <div class="flex justify-start gap-3 pt-3 pb-2 h-16" dir="ltr">

            @foreach(range(1, $numberLength) as $column)
                <x-filament::input.wrapper
                    :disabled="$isDisabled"
                    :valid="! $errors->has($statePath)"
                    :attributes="
                        \Filament\Support\prepare_inherited_attributes($getExtraAttributeBag())
                        ->class(['fi-fo-text-input overflow-hidden'])
                    "
                >
                    <input
                        type="tel"
                        maxlength="1"
                        x-ref="{{$column}}"
                        required
                        inputmode="numeric"
                        pattern="[0-9]*"
                        autocomplete="one-time-code"
                        dir="ltr"
                        class="fi-input block w-full border-none text-base text-gray-950 transition duration-75 placeholder:text-gray-400 focus:ring-0 dark:text-white dark:placeholder:text-gray-500 sm:leading-6 bg-white/0 text-center"
                        x-on:input="handleInput($event, {{$column}})"
                        x-on:paste="handlePaste($event)"
                        x-on:keydown.backspace="handleBackspace($event)"
                        x-bind:disabled="isFieldDisabled({{$column}})"
                    />

                </x-filament::input.wrapper>
            @endforeach

        </div>
    </div>
</x-dynamic-component>
