<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\File as FileModel;
use App\Models\Folder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Root folders
        $documents = Folder::create([
            'name' => 'Documents',
            'parent_id' => null,
        ]);

        $photos = Folder::create([
            'name' => 'Photos',
            'parent_id' => null,
        ]);

        $projects = Folder::create([
            'name' => 'Projects',
            'parent_id' => null,
        ]);

        // Documents children
        $work = Folder::create([
            'name' => 'Work',
            'parent_id' => $documents->id,
        ]);

        $personal = Folder::create([
            'name' => 'Personal',
            'parent_id' => $documents->id,
        ]);

        // Projects children
        $laravel = Folder::create([
            'name' => 'Laravel',
            'parent_id' => $projects->id,
        ]);

        $challenge = Folder::create([
            'name' => 'File System Challenge',
            'parent_id' => $laravel->id,
        ]);

        // Root files
        FileModel::create([
            'name' => 'readme.txt',
            'folder_id' => null,
        ]);

        FileModel::create([
            'name' => 'notes.txt',
            'folder_id' => null,
        ]);

        // Documents
        FileModel::create([
            'name' => 'cv.pdf',
            'folder_id' => $documents->id,
        ]);

        FileModel::create([
            'name' => 'cover-letter.pdf',
            'folder_id' => $documents->id,
        ]);

        // Work
        FileModel::create([
            'name' => 'contract.pdf',
            'folder_id' => $work->id,
        ]);

        FileModel::create([
            'name' => 'report.docx',
            'folder_id' => $work->id,
        ]);

        FileModel::create([
            'name' => 'requirements.txt',
            'folder_id' => $work->id,
        ]);

        // Personal
        FileModel::create([
            'name' => 'shopping-list.txt',
            'folder_id' => $personal->id,
        ]);

        // Photos
        FileModel::create([
            'name' => 'holiday.jpg',
            'folder_id' => $photos->id,
        ]);

        FileModel::create([
            'name' => 'family.png',
            'folder_id' => $photos->id,
        ]);

        // Laravel project
        FileModel::create([
            'name' => 'architecture.md',
            'folder_id' => $laravel->id,
        ]);

        FileModel::create([
            'name' => 'database-design.md',
            'folder_id' => $challenge->id,
        ]);
    }
}
