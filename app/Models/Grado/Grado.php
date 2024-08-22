<?php

namespace App\Models\Grado;

use App\Models\Matricula\Matricula;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Grado extends Model
{
    use HasFactory;
    protected $table = "grado";

    protected $fillable = [
        'descripcion',
    ];
    // Relación con el modelo Matricula
    public function matriculas()
    {
        return $this->hasMany(Matricula::class, 'id_grado');
    }
}
