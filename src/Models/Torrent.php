<?php

namespace MetaverseSystems\TorrentPHPBackend\Models;

use Illuminate\Database\Eloquent\Model;

class Torrent extends Model
{
    public $incrementing = false;
    protected $keyType = "string";
}
