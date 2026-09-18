<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchFilesRequest;
use App\Http\Resources\FileResource;
use App\Models\File;

class SearchController extends Controller
{
    /**
     * Return top 10 files whose name starts with the search string.
     */
    public function suggestions(SearchFilesRequest $request)
    {
        $search = $request->validated('q');
        $searchAll = $request->boolean('all');
        $folderId = $request->validated('folder_id');

        $files = File::query()
            ->where('name', 'like', $search . '%')
            ->when(
                !$searchAll,
                fn ($query) => $folderId !== null
                    ? $query->where('folder_id', $folderId)
                    : $query->whereNull('folder_id')
            )
            ->orderBy('name')
            ->limit(10)
            ->get();

        return FileResource::collection($files);
    }

    /**
     * Search for an exact file name.
     */
    public function exact(SearchFilesRequest $request)
    {
        $data = $request->validated();

        $search = $data['q'];
        $searchAll = $data['all'] ?? false;
        $folderId = $data['folder_id'] ?? null;

        $files = File::query()
            ->with('folder')
            ->where('name', $search)
            ->when(
                !$searchAll,
                fn ($query) => $folderId !== null
                    ? $query->where('folder_id', $folderId)
                    : $query->whereNull('folder_id')
            )
            ->orderBy('name')
            ->get();

        return FileResource::collection($files);
    }
}