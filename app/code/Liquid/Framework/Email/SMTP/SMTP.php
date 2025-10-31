<?php
declare(strict_types=1);

namespace Liquid\Framework\Email\SMTP;

use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\Smtp\Auth\PlainAuthenticator;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Symfony\Component\Mime\RawMessage;

class SMTP
{
    private EsmtpTransport $transport;

    public function __construct(
        private readonly string $host,
        private readonly int    $port,
        private readonly bool   $tls,
        private readonly string $username,
        private readonly string $password,

    )
    {
        $this->transport = new EsmtpTransport(
            host: $this->host,
            port: $this->port,
            tls: $this->tls,

            authenticators: [new PlainAuthenticator()]
        );

        $this->transport->setUsername($this->username);
        $this->transport->setPassword($this->password);
    }

    public function send(RawMessage $email): SentMessage|null
    {
        // TODO: add error handling
        return $this->transport->send($email);
    }
}
