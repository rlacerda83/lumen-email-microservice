<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Elocache\Observers\BaseObserver;

class Email extends Model
{
    const SEND_TYPE_QUEUE = 'QUEUE';
    const SEND_TYPE_SYNC = 'NOW';

    protected $fillable = ['to', 'send_type', 'subject', 'html', 'origin', 'from', 'reply_to'];

    public static function boot()
    {
        parent::boot();

        Self::observe(new BaseObserver);
    }

    /**
     * @param array $attributes
     * @return Email
     * @throws \Exception
     */
    public function customFill(array $attributes)
    {
        try {
            $this->fill($attributes);

            if (isset($attributes['bcc']) && is_array($attributes['bcc'])) {
                $this->bcc = json_encode($attributes['bcc']);
            }

            if (isset($attributes['cc']) && is_array($attributes['cc'])) {
                $this->cc = json_encode($attributes['cc']);
            }
        } catch (\Exception $e) {
            throw $e;
        }

        return $this;
    }
}
