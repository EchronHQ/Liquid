<?php

declare(strict_types=1);

namespace Liquid\Content\Repository;

use Attlaz\Adapter\Base\RemoteService\SqlRemoteService;

class FormRepository
{
    private SqlRemoteService $remoteService;


    public function __construct(SqlRemoteService $remoteService)
    {
        $this->remoteService = $remoteService;
    }

    public function create(string $type, array $data, string $ip, float $recaptchaScore, \DateTime $date): void
    {
        $entries = [
            [
                'type'            => $type,
                'data'            => \json_encode($data),
                'ip'              => $ip,
                'recaptcha_score' => $recaptchaScore,
                'date'            => $date->format('Y-m-d H:i:s')
            ]
        ];
        $this->remoteService->insert('form_submitted', $entries);
    }
}
