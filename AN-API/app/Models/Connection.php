<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Connection extends Model
{
    use HasFactory;

    protected $fillable = [
        'interface_id1',
        'interface_id2',
        'device_id1',
        'device_id2',
        'name1',
        'name2',
    ];
    protected $table = 'connections';
    protected $primaryKey = 'connection_id';

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
        return $this->belongsTo(InterfaceOfDevice::class, 'interface_id1', 'interface_id');

        return $this->belongsTo(InterfaceOfDevice::class, 'interface_id2', 'interface_id');

        return $this->belongsTo(InterfaceOfDevice::class, 'device_id1', 'id');

        return $this->belongsTo(InterfaceOfDevice::class, 'device_id2', 'id');
    }
}
