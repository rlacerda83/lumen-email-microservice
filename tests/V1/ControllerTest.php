<?php

use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Models\Email;

class ControllerTest extends TestCase
{
    private $key;

    private $serverParams;

    private $mock;

    use DatabaseTransactions;

    public function setUp()
    {
        parent::setUp();
        $this->key = 'key:'.env('APP_KEY');
        $this->serverParams = [
            'HTTP_ACCEPT'      => 'application/vnd.app.v1+json',
            'HTTP_API_TOKEN' => $this->key,
        ];
    }

    public function tearDown()
    {
        parent::tearDown();

        Mockery::close();
    }

    public function testApitWithoutCredentials()
    {
        $this->call('GET', '/api/emails');
        $this->assertResponseStatus(401);
    }

    public function testGetEmails()
    {
        $response = $this->call('GET', '/api/emails', [], [], [], $this->serverParams);
        $json = json_decode($response->getContent());

        $this->assertResponseOk();

        /*
         * check and test json response
         */
        $this->assertTrue(isset($json->meta));
        $this->assertTrue(isset($json->data));
    }

    public function testGetEmail()
    {
        $email = $this->generateEmail();
        $response = $this->call('GET', "/api/emails/{$email->id}", [], [], [], $this->serverParams);
        $json = json_decode($response->getContent());

        $this->assertResponseOk();

        /*
         * check and test json response
         */
        $this->assertTrue(isset($json->data));
        $this->assertTrue(isset($json->data->id));
        $this->assertEquals($json->data->id, $email->id);
    }

    public function testGetEmailNotFound()
    {
        $this->call('GET', '/api/emails/0', [], [], [], $this->serverParams);
        $this->assertResponseStatus(422);
    }

    public function testDeleteEmail()
    {
        $email = $this->generateEmail();
        $this->call('DELETE', "/api/emails/{$email->id}", [], [], [], $this->serverParams);
        $this->assertResponseStatus(204);

        $this->notSeeInDatabase($email->getTable(), ['id' => $email->id]);
    }

    public function testDeleteEmailNotFound()
    {
        $this->call('DELETE', '/api/emails/0', [], [], [], $this->serverParams);
        $this->assertResponseStatus(422);
    }

    public function testSendEmailWithBadParams()
    {
        $this->call('POST', '/api/emails/send', [], [], [], $this->serverParams);
        $this->assertResponseStatus(422);
    }

    /**
     * @param $queryParams
     * @param $expectedResult
     *
     * @dataProvider providerTestSendEmail
     */
    public function testSendEmail($queryParams, $expectedResult)
    {
        if ($queryParams['save'] == true && $expectedResult == 201) {
            $totalBefore = Email::count();
        }

        Mail::pretend(true);

        $this->call('POST', '/api/emails/send', $queryParams, [], [], $this->serverParams);
        $this->assertResponseStatus($expectedResult);

        if ($queryParams['save'] == true && $expectedResult == 201) {
            $totalAfter = Email::count();
            $this->assertEquals($totalBefore, $totalAfter-1);
        }
    }

    public function providerTestSendEmail()
    {
        return [
            [[
                'to' => 'r.lacerda83@gmail.com',
                'subject' => 'Test',
                'html' => '<html><b>OK</b></html>',
                'save' => false
            ], 201],
            [[
                'to' => 'r.lacerda83@gmail.com',
                'subject' => 'Test',
                'html' => '<html><b>OK</b></html>',
                'save' => true
            ], 201],
            [[
                'to' => 'r.lacerda83@gmail.com',
                'subject' => 'Test',
                'save' => false
            ], 422],
        ];
    }

    protected function generateEmail()
    {
        $data = [
            'to' => 'r.lacerda83@gmail.com',
            'send_type' => 'queue',
            'subject' => 'Test',
            'html' => '<html></html>',
        ];

        return Email::create($data);
    }


}
