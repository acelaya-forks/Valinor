<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Type\Parser\Factory;

use CuyZ\Valinor\Tests\Fake\Type\Parser\Template\FakeTemplateParser;
use CuyZ\Valinor\Type\Parser\CachedParser;
use CuyZ\Valinor\Type\Parser\Factory\LexingTypeParserFactory;
use CuyZ\Valinor\Type\Parser\Factory\TypeParserFactory;
use LogicException;
use PHPUnit\Framework\TestCase;
use stdClass;

final class LexingTypeParserFactoryTest extends TestCase
{
    private TypeParserFactory $typeParserFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->typeParserFactory = new LexingTypeParserFactory(new FakeTemplateParser());
    }

    public function test_get_parser_without_specification_returns_same_cached_parser(): void
    {
        $parserA = $this->typeParserFactory->get();
        $parserB = $this->typeParserFactory->get();

        self::assertInstanceOf(CachedParser::class, $parserA);
        self::assertSame($parserA, $parserB);
    }

    public function test_unhandled_specification_throws_exception(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Unhandled specification of type `stdClass`.');

        $this->typeParserFactory->get(new stdClass());
    }
}
