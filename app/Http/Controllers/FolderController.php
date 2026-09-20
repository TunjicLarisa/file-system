<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFolderRequest;
use App\Http\Resources\FolderResource;
use App\Models\Folder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class FolderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $parentId = $request->query('parent_id');

        $folders = Folder::query()
            ->when(
                $parentId !== null,
                fn ($query) => $query->where('parent_id', $parentId),
                fn ($query) => $query->whereNull('parent_id')
            )
            ->orderBy('name')
            ->paginate(50);

        return FolderResource::collection($folders);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreFolderRequest $request): JsonResponse
    {
        $folder = Folder::create(
            $request->validated()
        );

        return (new FolderResource($folder))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Folder $folder): Response
    {
        $folder->delete();

        return response()->noContent();
    }
}
