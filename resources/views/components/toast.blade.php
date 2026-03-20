<!-- Toast Notification Container -->
<div 
    x-data="toastManager()" 
    @toast.window="addToast($event.detail)"
    class="fixed top-4 right-4 z-50 space-y-3 pointer-events-none"
>
    <template x-for="toast in toasts" :key="toast.id">
        <div 
            x-show="toast.show"
            x-transition:enter="transform ease-out duration-300 transition"
            x-transition:enter-start="translate-x-full opacity-0"
            x-transition:enter-end="translate-x-0 opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="pointer-events-auto w-96 max-w-full"
        >
            <div 
                class="bg-white rounded-lg shadow-2xl border-l-4 overflow-hidden"
                :class="{
                    'border-green-500': toast.type === 'success',
                    'border-red-500': toast.type === 'error',
                    'border-blue-500': toast.type === 'info',
                    'border-amber-500': toast.type === 'warning'
                }"
            >
                <div class="p-4 flex items-start gap-3">
                    <!-- Icon -->
                    <div class="flex-shrink-0">
                        <!-- Success Icon -->
                        <div x-show="toast.type === 'success'" class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <!-- Error Icon -->
                        <div x-show="toast.type === 'error'" class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <!-- Info Icon -->
                        <div x-show="toast.type === 'info'" class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <!-- Warning Icon -->
                        <div x-show="toast.type === 'warning'" class="w-10 h-10 bg-amber-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="flex-1 min-w-0">
                        <h4 
                            class="text-sm font-semibold mb-1"
                            :class="{
                                'text-green-900': toast.type === 'success',
                                'text-red-900': toast.type === 'error',
                                'text-blue-900': toast.type === 'info',
                                'text-amber-900': toast.type === 'warning'
                            }"
                            x-text="toast.title"
                        ></h4>
                        <p class="text-sm text-slate-600" x-text="toast.message"></p>
                        
                        <!-- Progress Bar -->
                        <div 
                            x-show="toast.duration > 0"
                            class="mt-2 h-1 bg-slate-200 rounded-full overflow-hidden"
                        >
                            <div 
                                class="h-full transition-all ease-linear"
                                :class="{
                                    'bg-green-500': toast.type === 'success',
                                    'bg-red-500': toast.type === 'error',
                                    'bg-blue-500': toast.type === 'info',
                                    'bg-amber-500': toast.type === 'warning'
                                }"
                                :style="`width: ${toast.progress}%; transition-duration: ${toast.duration}ms;`"
                            ></div>
                        </div>
                    </div>

                    <!-- Close Button -->
                    <button 
                        @click="removeToast(toast.id)"
                        class="flex-shrink-0 text-slate-400 hover:text-slate-600 transition"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </template>
</div>

<script>
    function toastManager() {
        return {
            toasts: [],
            nextId: 1,

            addToast(data) {
                const toast = {
                    id: this.nextId++,
                    type: data.type || 'info',
                    title: data.title || this.getDefaultTitle(data.type),
                    message: data.message || '',
                    duration: data.duration !== undefined ? data.duration : 5000,
                    show: false,
                    progress: 100
                };

                this.toasts.push(toast);

                // Trigger entrance animation
                this.$nextTick(() => {
                    const index = this.toasts.findIndex(t => t.id === toast.id);
                    if (index !== -1) {
                        this.toasts[index].show = true;
                        
                        // Start progress bar
                        if (toast.duration > 0) {
                            setTimeout(() => {
                                if (this.toasts[index]) {
                                    this.toasts[index].progress = 0;
                                }
                            }, 50);
                        }
                    }
                });

                // Auto dismiss
                if (toast.duration > 0) {
                    setTimeout(() => {
                        this.removeToast(toast.id);
                    }, toast.duration);
                }
            },

            removeToast(id) {
                const index = this.toasts.findIndex(t => t.id === id);
                if (index !== -1) {
                    this.toasts[index].show = false;
                    setTimeout(() => {
                        this.toasts = this.toasts.filter(t => t.id !== id);
                    }, 200);
                }
            },

            getDefaultTitle(type) {
                const titles = {
                    success: 'Success!',
                    error: 'Error!',
                    warning: 'Warning!',
                    info: 'Info'
                };
                return titles[type] || 'Notification';
            }
        }
    }

    // Global toast helper function
    window.toast = function(message, type = 'info', title = null, duration = 5000) {
        window.dispatchEvent(new CustomEvent('toast', {
            detail: { message, type, title, duration }
        }));
    };

    // Convenience methods
    window.toast.success = (message, duration = 5000) => toast(message, 'success', 'Success!', duration);
    window.toast.error = (message, duration = 5000) => toast(message, 'error', 'Error!', duration);
    window.toast.warning = (message, duration = 5000) => toast(message, 'warning', 'Warning!', duration);
    window.toast.info = (message, duration = 5000) => toast(message, 'info', 'Info', duration);
</script>
