<?php

namespace App\Repositories\Eloquent;


use Elocache\Repositories\Eloquent\AbstractRepository;
use Illuminate\Http\Request;
use QueryParser\ParserRequest;
use Validator;

class EmailRepository extends AbstractRepository {

    protected $enableCaching = true;

    public static $rules = [
        'to' => 'required|email|max:150',
        'subject' => 'required|max:255',
        'reply_to' => 'email|max:150',
        'from' => 'email|max:150',
        'html' => 'required',
    ];

    /**
     * Specify Model class name
     *
     * @return mixed
     */
    public function model()
    {
        return 'App\Models\Email';
    }

    /**
     * @param Request $request
     * @return bool
     */
    public function validateRequest(Request $request)
    {
        $rules = self::$rules;

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
     * @return mixed
     * @throws \Exception
     */
    public function customFill(array $attributes)
    {
        try {
            $this->getModel()->fill($attributes);

            if (isset($attributes['bcc']) && is_array($attributes['bcc'])) {
                $this->bcc = json_encode($attributes['bcc']);
            }

            if (isset($attributes['cc']) && is_array($attributes['cc'])) {
                $this->cc = json_encode($attributes['cc']);
            }
        } catch (\Exception $e) {
            throw $e;
        }

        return $this->getModel();
    }

    public function findAllPaginate(Request $request, $itemsPage = 30)
    {
        $key = md5($itemsPage . $request->getRequestUri());
        $queryParser = new ParserRequest($request, $this->getModel());
        $queryBuilder = $queryParser->parser();

        return $this->cacheQueryBuilder($key, $queryBuilder, 'paginate', $itemsPage);
    }


}