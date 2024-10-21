<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rol extends Model
{
    protected $table = "rol";
    protected $primaryKey = "id_rol";
    public $timestamps = false;

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }
}
