<?php namespace App\Http\Controllers\V1;

use Mail;
use App\Models\Email;
use App\Transformers\EmailTransformer;
use Illuminate\Http\Request;
use Dingo\Api\Routing\Helpers;
use Dingo\Api\Exception\StoreResourceFailedException;
use Dingo\Api\Exception\DeleteResourceFailedException;
use Laravel\Lumen\Routing\Controller as BaseController;

class EmailController extends BaseController
{

    use Helpers;

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index()
    {
        $emails =  Email::paginate(5);
        return $this->response->paginator($emails, new EmailTransformer);
    }

    /**
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function get($id){

        $email = Email::find($id);
        if(!$email) {
            throw new StoreResourceFailedException('Email not found');
        }

        return $this->response->item($email, new EmailTransformer);

    }

    /**
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function delete($id){

        $email = Email::find($id);
        if(!$email) {
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
        $email = new Email();

        $handleRequest = $email->validateRequest($request);

        if (is_array($handleRequest)) {
            throw new StoreResourceFailedException('Invalid request', $handleRequest);
        } else {
            try {
                $email->customFill($request->all());

                if ($email->send_type) {
                    $method = strtoupper($email->send_type) ==  Email::SEND_TYPE_SYNC ? 'send' : 'queue';
                } else {
                    $method = env('MAIL_SEND_TYPE');
                }
                $email->send_type = $method;

                Mail::$method('email.blank', ['html' => $email->html], function($msg) use ($email) {

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

                if (isset($options['save']) && $options['save'] == true) {
                    $email->save();
                } else if(env('MAIL_SAVE') == true) {
                    $email->save();
                }

                return $this->response->created();
            } catch (\Exception $e) {
                return $this->response->error($e->getMessage(), 422);
            }
        }
    }
}


