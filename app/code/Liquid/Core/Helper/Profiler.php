<?php

declare(strict_types=1);

namespace Liquid\Core\Helper;

use Echron\Tools\Bytes;
use Echron\Tools\Time;
use Psr\Log\LoggerInterface;

class Profiler
{
    private array $profiles = [];

    private array $profilesKeys = [];

    private array|null $currentProfile = null;

    public function profilerStart(string $key): void
    {
        $time = \microtime(true);
        $mem = memory_get_usage();

        // Check if previous profile has ended
        $last = end($this->profiles);

        $parentKey = null;
        $level = 0;
        if ($this->currentProfile !== null) {
            $parentKey = $this->currentProfile['key'];
            $parent = $this->getProfileByKey($parentKey);
            $level = $parent['level'] + 1;
        }
        // $key = $last['key'];


        $profile = [
            'key'         => $key,
            'time_start'  => $time,
            'time_end'    => null,
            'time_elapse' => null,
            'mem_start'   => $mem,
            'mem_end'     => null,
            'mem_delta'   => null,
            'level'       => $level,
            'parent'      => $parentKey
        ];
        $this->profiles[] = $profile;
        $this->profilesKeys[$key] = count($this->profiles) - 1;
        $this->currentProfile = $profile;
    }

    public function profilerFinish(string|null $key = null): void
    {
        $end = \microtime(true);
        $mem = memory_get_usage();
        if ($key === null) {
            $last = end($this->profiles);
            $key = $last['key'];
        }
        if (!isset($this->profilesKeys[$key])) {
            throw new \RuntimeException('Profile `' . $key . '` must be started before you can finish it');
        }
        $profileIndex = $this->profilesKeys[$key];
        $profile = &$this->profiles[$profileIndex];

        $profile['time_end'] = $end;
        $profile['time_elapse'] = $profile['time_end'] - $profile['time_start'];

        $profile['mem_end'] = $mem;
        $profile['mem_delta'] = $profile['mem_end'] - $profile['mem_start'];

        if ($profile['parent'] === null) {
            $this->currentProfile = null;
        } else {
            $this->currentProfile = $this->getProfileByKey($profile['parent']);
        }


    }

    private function getProfileByKey(string $key): array|null
    {
        if (!isset($this->profilesKeys[$key])) {
            return null;
        }
        $profileIndex = $this->profilesKeys[$key];
        return $this->profiles[$profileIndex];
    }

    public function getProfiles(): array
    {
        return $this->profiles;
    }

    private function orderByEnd(array $a, array $b): int
    {
        return $a["time_end"] <=> $b["time_end"];
    }

    private function getProfilesByParent(string|null $parentKey): array
    {
        $result = [];
        foreach ($this->profiles as $profile) {
            if ($profile['parent'] === $parentKey) {
                $result[] = $profile;
            }
        }
        return $result;
    }

    public function output(LoggerInterface $logger, string|null $parentKey = null): void
    {
        // Filter by finished last
        $profiles = $this->getProfilesByParent($parentKey);

        usort($profiles, array($this, 'orderByEnd'));


        foreach ($profiles as $profile) {
            $this->output($logger, $profile['key']);

            if ($profile['time_elapse'] === null) {
                $logger->warning('[Profile] `' . $profile['key'] . '` not finished');
            } else {
                $logger->debug('[Profile] ' . str_repeat('  ', $profile['level']) . ' ' . $profile['key'] . ': ' . Time::readableSeconds($profile['time_elapse']) . ' - ' . Bytes::readable($profile['mem_delta']));
            }

        }
        if ($parentKey === null) {
            $logger->debug('[Profile] Peak memory: ' . Bytes::readable(memory_get_peak_usage()));
        }
    }
}
