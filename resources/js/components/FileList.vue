<script>
import {
    File,
    FileXCorner,
    Trash,
    CircleChevronLeft,
    CircleChevronRight,
} from 'lucide-vue-next';

export default {
    components: {
        File,
        FileXCorner,
        Trash,
        CircleChevronLeft,
        CircleChevronRight,
    },

    props: {
        files: {
            type: Array,
            default: () => [],
        },

        searchActive: {
            type: Boolean,
            default: false,
        },

        searchAllFiles: {
            type: Boolean,
            default: false,
        },

        filePagination: {
            type: Object,
            default: null,
        },

        searchPagination: {
            type: Object,
            default: null,
        },
    },

    emits: ['delete', 'change-file-page', 'change-search-page'],

    computed: {
        totalFiles() {
            if (this.searchActive) {
                return this.searchPagination?.total ?? this.files.length;
            }

            return this.filePagination?.total ?? this.files.length;
        },
    },
};
</script>

<template>
    <section>
        <div class="mb-4 flex items-center justify-between">
            <h2
                class="text-sm font-semibold tracking-wide text-gray-500 uppercase"
            >
                {{ searchActive ? 'Search results' : 'Files' }}
            </h2>

            <span class="text-xs text-gray-400">
                {{ totalFiles }}
                file{{ totalFiles === 1 ? '' : 's' }}
            </span>
        </div>

        <div v-if="files.length > 0" class="space-y-2">
            <div
                v-for="file in files"
                :key="file.id"
                class="flex items-center justify-between rounded-lg border border-gray-200 px-4 py-3 transition hover:bg-gray-50"
            >
                <div class="flex items-center gap-3">
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-lg bg-gray-100 text-gray-900"
                    >
                        <File class="h-5 w-5" />
                    </div>

                    <div>
                        <div class="font-medium text-gray-900">
                            {{ file.name }}
                        </div>

                        <div
                            v-if="searchAllFiles && file.folder_path"
                            class="text-xs text-gray-400"
                        >
                            {{ file.folder_path }}
                        </div>

                        <div v-else class="text-xs text-gray-400">File</div>
                    </div>
                </div>

                <button
                    @click="$emit('delete', file)"
                    aria-label="Delete file"
                    class="cursor-pointer rounded-md px-3 py-2 text-sm text-gray-400 transition hover:bg-red-50 hover:text-red-600"
                >
                    <Trash />
                </button>
            </div>
        </div>

        <div
            v-else
            class="flex flex-col items-center justify-center rounded-lg border border-dashed border-gray-300 px-6 py-8 text-center"
        >
            <FileXCorner class="mb-2 h-8 w-8 text-gray-400" />

            <p class="text-sm text-gray-500">
                {{
                    searchActive
                        ? 'No matching files found.'
                        : 'No files in this directory.'
                }}
            </p>
        </div>

        <!-- Normal file pagination -->

        <div
            v-if="
                !searchActive && filePagination && filePagination.last_page > 1
            "
            class="mt-4 flex items-center justify-center gap-3"
        >
            <button
                :disabled="filePagination.current_page === 1"
                @click="
                    $emit('change-file-page', filePagination.current_page - 1)
                "
                class="cursor-pointer transition hover:scale-110 disabled:cursor-not-allowed disabled:opacity-40"
            >
                <CircleChevronLeft class="h-6 w-6" />
            </button>

            <span class="text-sm text-gray-500">
                {{ filePagination.current_page }}
                /
                {{ filePagination.last_page }}
            </span>

            <button
                :disabled="
                    filePagination.current_page === filePagination.last_page
                "
                @click="
                    $emit('change-file-page', filePagination.current_page + 1)
                "
                class="cursor-pointer transition hover:scale-110 disabled:cursor-not-allowed disabled:opacity-40"
            >
                <CircleChevronRight class="h-6 w-6" />
            </button>
        </div>

        <!-- Search pagination -->

        <div
            v-if="
                searchActive &&
                searchPagination &&
                searchPagination.last_page > 1
            "
            class="mt-4 flex items-center justify-center gap-3"
        >
            <button
                :disabled="searchPagination.current_page === 1"
                @click="
                    $emit(
                        'change-search-page',
                        searchPagination.current_page - 1,
                    )
                "
                class="cursor-pointer transition hover:scale-110 disabled:cursor-not-allowed disabled:opacity-40"
            >
                <CircleChevronLeft class="h-6 w-6" />
            </button>

            <span class="text-sm text-gray-500">
                {{ searchPagination.current_page }}
                /
                {{ searchPagination.last_page }}
            </span>

            <button
                :disabled="
                    searchPagination.current_page === searchPagination.last_page
                "
                @click="
                    $emit(
                        'change-search-page',
                        searchPagination.current_page + 1,
                    )
                "
                class="cursor-pointer transition hover:scale-110 disabled:cursor-not-allowed disabled:opacity-40"
            >
                <CircleChevronRight class="h-6 w-6" />
            </button>
        </div>
    </section>
</template>
