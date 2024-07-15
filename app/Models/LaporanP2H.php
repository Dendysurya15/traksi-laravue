<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LaporanP2H extends Model
{
    use HasFactory;

    protected $connection = 'mysql2';
    protected $table = "laporan_p2h";
}
