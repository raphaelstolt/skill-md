<?php

declare(strict_types=1);


namespace Stolt\Ai;

final class SkillMd
{
    private const MAX_NAME_LENGTH = 64;

    private const MAX_DESCRIPTION_LENGTH = 1024;

    /**
     * @var list<string>
     */
    private const ALLOWED_FIELDS = [
        'allowed-tools',
        'argument-hint',
        'arguments',
        'author',
        'compatibility',
        'description',
        'disable-model-invocation',
        'effort',
        'license',
        'metadata',
        'model',
        'name',
        'paths',
        'tags',
        'version',
        'when_to_use',
    ];

    /**
     * @param array<string, mixed> $additionalFields
     */
    private function __construct(
        private string $name,
        private string $description,
        private array  $additionalFields = [],
    ) {
        $this->additionalFields = self::filterAdditionalFields(
            $this->additionalFields
        );
    }

    private function dasherizeName(): string
    {
        $name = \strtolower($this->name);

        // Replace any non-alphanumeric sequence with a single dash
        $name = \preg_replace('/[^a-z0-9]+/', '-', $name) ?? $name;

        // Trim leading/trailing dashes
        return \trim($name, '-');
    }

    /**
     * @param array<string, mixed> $metadata
     */
    public static function fromArray(array $metadata): self
    {
        $name = (string) ($metadata['name'] ?? '');
        $description = (string) ($metadata['description'] ?? '');

        if (\strlen($name) > self::MAX_NAME_LENGTH) {
            throw new \InvalidArgumentException(
                'Name must not exceed ' . self::MAX_NAME_LENGTH . ' characters.'
            );
        }

        if (\strlen($description) > self::MAX_DESCRIPTION_LENGTH) {
            throw new \InvalidArgumentException(
                'Description must not exceed ' . self::MAX_DESCRIPTION_LENGTH . ' characters.'
            );
        }

        $additionalFields = \array_diff_key(
            $metadata,
            \array_flip(['name', 'description'])
        );

        $allowedAdditionalFields = \array_intersect_key(
            $additionalFields,
            \array_flip(self::ALLOWED_FIELDS)
        );

        $filteredAdditionalFields = \array_filter(
            $allowedAdditionalFields,
            static fn (mixed $value): bool => $value !== false
        );

        return new self(
            $name,
            $description,
            $filteredAdditionalFields
        );
    }

    public function name(): string
    {
        return $this->name;
    }

    public function description(): string
    {
        return $this->description;
    }

    public function has(string $key): bool
    {
        return $key === 'name'
            || $key === 'description'
            || \array_key_exists($key, $this->additionalFields);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return match ($key) {
            'name' => $this->name,
            'description' => $this->description,
            default => $this->additionalFields[$key] ?? $default,
        };
    }

    /**
     * @return array<string, mixed>
     */
    public function additionalFields(): array
    {
        return $this->additionalFields;
    }

    /**
     * @return list<string>
     */
    public function tags(): array
    {
        $tags = $this->get('tags', []);

        if (\is_array($tags) === false) {
            return [];
        }

        return \array_values($tags);
    }

    public function version(): ?string
    {
        $version = $this->get('version');

        if (\is_string($version) === false || $version === '') {
            return null;
        }

        return $version;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(bool $dasherizeName = false): array
    {
        if ($dasherizeName) {
            return [
                'name' => $this->dasherizeName(),
                'description' => $this->description,
                ...$this->additionalFields,
            ];
        }

        return [
            'name' => $this->name,
            'description' => $this->description,
            ...$this->additionalFields,
        ];
    }

    public function toMarkdown(string $body = ''): string
    {
        $frontmatter = [
            'name' => $this->dasherizeName(),
            'description' => $this->description,
            ...$this->additionalFields,
        ];

        $lines = ['---'];

        foreach ($frontmatter as $key => $value) {
            if (\is_array($value)) {
                $lines[] = \sprintf('%s:', $key);

                foreach ($value as $item) {
                    $lines[] = \sprintf('  - %s', (string) $item);
                }

                continue;
            }

            if (\is_bool($value)) {
                $value = $value ? 'true' : 'false';
            }

            $lines[] = \sprintf('%s: %s', $key, (string) $value);
        }

        $lines[] = '---';

        if ($body !== '') {
            $lines[] = '';
            $lines[] = $body;
        }

        return \implode(PHP_EOL, $lines);
    }

    /**
     * @param array<string, mixed> $additionalFields
     *
     * @return array<string, mixed>
     */
    private static function filterAdditionalFields(array $additionalFields): array
    {
        return \array_filter(
            $additionalFields,
            static fn (mixed $value): bool => $value !== false
        );
    }
}
