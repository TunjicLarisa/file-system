<script>
import { FolderPlus, FilePlusCorner } from 'lucide-vue-next';

import FileSearch from '../components/FileSearch.vue';
import BreadcrumbNavigation from '../components/BreadcrumbNavigation.vue';
import FolderList from '../components/FolderList.vue';
import FileList from '../components/FileList.vue';

export default {
    components: {
        FolderPlus,
        FilePlusCorner,
        FileSearch,
        BreadcrumbNavigation,
        FolderList,
        FileList,
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
            searchPage: 1,

            folderPagination: null,
            filePagination: null,
            searchPagination: null,
        };
    },

    watch: {
        search() {
            clearTimeout(this.searchTimer);

            this.searchPage = 1;
            this.searchResults = [];
            this.searchPagination = null;

            if (!this.search.trim()) {
                this.suggestions = [];
                return;
            }

            this.searchTimer = setTimeout(() => {
                this.loadSuggestions();
            }, 300);
        },

        searchAllFiles() {
            this.searchPage = 1;
            this.searchResults = [];
            this.searchPagination = null;

            if (this.search.trim()) {
                this.loadSuggestions();
            }
        },
    },

    computed: {
        displayedFiles() {
            if (this.search.trim()) {
                return this.searchResults;
            }

            return this.files;
        },
    },

    methods: {
        async loadFolder() {
            const folderParams = new URLSearchParams({
                page: this.folderPage,
            });

            const fileParams = new URLSearchParams({
                page: this.filePage,
            });

            if (this.currentFolderId !== null) {
                folderParams.set('parent_id', this.currentFolderId);

                fileParams.set('folder_id', this.currentFolderId);
            }

            try {
                const [foldersResponse, filesResponse] = await Promise.all([
                    fetch(`/api/folders?${folderParams.toString()}`),

                    fetch(`/api/files?${fileParams.toString()}`),
                ]);

                if (!foldersResponse.ok || !filesResponse.ok) {
                    console.error('Could not load folder contents');
                    return;
                }

                const foldersData = await foldersResponse.json();

                const filesData = await filesResponse.json();

                this.folders = foldersData.data ?? [];

                this.files = filesData.data ?? [];

                this.folderPagination = foldersData.meta ?? null;

                this.filePagination = filesData.meta ?? null;
            } catch (error) {
                console.error(error);
            }
        },

        async createFolder() {
            const name = prompt('Folder name:');

            if (name === null) {
                return;
            }

            try {
                const response = await fetch('/api/folders', {
                    method: 'POST',

                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                    },

                    body: JSON.stringify({
                        name: name.trim(),
                        parent_id: this.currentFolderId,
                    }),
                });

                const data = await response.json();

                if (!response.ok) {
                    this.showApiError(response, data);
                    return;
                }

                await this.loadFolder();
            } catch (error) {
                this.showUnexpectedError(error);
            }
        },

        async createFile() {
            const name = prompt('File name:');

            if (name === null) {
                return;
            }

            try {
                const response = await fetch('/api/files', {
                    method: 'POST',

                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                    },

                    body: JSON.stringify({
                        name: name.trim(),
                        folder_id: this.currentFolderId,
                    }),
                });

                const data = await response.json();

                if (!response.ok) {
                    this.showApiError(response, data);
                    return;
                }

                await this.loadFolder();
            } catch (error) {
                this.showUnexpectedError(error);
            }
        },

        async deleteFolder(folder) {
            const confirmed = confirm(`Delete folder "${folder.name}"?`);

            if (!confirmed) {
                return;
            }

            try {
                const response = await fetch(`/api/folders/${folder.id}`, {
                    method: 'DELETE',

                    headers: {
                        Accept: 'application/json',
                    },
                });

                if (!response.ok) {
                    await this.showDeleteError(
                        response,
                        'Could not delete folder.',
                    );

                    return;
                }

                await this.loadFolder();
            } catch (error) {
                this.showUnexpectedError(error);
            }
        },

        async deleteFile(file) {
            const confirmed = confirm(`Delete file "${file.name}"?`);

            if (!confirmed) {
                return;
            }

            try {
                const response = await fetch(`/api/files/${file.id}`, {
                    method: 'DELETE',

                    headers: {
                        Accept: 'application/json',
                    },
                });

                if (!response.ok) {
                    await this.showDeleteError(
                        response,
                        'Could not delete file.',
                    );

                    return;
                }

                if (this.search.trim()) {
                    await this.exactSearch();
                } else {
                    await this.loadFolder();
                }
            } catch (error) {
                this.showUnexpectedError(error);
            }
        },

        async loadSuggestions() {
            const query = this.search.trim();

            if (!query) {
                this.suggestions = [];
                return;
            }

            const params = this.createSearchParams(query);

            try {
                const response = await fetch(
                    `/api/search/suggestions?${params.toString()}`,
                    {
                        headers: {
                            Accept: 'application/json',
                        },
                    },
                );

                const data = await response.json();

                if (!response.ok) {
                    this.suggestions = [];
                    return;
                }

                this.suggestions = data.data ?? data;
            } catch (error) {
                console.error(error);
                this.suggestions = [];
            }
        },

        async exactSearch() {
            clearTimeout(this.searchTimer);

            const query = this.search.trim();

            if (!query) {
                this.searchResults = [];
                this.suggestions = [];
                this.searchPagination = null;
                return;
            }

            const params = this.createSearchParams(query);

            params.set('page', this.searchPage);

            try {
                const response = await fetch(
                    `/api/search/exact?${params.toString()}`,
                    {
                        headers: {
                            Accept: 'application/json',
                        },
                    },
                );

                const data = await response.json();

                if (!response.ok) {
                    this.searchResults = [];
                    this.searchPagination = null;
                    return;
                }

                this.searchResults = data.data ?? [];

                this.searchPagination = data.meta ?? null;

                this.suggestions = [];
            } catch (error) {
                console.error(error);
                this.searchResults = [];
            }
        },

        createSearchParams(query) {
            const params = new URLSearchParams({
                q: query,

                all: this.searchAllFiles ? '1' : '0',
            });

            if (!this.searchAllFiles && this.currentFolderId !== null) {
                params.set('folder_id', this.currentFolderId);
            }

            return params;
        },

        async selectSuggestion(file) {
            this.search = file.name;
            this.suggestions = [];

            await this.exactSearch();
        },

        async openFolder(folder) {
            this.path.push({
                id: folder.id,
                name: folder.name,
            });

            this.currentFolderId = folder.id;

            this.resetDirectoryState();

            await this.loadFolder();
        },

        async goHome() {
            this.path = [];
            this.currentFolderId = null;

            this.resetDirectoryState();

            await this.loadFolder();
        },

        async goBack() {
            if (this.path.length === 0) {
                return;
            }

            this.path.pop();

            this.currentFolderId =
                this.path.length === 0
                    ? null
                    : this.path[this.path.length - 1].id;

            this.resetDirectoryState();

            await this.loadFolder();
        },

        async goToPath(index) {
            this.path = this.path.slice(0, index + 1);

            this.currentFolderId = this.path[this.path.length - 1].id;

            this.resetDirectoryState();

            await this.loadFolder();
        },

        resetDirectoryState() {
            this.search = '';
            this.suggestions = [];
            this.searchResults = [];
            this.searchPagination = null;

            this.folderPage = 1;
            this.filePage = 1;
            this.searchPage = 1;
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

        async changeSearchPage(page) {
            if (
                !this.searchPagination ||
                page < 1 ||
                page > this.searchPagination.last_page
            ) {
                return;
            }

            this.searchPage = page;
            await this.exactSearch();
        },

        showApiError(response, data) {
            if (response.status === 422) {
                const firstError = Object.values(data.errors ?? {})[0]?.[0];

                alert(firstError ?? data.message ?? 'Validation failed.');

                return;
            }

            alert(data.message ?? `Error ${response.status}`);
        },

        async showDeleteError(response, fallbackMessage) {
            let message = fallbackMessage;

            try {
                const data = await response.json();

                message = data.message ?? message;
            } catch {
                // Response has no JSON body.
            }

            alert(message);
        },

        showUnexpectedError(error) {
            console.error(error);

            alert(error.message ?? 'Unexpected error occurred.');
        },
    },

    mounted() {
        this.loadFolder();
    },
};
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
                <FileSearch
                    v-model="search"
                    v-model:search-all-files="searchAllFiles"
                    :suggestions="suggestions"
                    @search="exactSearch"
                    @select-suggestion="selectSuggestion"
                />

                <BreadcrumbNavigation
                    :path="path"
                    @back="goBack"
                    @home="goHome"
                    @navigate="goToPath"
                />

                <div class="p-6">
                    <div class="mb-4 flex items-center justify-end gap-3">
                        <button
                            @click="createFolder"
                            class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50"
                        >
                            <FolderPlus class="h-4 w-4" />

                            <span> Create folder </span>
                        </button>

                        <button
                            @click="createFile"
                            class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-gray-100 px-4 py-2.5 text-sm font-medium text-gray-900 transition hover:bg-gray-200"
                        >
                            <FilePlusCorner class="h-4 w-4" />

                            <span> Create file </span>
                        </button>
                    </div>

                    <FolderList
                        :folders="folders"
                        :pagination="folderPagination"
                        @open="openFolder"
                        @delete="deleteFolder"
                        @change-page="changeFolderPage"
                    />

                    <FileList
                        :files="displayedFiles"
                        :search-active="Boolean(search.trim())"
                        :search-all-files="searchAllFiles"
                        :file-pagination="filePagination"
                        :search-pagination="searchPagination"
                        @delete="deleteFile"
                        @change-file-page="changeFilePage"
                        @change-search-page="changeSearchPage"
                    />
                </div>
            </div>
        </div>
    </div>
</template>
