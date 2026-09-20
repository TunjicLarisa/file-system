<script>
import {
    Folder,
    FolderOpen,
    Trash,
    CircleChevronLeft,
    CircleChevronRight,
} from 'lucide-vue-next'

export default {
    components: {
        Folder,
        FolderOpen,
        Trash,
        CircleChevronLeft,
        CircleChevronRight,
    },

    props: {
        folders: {
            type: Array,
            default: () => [],
        },

        pagination: {
            type: Object,
            default: null,
        },
    },

    emits: [
        'open',
        'delete',
        'change-page',
    ],

    computed: {
        totalFolders() {
            return this.pagination?.total ?? this.folders.length
        },
    },
}
</script>

<template>
    <section class="mb-8">
        <div class="mb-4 flex items-center justify-between">
            <h2 class="text-sm font-semibold tracking-wide text-gray-500">
                FOLDERS
            </h2>

            <span class="text-xs text-gray-400">
                {{ totalFolders }}
                folder{{ totalFolders === 1 ? '' : 's' }}
            </span>
        </div>

        <div
            v-if="folders.length > 0"
            class="space-y-2"
        >
            <div
                v-for="folder in folders"
                :key="folder.id"
                class="flex items-center justify-between rounded-lg border border-gray-200 px-4 py-3 transition hover:bg-gray-50"
            >
                <button
                    @click="$emit('open', folder)"
                    class="flex flex-1 items-center gap-3 text-left"
                >
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100 text-xl">
                        <Folder />
                    </div>

                    <div>
                        <div class="font-medium text-gray-900">
                            {{ folder.name }}
                        </div>

                        <div class="text-xs text-gray-400">
                            Folder
                        </div>
                    </div>
                </button>

                <button
                    @click="$emit('delete', folder)"
                    aria-label="Delete folder"
                    class="rounded-md px-3 py-2 text-sm text-gray-400 transition hover:bg-red-50 hover:text-red-600"
                >
                    <Trash />
                </button>
            </div>

            <div
                v-if="pagination && pagination.last_page > 1"
                class="mt-4 flex items-center justify-center gap-3"
            >
                <button
                    :disabled="pagination.current_page === 1"
                    @click="$emit(
                        'change-page',
                        pagination.current_page - 1
                    )"
                    class="cursor-pointer transition hover:scale-110 disabled:cursor-not-allowed disabled:opacity-40"
                >
                    <CircleChevronLeft class="h-6 w-6" />
                </button>

                <span class="text-sm text-gray-500">
                    {{ pagination.current_page }}
                    /
                    {{ pagination.last_page }}
                </span>

                <button
                    :disabled="
                        pagination.current_page ===
                        pagination.last_page
                    "
                    @click="$emit(
                        'change-page',
                        pagination.current_page + 1
                    )"
                    class="cursor-pointer transition hover:scale-110 disabled:cursor-not-allowed disabled:opacity-40"
                >
                    <CircleChevronRight class="h-6 w-6" />
                </button>
            </div>
        </div>

        <div
            v-else
            class="flex flex-col items-center justify-center rounded-lg border border-dashed border-gray-300 px-6 py-8 text-center"
        >
            <FolderOpen class="mb-2 h-8 w-8 text-gray-400" />

            <p class="text-sm text-gray-500">
                No folders in this directory.
            </p>
        </div>
    </section>
</template>