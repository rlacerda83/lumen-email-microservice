<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;

class EmailTransformer extends TransformerAbstract
{

    /**
     * @param \App\Models\Email $email
     * @return array
     */
    public function transform(\App\Models\Email $email)
    {
       return $email->toArray();
    }

}