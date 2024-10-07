<?php

namespace App\Models\Docente;

use App\Models\Cursos\Curso;
use App\Models\Persona\Persona;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Docente extends Model
{
    use HasFactory;

    protected $table = 'docente';

    protected $fillable = [
        'id_persona',
        'id_curso'
    ];

    public function persona()
    {
        return $this->belongsTo(Persona::class, 'id_persona');
    }

    public function cursos()
    {
        return $this->hasMany(Curso::class, 'id', 'id_curso');
    }
}
