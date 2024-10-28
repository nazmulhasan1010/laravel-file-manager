<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @method static create()
 */
class Trash extends Model
{
    protected $fillable = ['file_name', 'path', 'type', 'original_path'];
}
