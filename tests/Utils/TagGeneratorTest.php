<?php
declare(strict_types = 1);

namespace App\Test\Utils;

use App\Utils\Config;
use App\Utils\Tag;
use App\Utils\TagGenerator;
use PHPUnit\Framework\TestCase;
use InvalidArgumentException;

final class TagGeneratorTest extends TestCase {
  private Config $config;

  protected function setUp(): void {
    $this->config = new Config(__DIR__ . '/../Fixtures');
  }

  /**
   * @dataProvider generatorDataProvider
   */
  public function testGenerator(string $tag, array $props, array $tags): void {
    $generator = TagGenerator::fromString($this->config, $tag);

    $this->assertSame($props[0], $generator->getExtName());
    $this->assertSame($props[1], $generator->getVersion());
    $this->assertSame($props[2], $generator->getPhpVersion());
    $this->assertSame($props[3], $generator->getOsName());

    $array = iterator_to_array($generator->generate());

    $this->assertContainsOnlyInstancesOf(
      Tag::class,
      $array
    );

    $arrayStr = array_map(
      function (Tag $item): string {
        return (string)$item;
      },
      $array
    );

    sort($tags);
    sort($arrayStr);

    $this->assertSame(
      $tags,
      $arrayStr
    );
  }

  public function generatorDataProvider(): array {
    return [
      [
        // single tag
        'ahocorasick:pecl@7.3.25-buster',
        ['ahocorasick', 'pecl', '7.3.25', 'buster'],
        ['ahocorasick:pecl@7.3.25-buster']
      ],
      [
        // tags for all extensions, with fixed version/php/os
        '*:pecl@7.3.25-buster',
        ['*', 'pecl', '7.3.25', 'buster'],
        [
          'ahocorasick:pecl@7.3.25-buster',
          'amqp:pecl@7.3.25-buster'
        ]
      ],
      [
        // tags for all versions, with fixed extension/php/os
        'ahocorasick:*@7.3.25-buster',
        ['ahocorasick', '*', '7.3.25', 'buster'],
        [
          'ahocorasick:dev@7.3.25-buster',
          'ahocorasick:pecl@7.3.25-buster'
        ]
      ],
      [
        // tags for all extension/version combination, with fixed php/os
        '*:*@7.3.25-buster',
        ['*', '*', '7.3.25', 'buster'],
        [
          'ahocorasick:dev@7.3.25-buster',
          'ahocorasick:pecl@7.3.25-buster',
          'amqp:dev@7.3.25-buster',
          'amqp:pecl@7.3.25-buster'
        ]
      ],
      [
        // tags for all php versions, with fixed extension/version/os
        'ahocorasick:pecl@*-buster',
        ['ahocorasick', 'pecl', '*', 'buster'],
        [
          'ahocorasick:pecl@7.3.25-buster',
          'ahocorasick:pecl@7.3.25-zts-buster',
          'ahocorasick:pecl@7.4.13-buster',
          'ahocorasick:pecl@7.4.13-zts-buster',
          'ahocorasick:pecl@8.0.0-buster',
          'ahocorasick:pecl@8.0.0-zts-buster'
        ]
      ],
      [
        // tags for all oses, with fixed extension/version/php
        'ahocorasick:pecl@7.3.25-*',
        ['ahocorasick', 'pecl', '7.3.25', '*'],
        [
          'ahocorasick:pecl@7.3.25-alpine',
          'ahocorasick:pecl@7.3.25-buster'
        ]
      ],
      [
        // tags for all php/os combination, with fixed extension/version
        'ahocorasick:pecl@*-*',
        ['ahocorasick', 'pecl', '*', '*'],
        [
          'ahocorasick:pecl@7.3.25-alpine',
          'ahocorasick:pecl@7.3.25-buster',
          'ahocorasick:pecl@7.3.25-zts-alpine',
          'ahocorasick:pecl@7.3.25-zts-buster',
          'ahocorasick:pecl@7.4.13-alpine',
          'ahocorasick:pecl@7.4.13-buster',
          'ahocorasick:pecl@7.4.13-zts-alpine',
          'ahocorasick:pecl@7.4.13-zts-buster',
          'ahocorasick:pecl@8.0.0-alpine',
          'ahocorasick:pecl@8.0.0-buster',
          'ahocorasick:pecl@8.0.0-zts-alpine',
          'ahocorasick:pecl@8.0.0-zts-buster'
        ]
      ],
      [
        // tags for all extension/version/php/os combinations
        '*:*@*-*',
        ['*', '*', '*', '*'],
        [
          'ahocorasick:dev@7.3.25-alpine',
          'ahocorasick:dev@7.3.25-buster',
          'ahocorasick:dev@7.3.25-zts-alpine',
          'ahocorasick:dev@7.3.25-zts-buster',
          'ahocorasick:dev@7.4.13-alpine',
          'ahocorasick:dev@7.4.13-buster',
          'ahocorasick:dev@7.4.13-zts-alpine',
          'ahocorasick:dev@7.4.13-zts-buster',
          'ahocorasick:dev@8.0.0-alpine',
          'ahocorasick:dev@8.0.0-buster',
          'ahocorasick:dev@8.0.0-zts-alpine',
          'ahocorasick:dev@8.0.0-zts-buster',
          'ahocorasick:pecl@7.3.25-alpine',
          'ahocorasick:pecl@7.3.25-buster',
          'ahocorasick:pecl@7.3.25-zts-alpine',
          'ahocorasick:pecl@7.3.25-zts-buster',
          'ahocorasick:pecl@7.4.13-alpine',
          'ahocorasick:pecl@7.4.13-buster',
          'ahocorasick:pecl@7.4.13-zts-alpine',
          'ahocorasick:pecl@7.4.13-zts-buster',
          'ahocorasick:pecl@8.0.0-alpine',
          'ahocorasick:pecl@8.0.0-buster',
          'ahocorasick:pecl@8.0.0-zts-alpine',
          'ahocorasick:pecl@8.0.0-zts-buster',
          'amqp:dev@7.3.25-alpine',
          'amqp:dev@7.3.25-buster',
          'amqp:dev@7.3.25-zts-alpine',
          'amqp:dev@7.3.25-zts-buster',
          'amqp:dev@7.4.13-alpine',
          'amqp:dev@7.4.13-buster',
          'amqp:dev@7.4.13-zts-alpine',
          'amqp:dev@7.4.13-zts-buster',
          'amqp:dev@8.0.0-alpine',
          'amqp:dev@8.0.0-buster',
          'amqp:dev@8.0.0-zts-alpine',
          'amqp:dev@8.0.0-zts-buster',
          'amqp:pecl@7.3.25-alpine',
          'amqp:pecl@7.3.25-buster',
          'amqp:pecl@7.3.25-zts-alpine',
          'amqp:pecl@7.3.25-zts-buster',
          'amqp:pecl@7.4.13-alpine',
          'amqp:pecl@7.4.13-buster',
          'amqp:pecl@7.4.13-zts-alpine',
          'amqp:pecl@7.4.13-zts-buster',
          'amqp:pecl@8.0.0-alpine',
          'amqp:pecl@8.0.0-buster',
          'amqp:pecl@8.0.0-zts-alpine',
          'amqp:pecl@8.0.0-zts-buster'
        ]
      ],
      [
        // tag translation (full)
        '*',
        ['*', '*', '*', '*'],
        [
          'ahocorasick:dev@7.3.25-alpine',
          'ahocorasick:dev@7.3.25-buster',
          'ahocorasick:dev@7.3.25-zts-alpine',
          'ahocorasick:dev@7.3.25-zts-buster',
          'ahocorasick:dev@7.4.13-alpine',
          'ahocorasick:dev@7.4.13-buster',
          'ahocorasick:dev@7.4.13-zts-alpine',
          'ahocorasick:dev@7.4.13-zts-buster',
          'ahocorasick:dev@8.0.0-alpine',
          'ahocorasick:dev@8.0.0-buster',
          'ahocorasick:dev@8.0.0-zts-alpine',
          'ahocorasick:dev@8.0.0-zts-buster',
          'ahocorasick:pecl@7.3.25-alpine',
          'ahocorasick:pecl@7.3.25-buster',
          'ahocorasick:pecl@7.3.25-zts-alpine',
          'ahocorasick:pecl@7.3.25-zts-buster',
          'ahocorasick:pecl@7.4.13-alpine',
          'ahocorasick:pecl@7.4.13-buster',
          'ahocorasick:pecl@7.4.13-zts-alpine',
          'ahocorasick:pecl@7.4.13-zts-buster',
          'ahocorasick:pecl@8.0.0-alpine',
          'ahocorasick:pecl@8.0.0-buster',
          'ahocorasick:pecl@8.0.0-zts-alpine',
          'ahocorasick:pecl@8.0.0-zts-buster',
          'amqp:dev@7.3.25-alpine',
          'amqp:dev@7.3.25-buster',
          'amqp:dev@7.3.25-zts-alpine',
          'amqp:dev@7.3.25-zts-buster',
          'amqp:dev@7.4.13-alpine',
          'amqp:dev@7.4.13-buster',
          'amqp:dev@7.4.13-zts-alpine',
          'amqp:dev@7.4.13-zts-buster',
          'amqp:dev@8.0.0-alpine',
          'amqp:dev@8.0.0-buster',
          'amqp:dev@8.0.0-zts-alpine',
          'amqp:dev@8.0.0-zts-buster',
          'amqp:pecl@7.3.25-alpine',
          'amqp:pecl@7.3.25-buster',
          'amqp:pecl@7.3.25-zts-alpine',
          'amqp:pecl@7.3.25-zts-buster',
          'amqp:pecl@7.4.13-alpine',
          'amqp:pecl@7.4.13-buster',
          'amqp:pecl@7.4.13-zts-alpine',
          'amqp:pecl@7.4.13-zts-buster',
          'amqp:pecl@8.0.0-alpine',
          'amqp:pecl@8.0.0-buster',
          'amqp:pecl@8.0.0-zts-alpine',
          'amqp:pecl@8.0.0-zts-buster'
        ]
      ],
      [
        // tag translation (extension/version)
        '*@7.3.25-buster',
        ['*', '*', '7.3.25', 'buster'],
        [
          'ahocorasick:dev@7.3.25-buster',
          'ahocorasick:pecl@7.3.25-buster',
          'amqp:dev@7.3.25-buster',
          'amqp:pecl@7.3.25-buster'
        ]
      ],
      [
        // tag translation (php/os)
        'ahocorasick:pecl@*',
        ['ahocorasick', 'pecl', '*', '*'],
        [
          'ahocorasick:pecl@7.3.25-alpine',
          'ahocorasick:pecl@7.3.25-buster',
          'ahocorasick:pecl@7.3.25-zts-alpine',
          'ahocorasick:pecl@7.3.25-zts-buster',
          'ahocorasick:pecl@7.4.13-alpine',
          'ahocorasick:pecl@7.4.13-buster',
          'ahocorasick:pecl@7.4.13-zts-alpine',
          'ahocorasick:pecl@7.4.13-zts-buster',
          'ahocorasick:pecl@8.0.0-alpine',
          'ahocorasick:pecl@8.0.0-buster',
          'ahocorasick:pecl@8.0.0-zts-alpine',
          'ahocorasick:pecl@8.0.0-zts-buster'
        ]
      ]
    ];
  }
}
