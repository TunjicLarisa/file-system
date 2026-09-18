<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class File extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        'name',
        'folder_id',
    ];

    public function folder()
    {
        return $this->belongsTo(Folder::class);
    }
}
