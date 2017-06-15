<?php

namespace App\Tokens;


use App\Foundation\UuidModel;
use App\Requests\Request;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Token extends Model
{
    use UuidModel, SoftDeletes;

    public $timestamps = true;
    public $incrementing = false;
    public $primaryKey = 'uuid';
    protected $table = 'tokens';
    protected $dates = ['deleted_at', 'created_at', 'updated_at'];
    protected $fillable = ['ip', 'user_agent', 'default_content', 'default_status', 'default_content_type', 'timeout'];
    protected $hidden = ['id', 'user_agent', 'ip'];
    protected $casts = [
        'default_status' => 'integer',
        'timeout' => 'integer',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function requests()
    {
        return $this->hasMany(Request::class);
    }
}
