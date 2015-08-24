<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;

class EmailTransformer extends TransformerAbstract
{
    /**
     * @param $email
     * @return array
     */
    public function transform($email)
    {
        if ($email instanceof \stdClass) {
            return json_decode(json_encode($email), true);
        }

        return $email->toArray();
    }
}
