<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFileRequest;
use App\Http\Resources\FileResource;
use App\Models\File;
use Illuminate\Http\Request;

class FileController extends Controller
{
    public function index(Request $request)
    {
        $folderId = $request->query('folder_id');

        $files = File::query()
            ->when(
                $folderId !== null,
                fn ($query) => $query->where('folder_id', $folderId),
                fn ($query) => $query->whereNull('folder_id')
            )
            ->orderBy('name')
            ->paginate(50);

        return FileResource::collection($files);
    }

    public function store(StoreFileRequest $request)
    {
        $file = File::create(
            $request->validated()
        );

        return (new FileResource($file))
            ->response()
            ->setStatusCode(201);
    }

    public function destroy(File $file)
    {
        $file->delete();

        return response()->noContent();
    }
}
