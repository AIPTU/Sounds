<?php

declare(strict_types=1);

namespace aiptu\sounds;

class SoundFactory
{
    public static function create(string $soundId, float $volume = null, float $pitch = null): SoundImpl {
        $sound = new SoundImpl($soundId);
        if ($volume !== null) {
            $sound->setVolume($volume);
        }
        if ($pitch !== null) {
            $sound->setPitch($pitch);
        }

        return $sound;
    }
}