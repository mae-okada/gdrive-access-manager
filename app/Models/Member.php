<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Member
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $division
 * @property string|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Member extends Model
{
    use SoftDeletes;

    protected $table = 'members';

    protected $fillable = [
        'email',
        'name',
        'division',
    ];
}
