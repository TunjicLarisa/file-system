<script>
import { Folder, FolderPlus, FilePlusCorner, FolderOpen, FileText, File, FileXCorner, Trash, ArrowLeft, CircleChevronLeft, CircleChevronRight  } from 'lucide-vue-next'
export default {
    components: {
        Folder, FolderPlus, FilePlusCorner, FolderOpen, FileText, File, FileXCorner, Trash, ArrowLeft, CircleChevronLeft, CircleChevronRight
    },
    data() {
        return {
            folders: [],
            files: [],

            currentFolderId: null,
            path: [],

            search: '',
            searchAllFiles: false,

            suggestions: [],
            searchResults: [],

            searchTimer: null,

            folderPage: 1,
            filePage: 1,

            folderPagination: null,
            filePagination: null,
        }
    },
    watch: {
        search() {
            clearTimeout(this.searchTimer)

            if (!this.search.trim()) {
                this.suggestions = []
                this.searchResults = []
                return
            }

            this.searchTimer = setTimeout(() => {
                this.loadSuggestions()
            }, 300)
        },

        searchAllFiles() {
            if (this.search.trim()) {
                this.loadSuggestions()
            }
        },
    },
    computed: {
            currentFolder() {
                if (this.currentFolderId === null) {
                    return null
                }

                return this.folders.find(folder => {
                    return folder.id === this.currentFolderId
                })
            },

            childFolders() {
                return this.folders.filter(folder => {
                    return folder.parent_id === this.currentFolderId
                })
            },

            currentFiles() {
                return this.files.filter(file => {
                    return file.folder_id === this.currentFolderId
                })
            },

            breadcrumbs() {
                const result = []
                let folderId = this.currentFolderId

                while (folderId !== null) {
                    const folder = this.folders.find(folder => {
                        return folder.id === folderId
                    })

                    if (!folder) {
                        break
                    }

                    result.unshift(folder)
                    folderId = folder.parent_id
                }

                return result
            },
             displayedFiles() {
                if (this.search.trim()) {
                    return this.searchResults
                }

                return this.files
            },

        },

        methods: {
            async loadFolder() {
                const folderParams = new URLSearchParams({
                    page: this.folderPage,
                })

                const fileParams = new URLSearchParams({
                    page: this.filePage,
                })

                if (this.currentFolderId !== null) {
                    folderParams.set('parent_id', this.currentFolderId)
                    fileParams.set('folder_id', this.currentFolderId)
                }

                const [foldersResponse, filesResponse] = await Promise.all([
                    fetch(`/api/folders?${folderParams.toString()}`),
                    fetch(`/api/files?${fileParams.toString()}`),
                ])

                if (!foldersResponse.ok || !filesResponse.ok) {
                    console.error('Could not load folder contents')
                    return
                }

                const foldersData = await foldersResponse.json()
                const filesData = await filesResponse.json()

                this.folders = foldersData.data ?? []
                this.files = filesData.data ?? []

                this.folderPagination = foldersData.meta ?? null
                this.filePagination = filesData.meta ?? null
            },
            async createFolder() {
                const name = prompt('Folder name:')

                if (name === null) {
                    return
                }

                try {
                    const response = await fetch('/api/folders', {
                        method: 'POST',

                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                        },

                        body: JSON.stringify({
                            name: name.trim(),
                            parent_id: this.currentFolderId,
                        }),
                    })

                    const data = await response.json()

                    if (!response.ok) {
                        if (response.status === 422) {
                            const firstError =
                                Object.values(data.errors ?? {})[0]?.[0]

                            alert(firstError ?? data.message)
                            return
                        }

                        alert(data.message ?? `Error ${response.status}`)
                        return
                    }

                    await this.loadFolder()

                } catch (error) {
                    console.error(error)
                    alert(error.message)
                }
            },
            async deleteFolder(folder) {
                const confirmed = confirm(
                    `Delete folder "${folder.name}"?`
                )

                if (!confirmed) {
                    return
                }

                try {
                    const response = await fetch(
                        `/api/folders/${folder.id}`,
                        {
                            method: 'DELETE',
                            headers: {
                                'Accept': 'application/json',
                            },
                        }
                    )

                    if (!response.ok) {
                        let message = 'Could not delete folder.'

                        try {
                            const data = await response.json()
                            message = data.message ?? message
                        } catch {
                            // response no JSON body
                        }

                        alert(message)
                        return
                    }

                    await this.loadFolder()
                } catch (error) {
                    console.error(error)
                    alert(error.message)
                }
            },
            async openFolder(folder) {
                this.path.push({
                    id: folder.id,
                    name: folder.name,
                })

                this.currentFolderId = folder.id

                this.folderPage = 1
                this.filePage = 1

                this.search = ''
                this.suggestions = []
                this.searchResults = []

                await this.loadFolder()
            },
            async createFile() {
                const name = prompt('File name:')

                if (name === null) {
                    return
                }

                try {
                    const response = await fetch('/api/files', {
                        method: 'POST',

                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                        },

                        body: JSON.stringify({
                            name: name.trim(),
                            folder_id: this.currentFolderId,
                        }),
                    })

                    const data = await response.json()

                    if (!response.ok) {
                        if (response.status === 422) {
                            const firstError =
                                Object.values(data.errors ?? {})[0]?.[0]

                            alert(firstError ?? data.message)
                            return
                        }

                        alert(data.message ?? `Error ${response.status}`)
                        return
                    }

                    await this.loadFolder()

                } catch (error) {
                    console.error(error)
                    alert(error.message)
                }
            },
            async deleteFile(file) {
                const confirmed = confirm(
                    `Delete file "${file.name}"?`
                )

                if (!confirmed) {
                    return
                }

                try {
                    const response = await fetch(
                        `/api/files/${file.id}`,
                        {
                            method: 'DELETE',
                            headers: {
                                'Accept': 'application/json',
                            },
                        }
                    )

                    if (!response.ok) {
                        let message = 'Could not delete file.'

                        try {
                            const data = await response.json()
                            message = data.message ?? message
                        } catch {
                            // response no JSON body
                        }

                        alert(message)
                        return
                    }

                    await this.loadFolder()
                } catch (error) {
                    console.error(error)
                    alert(error.message)
                }
            },
        async loadSuggestions() {
            const query = this.search.trim()

            if (!query) {
                this.suggestions = []
                return
            }

            const params = new URLSearchParams({
                q: query,
                all: this.searchAllFiles ? '1' : '0',
            })

            if (
                !this.searchAllFiles &&
                this.currentFolderId !== null
            ) {
                params.append(
                    'folder_id',
                    this.currentFolderId
                )
            }

            try {
                const response = await fetch(
                    `/api/search/suggestions?${params.toString()}`,
                    {
                        headers: {
                            Accept: 'application/json',
                        },
                    }
                )

                const data = await response.json()

                if (!response.ok) {
                    this.suggestions = []
                    return
                }

                this.suggestions = data.data ?? data

            } catch (error) {
                console.error(error)
                this.suggestions = []
            }
        },
       async exactSearch() {
            clearTimeout(this.searchTimer)

            const query = this.search.trim()

            if (!query) {
                this.searchResults = []
                this.suggestions = []
                return
            }

            const params = new URLSearchParams({
                q: query,
                all: this.searchAllFiles ? '1' : '0',
            })

            if (
                !this.searchAllFiles &&
                this.currentFolderId !== null
            ) {
                params.append(
                    'folder_id',
                    this.currentFolderId
                )
            }

            try {
                const response = await fetch(
                    `/api/search/exact?${params.toString()}`,
                    {
                        headers: {
                            Accept: 'application/json',
                        },
                    }
                )

                const data = await response.json()

                if (!response.ok) {
                    this.searchResults = []
                    return
                }

                this.searchResults = data.data ?? data

                this.suggestions = []

            } catch (error) {
                console.error(error)
                this.searchResults = []
            }
        },
        async selectSuggestion(file) {
            this.search = file.name
            this.suggestions = []

            await this.exactSearch()
        },
        async goHome() {
            this.path = []
            this.currentFolderId = null
            this.search = ''
            this.folderPage = 1
            this.filePage = 1

            await this.loadFolder()
        },
        async goBack() {
            if (this.path.length === 0) {
                return
            }

            this.path.pop()

            if (this.path.length === 0) {
                this.currentFolderId = null
            } else {
                this.currentFolderId =
                    this.path[this.path.length - 1].id
            }

            this.search = ''
            this.folderPage = 1
            this.filePage = 1

            await this.loadFolder()
        },
        async goToPath(index) {
            this.path = this.path.slice(0, index + 1)

            this.currentFolderId =
                this.path[this.path.length - 1].id

            this.search = ''
            this.folderPage = 1
            this.filePage = 1

            await this.loadFolder()
        },
        async changeFolderPage(page) {
            if (
                !this.folderPagination ||
                page < 1 ||
                page > this.folderPagination.last_page
            ) {
                return;
            }

            this.folderPage = page;
            await this.loadFolder();
        },

        async changeFilePage(page) {
            if (
                !this.filePagination ||
                page < 1 ||
                page > this.filePagination.last_page
            ) {
                return;
            }

            this.filePage = page;
            await this.loadFolder();
        },
        
    },
    mounted() {
        this.loadFolder()
    },
}
</script>

