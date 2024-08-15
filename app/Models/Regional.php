<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Regional extends Model
{
    use HasFactory;


    protected $connection = 'mysql3';
    protected $table = 'reg';


    public function wilayah()
    {
        return $this->hasMany(Wilayah::class, 'regional'); // 'regional' is the foreign key in the 'estate' table
    }
}
