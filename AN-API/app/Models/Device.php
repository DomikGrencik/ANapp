<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    use HasFactory;

    protected $fillable = [
        'manufacturer',
        'model',
        'type'
    ];
    protected $table = 'devices';
    protected $primaryKey = 'id';

    public function getCreatedAtAttribute($value)
    {
        return date('j.n.y', strtotime($value));
    }

    public function getUpdatedAtAttribute($value)
    {
        return date('j.n.y', strtotime($value));
    }

    public function devices_in_networks()
    {
        return $this->hasMany(DevicesInNetwork::class, 'device_id', 'id');
    }

    public function ports()
    {
        return $this->hasMany(Port::class, 'port_id', 'id');
    }
}
