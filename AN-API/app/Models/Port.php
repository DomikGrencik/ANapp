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
        'uplink_downlink',
        'number_of_ports',
        'device_id'
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
}
