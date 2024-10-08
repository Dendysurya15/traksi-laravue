<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wilayah extends Model
{
    use HasFactory;

    protected $connection = 'mysql3';
    protected $table = 'wil';

    public function estate()
    {
        return $this->hasMany(Estate::class, 'wil'); // 'regional' is the foreign key in the 'estate' table
    }
}
