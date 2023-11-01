<?php

declare(strict_types=1);

namespace Liquid\Content\Controller\Demo;

use Liquid\Content\Helper\RecaptchaHelper;
use Liquid\Content\Repository\FormRepository;
use Liquid\Core\Model\Action\AbstractAction;
use Liquid\Core\Model\Action\Context;
use Liquid\Core\Model\Result\Json;
use Liquid\Core\Model\Result\Result;
use Laminas\Mail\Message;
use Laminas\Mail\Transport\Smtp;
use Laminas\Mail\Transport\SmtpOptions;
use Laminas\Mime\Mime;
use Laminas\Mime\Part;

class Submit extends AbstractAction
{
    private FormRepository $formRepository;


    public function __construct(Context $context, FormRepository $formRepository)
    {
        parent::__construct($context);
        $this->formRepository = $formRepository;

    }

//    private function validateReCaptcha(string $token): float
//    {
//        return 0;
//    }


    public function execute(): ?Result
    {
        $request = $this->getRequest();
        if (!$request->isAjax()) {

            // TODO: redirect to demo page or 404 page
            return null;
        }

        $name = $request->getPost('name');
        $email = $request->getPost('email');
        $company = $request->getPost('company');
        $phone = $request->getPost('phone');
        //        $country = $request->getPost('country');
        //        $message = $request->getPost('message');
        $time = $request->getPost('time');
        $recaptchaToken = $request->getPost('rt');

        $recaptchaScore = RecaptchaHelper::validateToken($recaptchaToken, 'demo_request');

        $ip = $request->getIp();
        $date = new \DateTime('now');
        $submittedData = [
            'name'      => $name,
            'email'     => $email,
            'company'   => $company,
            'phone'     => $phone,
            'user_time' => $time
            //            'country' => $country,
            //            'message' => $message,

        ];
        $this->logger->info('New demo request', ['data' => $submittedData, 'ip' => $ip, 'date' => $date->format("Y-m-d H:i:s"), 'recaptcha' => $recaptchaScore]);
        try {
            $this->formRepository->create('demo', $submittedData, $ip, $recaptchaScore, $date);
        } catch (\Throwable $ex) {
            $this->logger->error('Unable to save form data', ['ex' => $ex]);
        }
        try {
            $emailData = $submittedData;

            $emailData['ip'] = $ip;
            $emailData['date'] = $date->format("Y-m-d H:i:s");
            $emailData['recaptcha'] = $recaptchaScore;

            $this->sendEmail($emailData);
        } catch (\Throwable $ex) {
            $this->logger->error('Unable to save form email', ['ex' => $ex]);
        }
        $data = [
            'success' => true
        ];

        $result = new Json();
        $result->setData($data);
        return $result;

    }

    private function sendEmail(array $data): void
    {
        $body = '<table>';
        foreach ($data as $key => $value) {
            $body .= '<tr><td>' . $key . '</td><td>' . $value . '</td></tr>';
        }
        $body .= '</table>';


        $htmlMarkup = '<!doctype html><html lang="en"><body>' . $body . '</body></html>';

        $html = new Part($htmlMarkup);
        $html->type = Mime::TYPE_HTML;
        $html->charset = 'utf-8';
        $html->encoding = Mime::ENCODING_QUOTEDPRINTABLE;

        $body = new \Laminas\Mime\Message();
        $body->addPart($html);


        $message = new Message();
        $message->addFrom('no-reply@attlaz.com', 'Attlaz');
        $message->addTo('hello@attlaz.com');
        $message->setSubject('Demo request');
        $message->setBody($body);

        $contentTypeHeader = $message->getHeaders()->get('Content-Type');
        //        $contentTypeHeader->setType('multipart/related');

        $transport = new Smtp();


        $options = new SmtpOptions([
            'name'              => 'AWS',
            'host'              => 'email-smtp.eu-west-1.amazonaws.com',
            'port'              => 587,
            'connection_class'  => 'plain',
            'connection_config' => [
                'username' => 'AKIAYCYQTLBXL6KZU3XW',
                'password' => 'BDuCLGrQgDWBRWqx++HuHRuqleyV3p+mF+YplXam6O6q',
                'ssl'      => 'tls',
            ],
        ]);
        $transport->setOptions($options);

        $transport->send($message);
    }

}
