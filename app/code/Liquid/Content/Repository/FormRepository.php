<?php

declare(strict_types=1);

namespace Liquid\Content\Repository;

use Liquid\Core\Repository\BaseRepository;

class FormRepository extends BaseRepository
{


    public function create(string $type, array $data, string $ip, float $recaptchaScore, \DateTime $date): void
    {
        $entries = [
            [
                'type' => $type,
                'data' => \json_encode($data),
                'ip' => $ip,
                'recaptcha_score' => $recaptchaScore,
                'date' => $date->format('Y-m-d H:i:s'),
            ],
        ];
        $this->getRemoteService()->insert('form_submitted', $entries);
    }
}
