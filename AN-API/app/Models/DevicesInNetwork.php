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
        'router_id',
        'switch_id',
        'ED_id'
    ];
    protected $table = 'devices_in_networks';
    protected $primaryKey = 'device_id';

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
        return $this->hasMany(InterfaceOfDevice::class, 'interface_id', 'device_id');
    }
}
