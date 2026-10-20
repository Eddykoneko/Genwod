<?php

namespace App\Twig\Components;

use App\Enum\Difficulte;
use App\Enum\WodType;
use App\Model\Page;
use App\Model\WodFiltre;
use App\Repository\WodRepository;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

/**
 * Recherche « live » de la bibliothèque : les résultats se mettent à jour pendant la saisie
 * et les filtres sont reflétés dans l'URL (partageable, bouton retour fonctionnel).
 */
#[AsLiveComponent]
final class WodRecherche
{
    use DefaultActionTrait;

    #[LiveProp(writable: true, url: true, onUpdated: 'retourPremierePage')]
    public string $q = '';

    #[LiveProp(writable: true, url: true, onUpdated: 'retourPremierePage')]
    public ?WodType $type = null;

    #[LiveProp(writable: true, url: true, onUpdated: 'retourPremierePage')]
    public ?Difficulte $difficulte = null;

    #[LiveProp(writable: true, url: true, onUpdated: 'retourPremierePage')]
    public ?int $dureeMax = null;

    #[LiveProp(writable: true, url: true, onUpdated: 'retourPremierePage')]
    public string $tri = 'recents';

    #[LiveProp(writable: true, url: true)]
    public int $page = 1;

    public function __construct(private readonly WodRepository $wods)
    {
    }

    /** @return Page<\App\Entity\Wod> */
    public function getResultats(): Page
    {
        return $this->wods->rechercher(new WodFiltre(
            q: '' !== trim($this->q) ? mb_substr($this->q, 0, 60) : null,
            type: $this->type,
            difficulte: $this->difficulte,
            dureeMax: null !== $this->dureeMax ? max(1, min(90, $this->dureeMax)) : null,
            tri: \in_array($this->tri, WodFiltre::TRIS, true) ? $this->tri : 'recents',
            page: max(1, $this->page),
        ));
    }

    #[LiveAction]
    public function allerPage(#[LiveArg] int $page): void
    {
        $this->page = max(1, $page);
    }

    #[LiveAction]
    public function reinitialiser(): void
    {
        $this->q = '';
        $this->type = null;
        $this->difficulte = null;
        $this->dureeMax = null;
        $this->tri = 'recents';
        $this->page = 1;
    }

    /** Toute modification d'un filtre ramène à la première page. */
    public function retourPremierePage(): void
    {
        $this->page = 1;
    }

    /** @return list<WodType> */
    public function getTypes(): array
    {
        return WodType::cases();
    }

    /** @return list<Difficulte> */
    public function getDifficultes(): array
    {
        return Difficulte::cases();
    }
}
