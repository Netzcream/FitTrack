<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Permission extends Model
{
    use HasFactory;



        /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'permissions';

}
