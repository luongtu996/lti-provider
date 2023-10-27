<?php

namespace App\Models;

use App\Models\Lti13Key;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lti13Registration extends Model
{
    protected  $table = 'lti_registrations';
    protected $fillable = [
        'issuer',
        'client_id',
        'platform_login_auth_endpoint',
        'platform_auth_token_endpoint',
        'platform_key_set_endpoint',
        'lti_key_id'
    ];

    public function ltiDeployments(): HasMany
    {
        return $this->hasMany(Lti13Deployment::class, 'lti_registration_id', 'id');
    }

    public function ltiKey(): BelongsTo
    {
        return $this->belongsTo(Lti13Key::class, 'lti_key_id', 'id');
    }
}
