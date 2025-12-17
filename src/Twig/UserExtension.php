<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class UserExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('is_banned', [$this, 'isBanned']),
        ];
    }

    /**
     * Vérifie si un utilisateur est banni
     * Un utilisateur est considéré comme banni si banned_until existe et est dans le futur
     */
    public function isBanned(array $user): bool
    {
        if (!isset($user['banned_until']) || $user['banned_until'] === null) {
            return false;
        }

        // Si banned_until est une chaîne, on la convertit en DateTime
        if (is_string($user['banned_until'])) {
            $bannedUntil = new \DateTime($user['banned_until']);
        } else {
            $bannedUntil = $user['banned_until'];
        }

        // Comparer avec la date actuelle
        $now = new \DateTime();
        return $bannedUntil > $now;
    }
}
