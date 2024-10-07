<?php

namespace App\Models\Cursos;

use App\Models\Docente\Docente;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Curso extends Model
{
    use HasFactory;
    protected $table = "curso";

    protected $fillable = [
        'descripcion',
    ];

    // Si necesitas definir una relación inversa
    public function cursos()
    {
        return $this->hasOne(Curso::class, 'id', 'id_curso');
    }
}
