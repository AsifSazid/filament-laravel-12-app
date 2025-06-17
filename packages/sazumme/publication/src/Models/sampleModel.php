<?php

namespace Sazumme\Publication\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Traits\Historiable; // Not Neccessary

class SampleModel extends Model
{
    use SoftDeletes;

    // use Historiable; // Not Neccessary
    
    protected $connection = 'conntection_name';
    protected $table = 'table_name';
    protected $guarded = [];
}
