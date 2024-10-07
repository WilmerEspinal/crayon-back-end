<?php

namespace App\Models\PadreFamilia;

use App\Models\Persona\Persona;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PadreFamilia extends Model
{
    use HasFactory;
    protected $table = "padre_familia";

    protected $fillable = [
        'id_persona',
        'id_alumno',
        'relacion',
        'created_at',
        'updated_at',
    ];

    public function persona()
    {
        return $this->belongsTo(Persona::class, 'id_persona');
    }
}
