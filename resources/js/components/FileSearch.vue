<script>
import { File } from 'lucide-vue-next';

export default {
    components: {
        File,
    },

    props: {
        modelValue: {
            type: String,
            required: true,
        },

        searchAllFiles: {
            type: Boolean,
            required: true,
        },

        suggestions: {
            type: Array,
            default: () => [],
        },
    },

    emits: [
        'update:modelValue',
        'update:searchAllFiles',
        'search',
        'select-suggestion',
    ],

    computed: {
        searchValue: {
            get() {
                return this.modelValue;
            },

            set(value) {
                this.$emit('update:modelValue', value);
            },
        },

        searchAllValue: {
            get() {
                return this.searchAllFiles;
            },

            set(value) {
                this.$emit('update:searchAllFiles', value);
            },
        },
    },
};
</script>

<template>
    <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
            <div class="relative flex-1">
                <input
                    v-model="searchValue"
                    @keyup.enter="$emit('search')"
                    type="text"
                    placeholder="Search files..."
                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 outline-none placeholder:text-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                />

                <div
                    v-if="suggestions.length > 0"
                    class="absolute z-50 mt-1 w-full overflow-hidden rounded-lg border border-gray-200 bg-white shadow-lg"
                >
                    <button
                        v-for="file in suggestions"
                        :key="file.id"
                        @click="$emit('select-suggestion', file)"
                        class="flex w-full items-center gap-3 px-4 py-3 text-left hover:bg-gray-50"
                    >
                        <File class="h-4 w-4 shrink-0 text-gray-400" />

                        <div>
                            <div class="text-sm font-medium text-gray-900">
                                {{ file.name }}
                            </div>

                            <div
                                v-if="searchAllFiles && file.folder_path"
                                class="mt-0.5 text-xs text-gray-400"
                            >
                                {{ file.folder_path }}
                            </div>
                        </div>
                    </button>
                </div>
            </div>

            <label class="flex items-center gap-2 text-sm text-gray-600">
                <input
                    v-model="searchAllValue"
                    type="checkbox"
                    class="h-4 w-4 rounded border-gray-300"
                />

                Search all files
            </label>
        </div>
    </div>
</template>
