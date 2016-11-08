<?php

namespace App\Requests;


use App\Foundation\UuidModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Request extends Model
{
    use UuidModel, SoftDeletes;
    public $timestamps = true;
    public $incrementing = false;
    public $primaryKey = 'uuid';
    protected $table = 'requests';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'ip',
        'user_agent',
        'url',
        'method',
        'hostname',
        'content',
        'headers',
        'token_id',
    ];
    protected $hidden = ['id'];
    protected $casts = [
        'headers' => 'array',
    ];
}
