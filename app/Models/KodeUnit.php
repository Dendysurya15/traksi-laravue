<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KodeUnit extends Model
{
    use HasFactory;

    protected $connection = 'mysql2';
    protected $table = 'kode_unit_2';
}
