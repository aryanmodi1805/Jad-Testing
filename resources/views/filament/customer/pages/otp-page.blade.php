@php use Filament\Support\Facades\FilamentView; @endphp
@php use Filament\View\PanelsRenderHook; @endphp
<x-filament-panels::page.simple>

    @if($this->is_cooldown_mode)

        <div class="bg-orange-100 border-l-4 border-orange-500 text-orange-700 p-4 py-5" role="alert">
            <p class="font-bold">  {{__('otp.notifications.title')}}</p>
            <p>  {{__('otp.validation.temporarily_disabled',['time'=>$this->cooldown_end_at->diffForHumans(now())])}}</p>
        </div>

    @else
        {{ FilamentView::renderHook(PanelsRenderHook::AUTH_LOGIN_FORM_BEFORE, scopes: $this->getRenderHookScopes()) }}

        <div class="w-full mt-0 " x-ref="formDiv"
             @if(app()->getLocale() =='ar') style="    direction: ltr; text-align: right" @endif >
            <x-filament-panels::form wire:submit="verifyOtp" class="mt-0">
                {{ $this->form }}
                <x-filament-panels::form.actions
                    :actions="$this->getOtpFormActions()"
                    :full-width="true"
                />

                <div wire:ignore x-data="{
                        timeLeft: $wire.countDown,
                        timerRunning: false,
                        resendCode() {
                            this.timeLeft = $wire.countDown;
                            this.$refs.resendLink.classList.add('hidden');
                            this.$refs.formDiv.classList.add('hidden');
{{--                            this.$refs.timerWrapper.classList.remove('hidden');--}}
                            this.startTimer();
                            this.$dispatch('resendCode');
                        },
                        startTimer() {
                            this.timerRunning = true;
                            const interval = setInterval(() => {
                                if (this.timeLeft <= 0) {
                                    clearInterval(interval);
                                    this.timerRunning = false;
                                    this.$refs.resendLink.classList.remove('hidden');
                                    this.$refs.timerWrapper.classList.add('hidden');
                                }
                                this.timeLeft -= 1;
                                this.$refs.timeLeft.value = this.timeLeft;
                            }, 1000);
                        },
                        init() {
                            this.startTimer();
                            document.addEventListener('countDownStarted', () => {
                                this.startTimer();
                            });
                        }
                    }">
                    <div x-show="timerRunning" class="timer font-semibold resend-link text-end text-primary-600 text-sm"
                         x-ref="timerWrapper">
                        <span x-text="timeLeft"></span> {{ __('otp.view.time_left') }}
                    </div>
                    <a x-on:click="resendCode" x-show="!timerRunning" x-ref="resendLink"
                       class="hidden cursor-pointer font-semibold resend-link text-end text-primary-600 text-sm">
                        {{ __('otp.view.resend_code') }}
                    </a>
                    <input type="hidden" x-ref="timeLeft" name="timeLeft"/>
                </div>

            </x-filament-panels::form>
        </div>
    @endif
        {{ FilamentView::renderHook(PanelsRenderHook::AUTH_LOGIN_FORM_AFTER, scopes: $this->getRenderHookScopes()) }}

</x-filament-panels::page.simple>
