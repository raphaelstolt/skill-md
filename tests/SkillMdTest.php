<?php

declare(strict_types=1);

namespace Stolt\Ai\Tests;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Stolt\Ai\SkillMd;

final class SkillMdTest extends TestCase
{
    #[Test]
    public function itCanBeCreatedFromArray(): void
    {
        $metadata = [
            'name' => 'Code Review',
            'description' => 'Performs automated code reviews.',
            'body' => '# Some longer Markdown content',
            'version' => '1.0.0',
            'tags' => ['php', 'qa'],
        ];

        $skill = SkillMd::fromArray($metadata);

        self::assertSame('Code Review', $skill->name());
        self::assertSame('Performs automated code reviews.', $skill->description());
        self::assertSame('# Some longer Markdown content', $skill->body());
    }

    #[Test]
    public function itCanBeCreatedViaFactoryMethod(): void
    {
        $skill = SkillMd::create(
            'Code Review',
            'Reviews pull requests.',
            'Please review carefully.',
            [
                'tags' => ['php', 'review'],
                'version' => '1.0.0',
            ]
        );

        self::assertSame('Code Review', $skill->name());
        self::assertSame(
            'Reviews pull requests.',
            $skill->description()
        );

        self::assertSame(
            'Please review carefully.',
            $skill->body()
        );

        self::assertSame(
            ['php', 'review'],
            $skill->tags()
        );

        self::assertSame(
            '1.0.0',
            $skill->version()
        );
    }

    #[Test]
    public function itCanAlsoHandleAMarkdownBody(): void
    {
        $skill = SkillMd::fromArray([
            'name' => 'Code Review',
            'description' => 'Reviews pull requests.',
            'body' => 'Please review the diff carefully.',
            'tags' => ['php', 'review'],
        ]);

        self::assertTrue($skill->has('body'));

        self::assertSame(
            'Please review the diff carefully.',
            $skill->body()
        );

        self::assertSame(
            'Please review the diff carefully.',
            $skill->get('body')
        );

        self::assertSame(
            [
                'name' => 'Code Review',
                'description' => 'Reviews pull requests.',
                'body' => 'Please review the diff carefully.',
                'tags' => ['php', 'review'],
            ],
            $skill->toArray()
        );

        self::assertStringContainsString(
            'Please review the diff carefully.',
            $skill->toMarkdown()
        );
    }

