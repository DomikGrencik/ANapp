<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Port extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'connector',
        'AN',
        'speed',
        'number_of_ports',
        'type',
        'device_id',
    ];
    protected $table = 'ports';
    protected $primaryKey = 'port_id';

    public function getCreatedAtAttribute($value)
    {
        return date('j.n.y', strtotime($value));
    }

    public function getUpdatedAtAttribute($value)
    {
        return date('j.n.y', strtotime($value));
    }

    public function devices()
    {
        return $this->belongsTo(Device::class, 'device_id', 'device_id');
    }
}
