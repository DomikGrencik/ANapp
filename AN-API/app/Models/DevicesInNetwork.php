<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DevicesInNetwork extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'device_id',
    ];
    protected $table = 'devices_in_networks';
    protected $primaryKey = 'id';

    public function getCreatedAtAttribute($value)
    {
        return date('j.n.y', strtotime($value));
    }

    public function getUpdatedAtAttribute($value)
    {
        return date('j.n.y', strtotime($value));
    }

    public function interface_of_devices()
    {
        return $this->hasMany(InterfaceOfDevice::class, 'id', 'id');
    }

    public function devices()
    {
        return $this->belongsTo(Device::class, 'device_id', 'device_id');
    }
}


