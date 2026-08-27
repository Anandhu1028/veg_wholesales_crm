<div
    x-data="{
        messages: [],
        add(message, type = 'success') {
            const id = Date.now();
            this.messages.push({ id, message, type });
            setTimeout(() => this.remove(id), 4000);
        },
        remove(id) {
            this.messages = this.messages.filter(m => m.id !== id);
        }
    }"
    x-init="
        @if(session('success'))
            add('{{ addslashes(session('success')) }}', 'success');
        @endif
        @if(session('error'))
            add('{{ addslashes(session('error')) }}', 'error');
        @endif
        @if($errors->any())
            add('{{ addslashes($errors->first()) }}', 'error');
        @endif
    "
    class="fixed bottom-5 right-5 z-50 flex flex-col gap-2 max-w-sm w-full pointer-events-none"
>
    <template x-for="item in messages" :key="item.id">
        <div
            x-show="true"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 translate-y-2"
            :class="item.type === 'success' ? 'bg-emerald-800 border-emerald-700 text-white' : 'bg-rose-800 border-rose-700 text-white'"
            class="pointer-events-auto rounded-xl p-4 shadow-lg border flex items-center justify-between gap-3 text-sm font-medium"
        >
            <div class="flex items-center gap-2.5">
                <template x-if="item.type === 'success'">
                    <svg class="w-5 h-5 text-emerald-300 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </template>
                <template x-if="item.type === 'error'">
                    <svg class="w-5 h-5 text-rose-300 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </template>
                <span x-text="item.message"></span>
            </div>
            <button @click="remove(item.id)" class="text-white/70 hover:text-white focus:outline-none">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    </template>
</div>
