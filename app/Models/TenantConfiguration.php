<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class TenantConfiguration extends Model implements HasMedia
{

    use InteractsWithMedia;
    protected $table = 'tenant_configurations';

    protected $fillable = ['tenant_id', 'data'];

    protected $casts = [
        'data' => 'array',
    ];

    public $timestamps = true;
}
