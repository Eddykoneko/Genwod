<?php

namespace App\EventListener;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Loader\FilesystemLoader;

/**
 * Aperçu des propositions de refonte graphique.
 *
 * Le design est choisi par ?design=… (mémorisé dans un cookie). Chaque design peut
 * redéfinir n'importe quel gabarit dans templates/designs/<design>/ : Twig y cherche
 * d'abord, puis retombe sur les gabarits par défaut.
 */
final class DesignListener
{
    public const DESIGNS = ['brut' => 'Brut', 'editorial' => 'Éditorial', 'neon' => 'Néon'];
    public const COOKIE = 'genwod_design';

    /** @var list<string>|null chemins Twig d'origine (restaurés à chaque requête : serveurs persistants) */
    private ?array $cheminsOrigine = null;

    public function __construct(
        #[Autowire(service: 'twig.loader.native_filesystem')]
        private readonly FilesystemLoader $loader,
        #[Autowire('%kernel.project_dir%/templates/designs')]
        private readonly string $dossier,
    ) {
    }

    #[AsEventListener(event: KernelEvents::REQUEST, priority: 20)]
    public function choisir(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $demande = $request->query->get('design');
        $design = \is_string($demande) ? $demande : $request->cookies->get(self::COOKIE, '');

        if (!\array_key_exists((string) $design, self::DESIGNS)) {
            $design = '';
        }
        $request->attributes->set('_design', $design);
        $request->attributes->set('_design_change', \is_string($demande));

        $this->cheminsOrigine ??= $this->loader->getPaths();
        $this->loader->setPaths($this->cheminsOrigine);
        if ('' !== $design) {
            $this->loader->prependPath($this->dossier.'/'.$design);
        }
    }

    #[AsEventListener(event: KernelEvents::RESPONSE)]
    public function memoriser(ResponseEvent $event): void
    {
        $request = $event->getRequest();
        if (!$event->isMainRequest() || true !== $request->attributes->get('_design_change')) {
            return;
        }

        $event->getResponse()->headers->setCookie(
            Cookie::create(self::COOKIE, (string) $request->attributes->get('_design'), new \DateTimeImmutable('+30 days'))->withSameSite('lax'),
        );
    }
}
