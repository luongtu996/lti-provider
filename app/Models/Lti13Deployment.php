<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lti13Deployment extends Model
{
    protected  $table = 'lti_deployments';

    protected $fillable = [
        'deployment_id',
        'lti13_registration_id'
    ];

    public function ltiRegistration(): BelongsTo
    {
        return $this->belongsTo(Lti13Registration::class, 'lti_registration_id', 'id');
    }
}
