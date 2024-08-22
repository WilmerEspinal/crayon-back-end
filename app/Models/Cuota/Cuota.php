<?php

namespace App\Models\Cuota;

use App\Models\Matricula\matricula;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cuota extends Model
{
    use HasFactory;
    protected $table = 'cuota';

    protected $fillable = [
        'codigo_matricula',
        'id_matricula',
        'cuota_1',
        'cuota_2',
        'cuota_3',
        'cuota_4',
        'cuota_5',
        'cuota_6',
        'cuota_7',
        'cuota_8',
        'cuota_9',
        'cuota_10',
        'costo_matricula',
    ];

    public function matricula()
    {
        return $this->belongsTo(matricula::class, 'id_matricula');
    }
}
