<?php

namespace App\Enum;

use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

enum Mode: string implements TranslatableInterface
{
    case Rx = 'rx';
    case Scaled = 'scaled';

    public function label(): string
    {
        return match ($this) {
            self::Rx => 'RX',
            self::Scaled => 'Scaled',
        };
    }

    /** Libellé affiché par les formulaires Symfony et EasyAdmin. */
    public function trans(TranslatorInterface $translator, ?string $locale = null): string
    {
        return $this->label();
    }
}
