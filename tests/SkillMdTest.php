<?php

declare(strict_types=1);

namespace Stolt\Ai\Tests;

use PHPUnit\Framework\TestCase;
use Stolt\Ai\SkillMd;

final class SkillMdTest extends TestCase
{
    public function testItCanBeCreatedFromArray(): void
    {
        $metadata = [
            'name' => 'Code Review',
            'description' => 'Performs automated code reviews.',
            'version' => '1.0.0',
            'tags' => ['php', 'qa'],
        ];

        $skill = SkillMd::fromArray($metadata);

        self::assertSame('Code Review', $skill->name());
        self::assertSame('Performs automated code reviews.', $skill->description());
    }

    public function testItFiltersOutFalseAdditionalFieldValues(): void
    {
        $skill = SkillMd::fromArray([
            'name' => 'Static Analysis',
            'description' => 'Analyzes PHP codebases.',
            'author' => 'Raphael Stolt',
            'version' => false,
            'tags' => ['php', 'qa'],
            'disable-model-invocation' => false,
        ]);

        self::assertSame(
            [
                'author' => 'Raphael Stolt',
                'tags' => ['php', 'qa'],
            ],
            $skill->additionalFields()
        );

        self::assertFalse($skill->has('version'));
        self::assertFalse($skill->has('disable-model-invocation'));

        self::assertNull($skill->get('version'));
        self::assertNull($skill->get('disable-model-invocation'));
    }

    public function testConstructorFiltersOutFalseAdditionalFieldValues(): void
    {
        $reflection = new \ReflectionClass(SkillMd::class);

        /** @var SkillMd $skill */
        $skill = $reflection->newInstanceWithoutConstructor();

        $constructor = $reflection->getConstructor();

        $constructor->invoke(
            $skill,
            'Static Analysis',
            'Analyzes PHP codebases.',
            [
                'author' => 'Raphael Stolt',
                'version' => false,
                'tags' => ['php', 'qa'],
            ]
        );

        self::assertSame(
            [
                'author' => 'Raphael Stolt',
                'tags' => ['php', 'qa'],
            ],
            $skill->additionalFields()
        );

        self::assertFalse($skill->has('version'));
    }

    public function testItStoresAdditionalFields(): void
    {
        $metadata = [
            'name' => 'Lint',
            'description' => 'Checks README files.',
            'version' => '2.1.0',
            'author' => 'Raphael',
        ];

        $skill = SkillMd::fromArray($metadata);

        self::assertSame(
            [
                'version' => '2.1.0',
                'author' => 'Raphael',
            ],
            $skill->additionalFields()
        );
    }

    public function testItCanDetermineIfAFieldExists(): void
    {
        $skill = SkillMd::fromArray([
            'name' => 'Test',
            'description' => 'Description',
            'foo' => 'bar',
        ]);

        self::assertTrue($skill->has('name'));
        self::assertTrue($skill->has('description'));

        self::assertFalse($skill->has('missing'));
    }

    public function testItCanRetrieveFields(): void
    {
        $skill = SkillMd::fromArray([
            'name' => 'Analyzer',
            'description' => 'Analyzes projects.',
            'license' => 'MIT',
        ]);

        self::assertSame('Analyzer', $skill->get('name'));
        self::assertSame('Analyzes projects.', $skill->get('description'));
        self::assertSame('MIT', $skill->get('license'));
    }

    public function testItReturnsDefaultValueForMissingField(): void
    {
        $skill = SkillMd::fromArray([
            'name' => 'Analyzer',
            'description' => 'Analyzes projects.',
        ]);

        self::assertNull($skill->get('missing'));
        self::assertSame('default', $skill->get('missing', 'default'));
    }

    public function testItReturnsTags(): void
    {
        $skill = SkillMd::fromArray([
            'name' => 'Skill',
            'description' => 'Description',
            'tags' => ['php', 'testing'],
        ]);

        self::assertSame(
            ['php', 'testing'],
            $skill->tags()
        );
    }

    public function testItReturnsEmptyTagsWhenTagsAreNotAnArray(): void
    {
        $skill = SkillMd::fromArray([
            'name' => 'Skill',
            'description' => 'Description',
            'tags' => 'invalid',
        ]);

        self::assertSame([], $skill->tags());
    }

    public function testItReturnsVersion(): void
    {
        $skill = SkillMd::fromArray([
            'name' => 'Skill',
            'description' => 'Description',
            'version' => '1.2.3',
        ]);

        self::assertSame('1.2.3', $skill->version());
    }

    public function testItReturnsNullWhenVersionIsMissing(): void
    {
        $skill = SkillMd::fromArray([
            'name' => 'Skill',
            'description' => 'Description',
        ]);

        self::assertNull($skill->version());
    }

    public function testItReturnsNullWhenVersionIsEmpty(): void
    {
        $skill = SkillMd::fromArray([
            'name' => 'Skill',
            'description' => 'Description',
            'version' => '',
        ]);

        self::assertNull($skill->version());
    }

    public function testItReturnsNullWhenVersionIsNotAString(): void
    {
        $skill = SkillMd::fromArray([
            'name' => 'Skill',
            'description' => 'Description',
            'version' => 123,
        ]);

        self::assertNull($skill->version());
    }

    public function testItCanBeConvertedToArray(): void
    {
        $metadata = [
            'name' => 'Formatter',
            'description' => 'Formats code.',
            'version' => '1.0.0',
            'tags' => ['php'],
        ];

        $skill = SkillMd::fromArray($metadata);

        self::assertSame($metadata, $skill->toArray());
    }

    public function testItOnlyStoresAllowedAdditionalFields(): void
    {
        $skill = SkillMd::fromArray([
            'name' => 'Static Analysis',
            'description' => 'Analyzes PHP codebases.',
            'author' => 'Raphael Stolt',
            'version' => '1.0.0',
            'tags' => ['php', 'qa'],
            'unsupported-field' => 'should-be-ignored',
            'another-invalid-field' => true,
        ]);

        self::assertSame(
            [
                'author' => 'Raphael Stolt',
                'version' => '1.0.0',
                'tags' => ['php', 'qa'],
            ],
            $skill->additionalFields()
        );

        self::assertTrue($skill->has('author'));
        self::assertTrue($skill->has('version'));
        self::assertTrue($skill->has('tags'));

        self::assertFalse($skill->has('unsupported-field'));
        self::assertFalse($skill->has('another-invalid-field'));

        self::assertNull($skill->get('unsupported-field'));
        self::assertNull($skill->get('another-invalid-field'));
    }
}