    #[Test]
    public function itFiltersOutFalseAdditionalFieldValues(): void
    {
        $skill = SkillMd::fromArray([
            'name' => 'Static Analysis',
            'description' => 'Analyzes PHP codebases.',
            'body' => 'Some longer Markdown content',
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

    #[Test]
    public function constructorFiltersOutFalseAdditionalFieldValues(): void
    {
        $reflection = new \ReflectionClass(SkillMd::class);

        /** @var SkillMd $skill */
        $skill = $reflection->newInstanceWithoutConstructor();

        $constructor = $reflection->getConstructor();

        $constructor->invoke(
            $skill,
            'Static Analysis',
            'Analyzes PHP codebases.',
            'Some Markdown body',
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

    #[Test]
    public function itStoresAdditionalFields(): void
    {
        $metadata = [
            'name' => 'Lint',
            'description' => 'Checks README files.',
            'body' => 'Some longer Markdown content',
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

    #[Test]
    public function itCanDetermineIfAFieldExists(): void
    {
        $skill = SkillMd::fromArray([
            'name' => 'Test',
            'description' => 'Description',
            'body' => 'Some longer Markdown content',
            'foo' => 'bar',
        ]);

        self::assertTrue($skill->has('name'));
        self::assertTrue($skill->has('description'));

        self::assertFalse($skill->has('missing'));
    }

    #[Test]
    public function itCanRetrieveFields(): void
    {
        $skill = SkillMd::fromArray([
            'name' => 'Analyzer',
            'description' => 'Analyzes projects.',
            'body' => '# Some longer Markdown content',
            'license' => 'MIT',
        ]);

        self::assertSame('Analyzer', $skill->get('name'));
        self::assertSame('Analyzes projects.', $skill->get('description'));
        self::assertSame('MIT', $skill->get('license'));
    }

    #[Test]
    public function itReturnsDefaultValueForMissingField(): void
    {
        $skill = SkillMd::fromArray([
            'name' => 'Analyzer',
            'description' => 'Analyzes projects.',
            'body' => 'Some longer Markdown content',
        ]);

        self::assertNull($skill->get('missing'));
        self::assertSame('default', $skill->get('missing', 'default'));
    }

    #[Test]
    public function itReturnsTags(): void
    {
        $skill = SkillMd::fromArray([
            'name' => 'Skill',
            'description' => 'Description',
            'body' => 'Some longer Markdown content',
            'tags' => ['php', 'testing'],
        ]);

        self::assertSame(
            ['php', 'testing'],
            $skill->tags()
        );
    }

    #[Test]
    public function itReturnsEmptyTagsWhenTagsAreNotAnArray(): void
    {
        $skill = SkillMd::fromArray([
            'name' => 'Skill',
            'description' => 'Description',
            'body' => 'Some longer Markdown content',
            'tags' => 'invalid',
        ]);

        self::assertSame([], $skill->tags());
    }

    #[Test]
    public function itReturnsVersion(): void
    {
        $skill = SkillMd::fromArray([
            'name' => 'Skill',
            'description' => 'Description',
            'body' => 'Some longer Markdown content',
            'version' => '1.2.3',
        ]);

        self::assertSame('1.2.3', $skill->version());
    }

    #[Test]
    public function itReturnsNullWhenVersionIsMissing(): void
    {
        $skill = SkillMd::fromArray([
            'name' => 'Skill',
            'description' => 'Description',
            'body' => 'Some longer Markdown content',
        ]);

        self::assertNull($skill->version());
    }

    #[Test]
    public function itReturnsNullWhenVersionIsEmpty(): void
    {
        $skill = SkillMd::fromArray([
            'name' => 'Skill',
            'description' => 'Description',
            'body' => 'Some longer Markdown content',
            'version' => '',
        ]);

        self::assertNull($skill->version());
    }

    #[Test]
    public function itReturnsNullWhenVersionIsNotAString(): void
    {
        $skill = SkillMd::fromArray([
            'name' => 'Skill',
            'description' => 'Description',
            'body' => 'Some longer Markdown content',
            'version' => 123,
        ]);

        self::assertNull($skill->version());
    }

    #[Test]
    public function itCanBeConvertedToArray(): void
    {
        $metadata = [
            'name' => 'Formatter',
            'description' => 'Formats code.',
            'body' => '# Some Markdown content',
            'version' => '1.0.0',
            'tags' => ['php'],
        ];

        $skill = SkillMd::fromArray($metadata);

        self::assertSame($metadata, $skill->toArray());
    }

    #[Test]
    public function itCanBeConvertedToArrayWithDasherizedName(): void
    {
        $metadata = [
            'name' => 'Super Code Formatter',
            'description' => 'Formats code.',
            'body' => 'Some longer Markdown content',
            'version' => '1.0.0',
            'tags' => ['php'],
        ];

        $skill = SkillMd::fromArray($metadata);


        $skillArray = $skill->toArray(true);
        self::assertNotSame($metadata, $skillArray);
        self::assertSame('super-code-formatter', $skillArray['name']);
    }

    #[Test]
    public function itOnlyStoresAllowedAdditionalFields(): void
    {
        $skill = SkillMd::fromArray([
            'name' => 'Static Analysis',
            'description' => 'Analyzes PHP codebases.',
            'body' => 'Some longer Markdown content',
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

    #[Test]
    public function itCanBeConvertedToMarkdown(): void
    {
        $markdownBody = <<<MARKDOWN_BODY
# Usage

Run the analyzer against your project.
MARKDOWN_BODY;

        $skill = SkillMd::fromArray([
            'name' => 'static-analysis',
            'description' => 'Analyze PHP projects for quality issues.',
            'body' => $markdownBody,
            'author' => 'Raphael Stolt',
            'version' => '1.0.0',
            'tags' => ['php', 'qa'],
            'disable-model-invocation' => true,
        ]);

        $markdown = $skill->toMarkdown(
            "# Usage\n\nRun the analyzer against your project."
        );

        $expected = <<<MARKDOWN
---
name: static-analysis
description: Analyze PHP projects for quality issues.
author: Raphael Stolt
version: 1.0.0
tags:
  - php
  - qa
disable-model-invocation: true
---

# Usage

Run the analyzer against your project.
MARKDOWN;

        self::assertSame($expected, $markdown);
    }

    #[Test]
    public function itCanBeConvertedToMarkdownWithoutBody(): void
    {
        $skill = SkillMd::fromArray([
            'name' => 'My Super Static Analysis Skill',
            'description' => 'Analyze PHP projects for quality issues.',
            'body' => '# Some longer Markdown content',
        ]);

        $markdown = $skill->toMarkdown();

        $expected = <<<MARKDOWN
---
name: my-super-static-analysis-skill
description: Analyze PHP projects for quality issues.
---

# Some longer Markdown content
MARKDOWN;

        self::assertSame($expected, $markdown);
    }

    #[Test]
    public function itThrowsWhenNameIsTooLong(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Name must not exceed 64 characters.');

        SkillMd::fromArray([
            'name' => \str_repeat('a', 65),
            'description' => 'Valid description',
        ]);
    }

    #[Test]
    public function itThrowsWhenDescriptionIsTooLong(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Description must not exceed 1024 characters.');

        SkillMd::fromArray([
            'name' => 'valid-name',
            'description' => \str_repeat('a', 1025),
        ]);
    }

    #[Test]
    public function itThrowsWhenBodyIsTooLong(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Body must not exceed 65535 characters.');

        SkillMd::fromArray([
            'name' => 'valid-name',
            'description' => 'valid description',
            'body' => \str_repeat('a', 65536),
        ]);
    }

    #[Test]
    public function itThrowsWhenNameIsNotSet(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Name is required.');

        SkillMd::fromArray([
            'description' => 'Some description',
            'body' => '# Some longer Markdown content',
        ]);
    }

    #[Test]
    public function itThrowsWhenDescriptionIsNotSet(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Description is required.');

        SkillMd::fromArray([
            'name' => 'some-name',
            'body' => '# Some longer Markdown content',
        ]);
    }

    #[Test]
    public function itThrowsWhenBodyIsNotSet(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Body is required.');

        SkillMd::fromArray([
            'name' => 'some-name',
            'description' => 'Some description'
        ]);
    }

    #[Test]
    public function itAcceptsMaxAllowedLengths(): void
    {
        $skill = SkillMd::fromArray([
            'name' => \str_repeat('a', 64),
            'description' => \str_repeat('b', 1024),
            'body' => \str_repeat('c', 65535),
        ]);

        self::assertSame(64, \strlen($skill->name()));
        self::assertSame(1024, \strlen($skill->description()));
        self::assertSame(65535, \strlen($skill->body()));
    }
}
