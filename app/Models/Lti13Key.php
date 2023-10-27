<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Lti13Key extends Model
{
    protected  $table = 'lti_keys';
    protected $fillable = ['kid', 'private_key', 'public_key'];

	public static function getKeySets() {
		$keys = Lti13Key::all();
		$results = [];
		foreach ($keys as $key) {
			$results[$key->kid] = $key->private_key;
		}
		return $results;
	}

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::creating(function (Lti13Key $model) {
            if (empty($model->kid)) {
                $model->kid = Str::uuid();
            }
        });
    }

    public function ltiRegistrations(): HasMany
    {
        return $this->hasMany(Lti13Registration::class, 'lti_registration_id', 'id');
    }
}
