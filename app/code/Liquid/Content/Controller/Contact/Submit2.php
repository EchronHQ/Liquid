<?php

declare(strict_types=1);

namespace Liquid\Content\Controller\Contact;

use Liquid\Content\Helper\RecaptchaHelper;
use Liquid\Content\Repository\FormRepository;
use Liquid\Framework\App\Action\ActionInterface;
use Liquid\Framework\App\Action\Context;
use Liquid\Framework\App\Request\HttpRequest;
use Liquid\Framework\App\Route\Attribute\Route;
use Liquid\Framework\Controller\AbstractResult;
use Liquid\Framework\Email\SMTP\SMTPFactory;
use Liquid\Framework\ObjectManager\ObjectManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

#[Route('contact/submit', name: 'contact-submit')]
class Submit2 implements ActionInterface
{
    public function __construct(
        Context                                 $context,
        private readonly FormRepository         $formRepository,
        private readonly SMTPFactory            $smtpFactory,
        private readonly ObjectManagerInterface $objectManager,
        private readonly HttpRequest            $request,
        private readonly LoggerInterface        $logger,
    )
    {


    }

//    private function validateReCaptcha(string $token): float
//    {
//        return 0;
//    }


    public function execute(): AbstractResult
    {
        if (!$this->request->isAjax()) {
            // TODO: redirect to demo page or 404 page
            $this->logger->warning('Submit action is not Ajax');
            throw new \Exception('Action must be Ajax');
        }
        $request = $this->request;
        $name = $request->getPost('name');
        $email = $request->getPost('email');
        $company = $request->getPost('company');
        $phone = $request->getPost('phone');
        //        $country = $request->getPost('country');
        $message = $request->getPost('message');
        $time = $request->getPost('time');
        $recaptchaToken = $request->getPost('rt');

        $recaptchaScore = RecaptchaHelper::validateToken($recaptchaToken, 'contact');

        $ip = $request->getIp();
        $date = new \DateTime('now');
        $submittedData = [
            'name' => $name,
            'email' => $email,
            'company' => $company,
            'phone' => $phone,
            'user_time' => $time,
            //            'country' => $country,
            'message' => $message,

        ];
        $this->logger->info('New contact submission', ['data' => $submittedData, 'ip' => $ip, 'date' => $date->format("Y-m-d H:i:s"), 'recaptcha' => $recaptchaScore]);
        try {
            $this->formRepository->create('contact', $submittedData, $ip, $recaptchaScore, $date);
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
            'success' => true,
        ];


        $result = $this->objectManager->create(\Liquid\Framework\Controller\Result\Json::class);
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

        $email = new Email()
            ->from(new Address('no-reply@attlaz.com', 'Attlaz'))
            ->to('hello@attlaz.com')
            ->html($htmlMarkup)
            ->subject('Demo request');

        $smtp = $this->smtpFactory->create();

        $smtp->send($email);

    }

}
