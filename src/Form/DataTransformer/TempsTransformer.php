<?php

namespace App\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * Convertit un nombre de secondes ↔ « mm:ss » (ou « h:mm:ss »).
 *
 * @implements DataTransformerInterface<int, string>
 */
final class TempsTransformer implements DataTransformerInterface
{
    public function transform(mixed $value): string
    {
        if (null === $value) {
            return '';
        }

        return \sprintf('%d:%02d', intdiv((int) $value, 60), (int) $value % 60);
    }

    public function reverseTransform(mixed $value): ?int
    {
        $value = trim((string) $value);
        if ('' === $value) {
            return null;
        }
        if (!preg_match('/^(?:(\d{1,2}):)?(\d{1,3}):([0-5]\d)$/', $value, $m)) {
            $exception = new TransformationFailedException('Format de temps invalide.');
            $exception->setInvalidMessage('Saisissez un temps au format mm:ss, par exemple 12:34.');
            throw $exception;
        }

        return ((int) $m[1]) * 3600 + ((int) $m[2]) * 60 + (int) $m[3];
    }
}
