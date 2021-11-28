<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Type\Types;

use CuyZ\Valinor\Tests\Fake\Type\FakeType;
use CuyZ\Valinor\Tests\Fixture\Object\StringableObject;
use CuyZ\Valinor\Type\Types\Exception\InvalidStringValueType;
use CuyZ\Valinor\Type\Types\Exception\InvalidStringValue;
use CuyZ\Valinor\Type\Types\MixedType;
use CuyZ\Valinor\Type\Types\UnionType;
use CuyZ\Valinor\Type\Types\StringValueType;
use PHPUnit\Framework\TestCase;
use stdClass;

final class StringValueTypeTest extends TestCase
{
    private StringValueType $type;

    protected function setUp(): void
    {
        parent::setUp();

        $this->type = new StringValueType('Schwifty!');
    }

    public function test_accepts_correct_values(): void
    {
        $type = new StringValueType('Schwifty!');
        $typeSingleQuote = StringValueType::singleQuote('Schwifty!');
        $typeDoubleQuote = StringValueType::doubleQuote('Schwifty!');

        self::assertTrue($type->accepts('Schwifty!'));
        self::assertTrue($typeSingleQuote->accepts('Schwifty!'));
        self::assertTrue($typeDoubleQuote->accepts('Schwifty!'));
    }

    public function test_does_not_accept_incorrect_values(): void
    {
        self::assertFalse($this->type->accepts('other string'));
        self::assertFalse($this->type->accepts(null));
        self::assertFalse($this->type->accepts(42.1337));
        self::assertFalse($this->type->accepts(404));
        self::assertFalse($this->type->accepts(['foo' => 'bar']));
        self::assertFalse($this->type->accepts(false));
        self::assertFalse($this->type->accepts(new stdClass()));
    }

    public function test_can_cast_stringable_value(): void
    {
        self::assertTrue($this->type->canCast('Schwifty!'));
        self::assertTrue($this->type->canCast(42.1337));
        self::assertTrue($this->type->canCast(404));
        self::assertTrue($this->type->canCast(new StringableObject()));
    }

    public function test_cannot_cast_other_types(): void
    {
        self::assertFalse($this->type->canCast(null));
        self::assertFalse($this->type->canCast(['foo' => 'bar']));
        self::assertFalse($this->type->canCast(false));
        self::assertFalse($this->type->canCast(new stdClass()));
    }

    /**
     * @dataProvider cast_value_returns_correct_result_data_provider
     *
     * @param mixed $value
     */
    public function test_cast_value_returns_correct_result(StringValueType $type, $value, string $expected): void
    {
        self::assertSame($expected, $type->cast($value));
    }

    public function cast_value_returns_correct_result_data_provider(): array
    {
        return [
            'String from float' => [
                'type' => new StringValueType('404.42'),
                'value' => 404.42,
                'expected' => '404.42',
            ],
            'String from integer' => [
                'type' => new StringValueType('42'),
                'value' => 42,
                'expected' => '42',
            ],
            'String from object' => [
                'type' => new StringValueType('foo'),
                'value' => new StringableObject(),
                'expected' => 'foo',
            ],
            'String from string' => [
                'type' => new StringValueType('bar'),
                'value' => 'bar',
                'expected' => 'bar',
            ],
        ];
    }

    public function test_cast_invalid_value_throws_exception(): void
    {
        $this->expectException(InvalidStringValueType::class);
        $this->expectExceptionCode(1631263954);
        $this->expectExceptionMessage('Value of type `stdClass` does not match string value `Schwifty!`.');

        $this->type->cast(new stdClass());
    }

    public function test_cast_another_string_value_throws_exception(): void
    {
        $this->expectException(InvalidStringValue::class);
        $this->expectExceptionCode(1631263740);
        $this->expectExceptionMessage('Values `Schwifty?` and `Schwifty!` do not match.');

        $typeA = new StringValueType('Schwifty!');
        $typeB = new StringValueType('Schwifty?');

        $typeA->cast($typeB);
    }

    public function test_string_value_is_correct(): void
    {
        $type = new StringValueType('Schwifty!');
        $typeSingleQuote = StringValueType::singleQuote('Schwifty!');
        $typeDoubleQuote = StringValueType::doubleQuote('Schwifty!');

        self::assertSame('Schwifty!', (string)$type);
        self::assertSame("'Schwifty!'", (string)$typeSingleQuote);
        self::assertSame('"Schwifty!"', (string)$typeDoubleQuote);
    }

    public function test_matches_same_type_with_same_value(): void
    {
        $typeA = new StringValueType('Schwifty!');
        $typeB = new StringValueType('Schwifty!');
        $typeC = StringValueType::singleQuote('Schwifty!');
        $typeD = StringValueType::doubleQuote('Schwifty!');

        self::assertTrue($typeA->matches($typeB));
        self::assertTrue($typeA->matches($typeC));
        self::assertTrue($typeA->matches($typeD));
    }

    public function test_does_not_match_same_type_with_different_value(): void
    {
        $typeA = new StringValueType('Schwifty!');
        $typeB = new StringValueType('Schwifty?');

        self::assertFalse($typeA->matches($typeB));
    }

    public function test_does_not_match_other_type(): void
    {
        self::assertFalse($this->type->matches(new FakeType()));
    }

    public function test_matches_mixed_type(): void
    {
        self::assertTrue($this->type->matches(new MixedType()));
    }

    public function test_matches_union_type_containing_string_type(): void
    {
        $unionType = new UnionType(
            new FakeType(),
            $this->type,
            new FakeType(),
        );

        self::assertTrue($this->type->matches($unionType));
    }

    public function test_does_not_match_union_type_not_containing_string_type(): void
    {
        $unionType = new UnionType(new FakeType(), new FakeType());

        self::assertFalse($this->type->matches($unionType));
    }
}
