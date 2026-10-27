<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\HashtagModel;
use PHPUnit\Framework\TestCase;

class HashtagModelTest extends TestCase
{
    public function testExtractHashtagsReturnsNormalizedUniqueTags(): void
    {
        $content = 'Hola #UABCS bienvenidos a #Ingenieria y #uabcs #2026!';
        $hashtags = HashtagModel::extractHashtags($content);

        $this->assertSame(['uabcs', 'ingenieria', '2026'], $hashtags);
    }

    public function testExtractHashtagsReturnsEmptyArrayWhenNoHashtagsPresent(): void
    {
        $content = 'Publicación sin etiquetas ni símbolos especiales.';
        $hashtags = HashtagModel::extractHashtags($content);

        $this->assertSame([], $hashtags);
    }

    public function testExtractHashtagsIgnoresStandaloneHashSymbol(): void
    {
        $content = 'Texto con # suelto y # etiqueta separada, pero #Valido_1 sí.';
        $hashtags = HashtagModel::extractHashtags($content);

        $this->assertSame(['valido_1'], $hashtags);
    }
}
