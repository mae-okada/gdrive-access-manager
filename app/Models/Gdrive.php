<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Gdrive
 *
 * @property int $id
 * @property int $division_id
 * @property string|null $unique_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Division $division
 */
class Gdrive extends Model
{
    protected $table = 'gdrives';

    protected $casts = [
        'division_id' => 'int',
    ];

    protected $fillable = [
        'division_id',
        'unique_id',
    ];

    public function division()
    {
        return $this->belongsTo(Division::class);
    }
}
