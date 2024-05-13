<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InterfaceOfDevice extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'connector',
        'AN',
        'speed',
        'direction',
        'id',
        'type',
    ];
    protected $table = 'interface_of_devices';
    protected $primaryKey = 'interface_id';

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
        return $this->belongsTo(DevicesInNetwork::class, 'id', 'device_id');
    }

    public function connections()
    {
        return $this->hasOne(Connection::class, 'interface_id1', 'interface_id');

        return $this->hasOne(Connection::class, 'interface_id2', 'interface_id');

        return $this->hasOne(Connection::class, 'device_id1', 'id');

        return $this->hasOne(Connection::class, 'device_id2', 'id');
    }
}
