<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Folder extends Model
{
    protected $fillable = [
        'name',
        'parent_id',
    ];

    public function parent()
    {
        return $this->belongsTo(Folder::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Folder::class, 'parent_id');
    }

    public function files()
    {
        return $this->hasMany(File::class);
    }

    public function getPath(): string
    {
        $parts = [];
        $folder = $this;

        while ($folder) {
            array_unshift($parts, $folder->name);
            $folder = $folder->parent;
        }

        return 'Home / '.implode(' / ', $parts);
    }
}
