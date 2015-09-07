<?php

namespace App\Jobs;

use App\Models\Email;
use App\Services\Email\SendEmail;

class JobEmail extends Job
{
    protected $email;

    /**
     * @param Email $email
     */
    public function __construct(Email $email)
    {
        $this->email = $email;
    }

    /**
     * @throws \Exception
     */
    public function handle()
    {
        SendEmail::send($this->email);
    }
}
