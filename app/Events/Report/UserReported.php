<?php

namespace App\Events\Report;

use Illuminate\Queue\SerializesModels;

class UserReported
{
    use SerializesModels;

    public function __construct(public $reporterId, public $reportedId, public string $reason)
    {
    }
}
