<?php

namespace App\EventListener;

use App\Entity\Wod;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * Attribue un slug unique et lisible à chaque nouveau WOD : « tempete-polaire-3f9a1 ».
 */
#[AsEntityListener(event: Events::prePersist, entity: Wod::class)]
final class WodSlugListener
{
    public function __construct(private readonly SluggerInterface $slugger)
    {
    }

    public function prePersist(Wod $wod): void
    {
        if (null !== $wod->getSlug()) {
            return;
        }

        $base = $this->slugger->slug((string) $wod->getTitre())->lower()->truncate(80, '', false)->toString();
        $wod->setSlug(trim($base, '-').'-'.substr(bin2hex(random_bytes(3)), 0, 5));
    }
}