<template>
    <div class="min-h-screen bg-gray-50 px-6 py-10">
        <div class="mx-auto max-w-5xl">
            <div class="mb-8 flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">
                        File System
                    </h1>

                    <p class="mt-1 text-sm text-gray-500">
                        Browse and manage files and folders
                    </p>
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white shadow-sm">

                <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                       <div class="relative flex-1">
                            <input
                                v-model="search"
                                @keyup.enter="exactSearch"
                                type="text"
                                placeholder="Search files..."
                                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 outline-none placeholder:text-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                            />

                            <!-- Suggestions -->
                            <div
                                v-if="suggestions.length > 0"
                                class="absolute z-50 mt-1 w-full overflow-hidden rounded-lg border border-gray-200 bg-white shadow-lg"
                            >
                                <button
                                    v-for="file in suggestions"
                                    :key="file.id"
                                    @click="selectSuggestion(file)"
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
                                v-model="searchAllFiles"
                                type="checkbox"
                                class="h-4 w-4 rounded border-gray-300"
                            />

                            Search all files
                        </label>
                    </div>
                </div>

                <div class="border-b border-gray-200 px-6 py-4">
                    
                  

                    <div class="flex items-center ">
                        <button
                            @click="goBack"
                            :disabled="path.length === 0"
                            :class="[
                                'flex h-8 w-8 items-center justify-center rounded-lg border border-gray-300',
                                path.length === 0
                                    ? 'invisible pointer-events-none'
                                    : 'bg-white text-gray-600 hover:bg-gray-50'
                            ]"
                        >
                            <ArrowLeft></ArrowLeft>
                        </button>
                        <div class="flex flex-wrap items-center gap-2 text-sm pl-2">
                            <button
                                @click="goHome"
                                class="font-medium text-blue-600 hover:text-blue-800"
                            >
                                Home
                            </button>

                            <template
                                v-for="(folder, index) in path"
                                :key="folder.id"
                            >
                                <span class="text-gray-400">
                                    /
                                </span>

                                <button
                                    @click="goToPath(index)"
                                    class="font-medium text-blue-600 hover:text-blue-800"
                                >
                                    {{ folder.name }}
                                </button>
                            </template>
                        </div>
                    </div>
                </div>

                <div class="p-6">
                    <div class="mb-4 flex items-center justify-end gap-3">
                        <button
                            @click="createFolder"
                            class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50"
                        >
                            <FolderPlus class="h-4 w-4" />
                            <span>Create folder</span>
                        </button>

                        <button
                            @click="createFile"
                            class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-gray-100 px-4 py-2.5 text-sm font-medium text-gray-900 transition hover:bg-gray-200"
                        >
                            <FilePlusCorner class="h-4 w-4" />
                            <span>Create file</span>
                        </button>
                    </div>
                    <!-- ####### FILES ####### -->
                    <section class="mb-8">
                        <div class="mb-4 flex items-center justify-between">
                            <h2 class="text-sm font-semibold tracking-wide text-gray-500">
                                FOLDERS
                            </h2>

                            <span class="text-xs text-gray-400">
                                {{ childFolders.length }}
                                folder{{ childFolders.length === 1 ? '' : 's' }}
                            </span>
                        </div>

                        <div
                            v-if="childFolders.length > 0"
                            class="space-y-2"
                        >
                            <div
                                v-for="folder in childFolders"
                                :key="folder.id"
                                class="flex items-center justify-between rounded-lg border border-gray-200 px-4 py-3 transition hover:bg-gray-50"
                            >
                                <button
                                    @click="openFolder(folder)"
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
                                    @click="deleteFolder(folder)"
                                    class="rounded-md px-3 py-2 text-sm text-gray-400 transition hover:bg-red-50 hover:text-red-600"
                                >
                                    <Trash />
                                </button>
                            </div>
                            <div
                                v-if="folderPagination && folderPagination.last_page > 1"
                                class="mt-4 flex items-center justify-center gap-3"
                            >
                                <button
                                    :disabled="folderPagination.current_page === 1"
                                    @click="changeFolderPage(folderPagination.current_page - 1)"
                                    class="cursor-pointer transition hover:scale-110 disabled:cursor-not-allowed disabled:opacity-40"
                                >
                                    <CircleChevronLeft class="h-6 w-6" />
                                </button>

                                <span class="text-sm text-gray-500">
                                    {{ folderPagination.current_page }}
                                    /
                                    {{ folderPagination.last_page }}
                                </span>

                                <button
                                    :disabled="
                                        folderPagination.current_page === folderPagination.last_page
                                    "
                                    @click="changeFolderPage(folderPagination.current_page + 1)"
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
                    <!--    #######  FILES  #######  -->
                    <section>
                        <div class="mb-4 flex items-center justify-between">
                            <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500">
                                {{ search ? 'Search results' : 'Files' }}
                            </h2>

                            <span class="text-xs text-gray-400">
                                {{
                                    search
                                        ? displayedFiles.length
                                        : (filePagination?.total ?? displayedFiles.length)
                                }}
                                file{{
                                    (search
                                        ? displayedFiles.length
                                        : (filePagination?.total ?? displayedFiles.length)
                                    ) === 1
                                        ? ''
                                        : 's'
                                }}
                            </span>
                        </div>

                        <div
                            v-if="displayedFiles.length > 0"
                            class="space-y-2"
                        >
                            <div
                                v-for="file in displayedFiles"
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

                                        <div
                                            v-else
                                            class="text-xs text-gray-400"
                                        >
                                            File
                                        </div>
                                    </div>
                                </div>

                                <button
                                    @click="deleteFile(file)"
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
                                    search
                                        ? 'No matching files found.'
                                        : 'No files in this directory.'
                                }}
                            </p>
                        </div>

                        <div
                            v-if="
                                !search &&
                                filePagination &&
                                filePagination.last_page > 1
                            "
                            class="mt-4 flex items-center justify-center gap-3"
                        >
                            <button
                                :disabled="filePagination.current_page === 1"
                                @click="changeFilePage(filePagination.current_page - 1)"
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
                                @click="changeFilePage(filePagination.current_page + 1)"
                                class="cursor-pointer transition hover:scale-110 disabled:cursor-not-allowed disabled:opacity-40"
                            >
                                <CircleChevronRight class="h-6 w-6" />
                            </button>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div>
</template>