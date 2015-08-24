<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Validator;
use Illuminate\Http\Request;

class Email extends Model
{
    const SEND_TYPE_QUEUE = 'QUEUE';
    const SEND_TYPE_SYNC = 'NOW';

    //protected $casts = ['cc' => 'array', 'bcc' => 'array'];

    protected $fillable = ['to', 'send_type', 'subject', 'html', 'origin', 'from'];

    /**
     * @param Request $request
     * @return bool
     */
    public function validateRequest(Request $request)
    {
        $rules = [
            'to' => 'required|email|max:150',
            'subject' => 'required|max:255',
            'replyTo' => 'email|max:150',
            'from' => 'email|max:150',
            'html' => 'required',
        ];

        $options = $request->all();

        if (isset($options['cc']) && is_array($options['cc'])) {
            foreach ($options['cc'] as $key => $val) {
                $rules['cc.'.$key] = 'email|max:150';
            }
        }

        if (isset($options['bcc']) && is_array($options['bcc'])) {
            foreach ($options['bcc'] as $key => $val) {
                $rules['bcc.'.$key] = 'email|max:150';
            }
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return $validator->errors()->all();
        }

        return true;
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
