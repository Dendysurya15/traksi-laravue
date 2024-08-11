<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JenisUnit extends Model
{
    use HasFactory;

    protected $connection = 'mysql2';
    protected $table = 'jenis_unit_2';
}
