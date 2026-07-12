<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Mews\Purifier\Casts\CleanHtmlInput;

class ReportedUser extends Model
{
    protected $guarded = [];

    protected $casts = [
        'reason' => CleanHtmlInput::class,
    ];

    /**
     * Get reporter user.
     *
     * @return object
     */
    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_id')->withTrashed();
    }

    /**
     * Get reported user.
     *
     * @return object
     */
    public function reported()
    {
        return $this->belongsTo(User::class, 'reported_id')->withTrashed();
    }
}
