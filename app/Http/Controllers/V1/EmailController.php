<?php

namespace App\Http\Controllers\V1;

use App\Models\Email;
use App\Services\Email\SendEmail;
use App\Transformers\EmailTransformer;
use Illuminate\Http\Request;
use Dingo\Api\Routing\Helpers;
use Dingo\Api\Exception\StoreResourceFailedException;
use Dingo\Api\Exception\DeleteResourceFailedException;
use Laravel\Lumen\Routing\Controller as BaseController;
use App\Repositories\Eloquent\EmailRepository;
use QueryParser\QueryParserException;

class EmailController extends BaseController
{
    use Helpers;

    /**
     * @var EmailRepository
     */
    private $repository;

    /**
     * @param EmailRepository $repository
     */
    public function __construct(EmailRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(Request $request)
    {
        try {
            $paginator = $this->repository->findAllPaginate($request);

            return $this->response->paginator($paginator, new EmailTransformer);
        } catch (QueryParserException $e) {
            throw new StoreResourceFailedException($e->getMessage(), $e->getFields());
        }
    }

    /**
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function get($id)
    {
        $email = $this->repository->find($id);
        if (! $email) {
            throw new StoreResourceFailedException('Email not found');
        }

        return $this->response->item($email, new EmailTransformer);
    }

    /**
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function delete($id)
    {
        $email = $this->repository->find($id);
        if (! $email) {
            throw new DeleteResourceFailedException('Email not found');
        }

        $email->delete();

        return $this->response->noContent();
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function send(Request $request)
    {
        $handleRequest = $this->repository->validateRequest($request);

        if (is_array($handleRequest)) {
            throw new StoreResourceFailedException('Invalid request', $handleRequest);
        } else {
            try {
                $options = $request->all();
                $email = $this->repository->customFill($options);

                if ($email->send_type) {
                    $method = strtoupper($email->send_type) ==  Email::SEND_TYPE_SYNC ? 'send' : 'queue';
                } else {
                    $method = env('MAIL_SEND_TYPE');
                }

                $email->send_type = $method;

                if (isset($options['save']) && $options['save'] === true) {
                    $email->save();
                } elseif (env('MAIL_SAVE') === true || strtoupper($method) == Email::SEND_TYPE_QUEUE) {
                    $email->save();
                }

                $serviceEmail = new SendEmail();
                $serviceEmail->handleEmailType($email, $method);

                return $this->response->created();
            } catch (\Exception $e) {
                return $this->response->error($e->getMessage(), 422);
            }
        }
    }
}
