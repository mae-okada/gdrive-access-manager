<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Division
 *
 * @property int $id
 * @property string $name
 * @property string|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Division extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'divisions';

    protected $fillable = [
        'id',
        'name',
    ];

    public function members()
    {
        return $this->hasMany(Member::class);
    }
}
