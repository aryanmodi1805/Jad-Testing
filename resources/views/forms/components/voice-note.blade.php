<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>

        <div x-data="{
        isRecording: false,
        audioUrl: $wire.entangle('{{ $getStatePath() }}').defer,
        mediaRecorder: null,
        audioChunks: [],

        startRecording() {
            if (this.isRecording) return;
            this.isRecording = true;
            navigator.mediaDevices.getUserMedia({ audio: true })
                .then(stream => {
                    this.mediaRecorder = new MediaRecorder(stream);
                    this.mediaRecorder.ondataavailable = event => {
                        if (event.data.size > 0) this.audioChunks.push(event.data);
                    };
                    this.mediaRecorder.start();
                    this.mediaRecorder.onstop = async () => {
                        const audioBlob = new Blob(this.audioChunks, { type: 'audio/mpeg' });
                        this.audioUrl = await this.blobToDataURL(audioBlob);
                        this.isRecording = false;
                        this.audioChunks = []; // Clear the buffer after processing
                    };
                })
                .catch(error => {
                    console.error('Error accessing the microphone: ', error);
                    this.isRecording = false;
                });
        },

        stopRecording() {
            if (!this.isRecording || !this.mediaRecorder) return;
            this.mediaRecorder.stop();

            this.mediaRecorder.stream.getTracks()
                .forEach( track => track.stop() );
            },

        deleteRecording() {
            this.audioUrl = null;
            this.audioChunks = []; // Clear any remaining audio chunks
            this.$wire.set('{{ $getStatePath() }}', null);
        },

        blobToDataURL(blob) {
            return new Promise(resolve => {
                const reader = new FileReader();
                reader.onloadend = () => resolve(reader.result);
                reader.readAsDataURL(blob);
            });
        }
         ,audioUrl: $wire.entangle('{{ $getStatePath() }}')
    }"
             {{ $attributes->merge($getExtraAttributes() , escape: false)->class(['flex space-x-4']) }}
        >
            <audio :src="audioUrl" controls x-show="audioUrl" class="mt-2 "></audio>
            <input type="hidden" x-bind:value="audioUrl" x-model="audioUrl" />
            <div class="flex space-x-4">
                <x-filament::button icon="heroicon-s-microphone" color="primary" type="button" x-on:click="startRecording" x-show="!isRecording && !audioUrl" class="flex items-center space-x-2">
                    @lang('string.wizard.record.record')
                </x-filament::button>
                <x-filament::button icon="heroicon-s-stop" color="info" type="button" x-on:click="stopRecording" x-show="isRecording" class="flex items-center space-x-2">
                    @lang('string.wizard.record.stop')

                </x-filament::button>
                <x-filament::icon-button icon="heroicon-s-trash" color="danger" type="icon" x-on:click="deleteRecording" x-show="audioUrl" class="flex items-center space-x-2">
                    @lang('string.wizard.record.delete')
                </x-filament::icon-button>
            </div>


    </div>
</x-dynamic-component>
