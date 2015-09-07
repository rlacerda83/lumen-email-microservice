<?php

namespace App\Services\Email;

use App\Jobs\JobEmail;
use App\Models\Email;
use Laravel\Lumen\Routing\DispatchesJobs;
use Mail;

class SendEmail
{
    use DispatchesJobs;
    /**
     * @param Email $email
     * @param string $template
     * @return bool
     * @throws \Exception
     */
    public static function send(Email $email, $template = 'email.blank')
    {
        try {
            Mail::send($template, ['html' => $email->html], function ($msg) use ($email) {

                if ($email->from) {
                    $msg->from([$email->from]);
                }

                $msg->to([$email->to]);
                $msg->subject($email->subject);

                if ($email->replyTo) {
                    $msg->setReplyTo($email->replyTo);
                };

                if ($email->cc) {
                    $emailsCc = json_decode($email->cc);
                    foreach ($emailsCc as $key => $val) {
                        $msg->cc($val);
                    }
                }

                if ($email->bcc) {
                    $emailsBcc = json_decode($email->bcc);
                    foreach ($emailsBcc as $key => $val) {
                        $msg->bcc($val);
                    }
                }

            });
        } catch (\Exception $e) {
            throw $e;
        }

        return true;
    }

    public function handleEmailType(Email $email, $method = 'queue')
    {
        if (strtoupper($method) == Email::SEND_TYPE_QUEUE) {
           $this->dispatch(new JobEmail($email));
        } else {
            self::send($email);
        }
    }
}
