<script>
import { ArrowLeft } from 'lucide-vue-next';

export default {
    components: {
        ArrowLeft,
    },

    props: {
        path: {
            type: Array,
            default: () => [],
        },
    },

    emits: ['back', 'home', 'navigate'],
};
</script>

<template>
    <div class="border-b border-gray-200 px-6 py-4">
        <div class="flex items-center">
            <button
                @click="$emit('back')"
                :disabled="path.length === 0"
                :class="[
                    'flex h-8 w-8 items-center justify-center rounded-lg border border-gray-300',
                    path.length === 0
                        ? 'pointer-events-none invisible'
                        : 'bg-white text-gray-600 hover:bg-gray-50',
                ]"
            >
                <ArrowLeft />
            </button>

            <div class="flex flex-wrap items-center gap-2 pl-2 text-sm">
                <button
                    @click="$emit('home')"
                    class="font-medium text-blue-600 hover:text-blue-800"
                >
                    Home
                </button>

                <template v-for="(folder, index) in path" :key="folder.id">
                    <span class="text-gray-400"> / </span>

                    <button
                        @click="$emit('navigate', index)"
                        class="font-medium text-blue-600 hover:text-blue-800"
                    >
                        {{ folder.name }}
                    </button>
                </template>
            </div>
        </div>
    </div>
</template>
