<?php

namespace App\Tests\Unit;

use App\Entity\Seance;
use App\Form\DataTransformer\TempsTransformer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Exception\TransformationFailedException;

final class TempsTransformerTest extends TestCase
{
    public function testConversions(): void
    {
        $t = new TempsTransformer();

        self::assertSame('12:05', $t->transform(725));
        self::assertSame('', $t->transform(null));
        self::assertSame(725, $t->reverseTransform('12:05'));
        self::assertSame(3725, $t->reverseTransform('1:02:05'));
        self::assertNull($t->reverseTransform('  '));
        self::assertSame('12:05', Seance::formaterTemps(725));
    }

    public function testFormatInvalide(): void
    {
        $this->expectException(TransformationFailedException::class);
        (new TempsTransformer())->reverseTransform('12:75');
    }
}
