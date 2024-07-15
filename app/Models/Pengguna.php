<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Model;

class Pengguna extends Authenticatable
{
    use HasFactory;

    protected $table = 'pengguna';
    protected $primaryKey = 'user_id';
}
