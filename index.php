<?php

require_once __DIR__.'/vendor/autoload.php';

use Slim\Slim;
use Valitron\Validator;

$app = new Slim();

//------------------------------------------------------------------------------
// Load configuration
//------------------------------------------------------------------------------

$config = json_decode(file_get_contents(__DIR__.'/config.json'), true);

foreach ($config as $key => $value) {
  $app->config($key, $value);
}

//------------------------------------------------------------------------------
// Define routes
//------------------------------------------------------------------------------

$app->get('/', function () use ($app) {
  $request = $app->request;
  $response = $app->response;

  $message = '';
  $errors = [];
  $status = 201;

  $data = [
    'recipient' => $request->get('recipient') ?: $app->config('data.recipient'),
    'title'     => $request->get('title')     ?: $app->config('data.title'),
    'sender'    => $request->get('sender')    ?: $app->config('data.sender'),
    'name'      => $request->get('name')      ?: $app->config('data.name'),
    'message'   => $request->get('message')   ?: $app->config('data.message'),
    'cc'        => $request->get('cc')        ?: $app->config('data.cc'),
  ];

  $validator = new Validator($data);
  $validator
    ->labels([
      'recipient' => $app->config('label.recipient'),
      'title'     => $app->config('label.title'),
      'sender'    => $app->config('label.sender'),
      'name'      => $app->config('label.name'),
      'message'   => $app->config('label.message'),
    ])
    ->rule('required', ['sender', 'recipient', 'name', 'title', 'message'])
    ->message($app->config('validation.required'))
    ->rule('email', ['recipient', 'sender'])
    ->message($app->config('validation.email'));

  if ($validator->validate()) {
    $message = $app->config('message.success');

    $mailer = Swift_Mailer::newInstance(Swift_MailTransport::newInstance());
    $email = Swift_Message::newInstance()
      ->setSubject($data['title'])
      ->setFrom([$data['sender'] => $data['name']])
      ->setTo([$data['recipient']])
      ->setBody($data['message']);

    if ($data['cc']) {
      $email->setCc([$data['sender'] => $data['name']]);
    }

    $mailer->send($email);
  }
  else {
    $message = $app->config('message.error');
    $errors = array_map('reset', $validator->errors());
    $status = 400;
  }

  $response->headers->set('Content-Type', 'application/json');
  $response->setStatus($status);
  $response->setBody(json_encode([
    'message' => $message,
    'errors'  => $errors
  ]));
});

//------------------------------------------------------------------------------
// Start the engine
//------------------------------------------------------------------------------

$app->run();
