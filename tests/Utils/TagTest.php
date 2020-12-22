<?php
declare(strict_types = 1);

namespace App\Test\Utils;

use App\Utils\Tag;
use PHPUnit\Framework\TestCase;
use InvalidArgumentException;

final class TagTest extends TestCase {
  public function testValidPeclNoSpecialCharsNonZts(): void {
    $tag = Tag::fromString('ahocorasick:pecl@7.3.25-buster');

    $this->assertSame('ahocorasick', $tag->getExtName());
    $this->assertSame('pecl', $tag->getVersion());
    $this->assertSame('7.3.25', $tag->getPhpVersion());
    $this->assertSame('buster', $tag->getOsName());
    $this->assertFalse($tag->isZts());
    $this->assertSame('ahocorasick:pecl@7.3.25-buster', (string)$tag);
  }

  public function testValidPeclNoSpecialCharsZts(): void {
    $tag = Tag::fromString('ahocorasick:pecl@7.3.25-zts-buster');

    $this->assertSame('ahocorasick', $tag->getExtName());
    $this->assertSame('pecl', $tag->getVersion());
    $this->assertSame('7.3.25-zts', $tag->getPhpVersion());
    $this->assertSame('buster', $tag->getOsName());
    $this->assertTrue($tag->isZts());
    $this->assertSame('ahocorasick:pecl@7.3.25-zts-buster', (string)$tag);
  }

  public function testValidDevNoSpecialCharsNonZts(): void {
    $tag = Tag::fromString('ahocorasick:dev@7.3.25-buster');

    $this->assertSame('ahocorasick', $tag->getExtName());
    $this->assertSame('dev', $tag->getVersion());
    $this->assertSame('7.3.25', $tag->getPhpVersion());
    $this->assertSame('buster', $tag->getOsName());
    $this->assertFalse($tag->isZts());
    $this->assertSame('ahocorasick:dev@7.3.25-buster', (string)$tag);
  }

  public function testValidDevNoSpecialCharsZts(): void {
    $tag = Tag::fromString('ahocorasick:dev@7.3.25-zts-buster');

    $this->assertSame('ahocorasick', $tag->getExtName());
    $this->assertSame('dev', $tag->getVersion());
    $this->assertSame('7.3.25-zts', $tag->getPhpVersion());
    $this->assertSame('buster', $tag->getOsName());
    $this->assertTrue($tag->isZts());
    $this->assertSame('ahocorasick:dev@7.3.25-zts-buster', (string)$tag);
  }

  public function testValidPeclWithNumbersNonZts(): void {
    $tag = Tag::fromString('base58:pecl@7.3.25-buster');

    $this->assertSame('base58', $tag->getExtName());
    $this->assertSame('pecl', $tag->getVersion());
    $this->assertSame('7.3.25', $tag->getPhpVersion());
    $this->assertSame('buster', $tag->getOsName());
    $this->assertFalse($tag->isZts());
    $this->assertSame('base58:pecl@7.3.25-buster', (string)$tag);
  }

  public function testValidPeclWithNumbersZts(): void {
    $tag = Tag::fromString('base58:pecl@7.3.25-zts-buster');

    $this->assertSame('base58', $tag->getExtName());
    $this->assertSame('pecl', $tag->getVersion());
    $this->assertSame('7.3.25-zts', $tag->getPhpVersion());
    $this->assertSame('buster', $tag->getOsName());
    $this->assertTrue($tag->isZts());
    $this->assertSame('base58:pecl@7.3.25-zts-buster', (string)$tag);
  }

  public function testValidDevWithNumbersNonZts(): void {
    $tag = Tag::fromString('base58:dev@7.3.25-buster');

    $this->assertSame('base58', $tag->getExtName());
    $this->assertSame('dev', $tag->getVersion());
    $this->assertSame('7.3.25', $tag->getPhpVersion());
    $this->assertSame('buster', $tag->getOsName());
    $this->assertFalse($tag->isZts());
    $this->assertSame('base58:dev@7.3.25-buster', (string)$tag);
  }

  public function testValidDevWithNumbersZts(): void {
    $tag = Tag::fromString('base58:dev@7.3.25-zts-buster');

    $this->assertSame('base58', $tag->getExtName());
    $this->assertSame('dev', $tag->getVersion());
    $this->assertSame('7.3.25-zts', $tag->getPhpVersion());
    $this->assertSame('buster', $tag->getOsName());
    $this->assertTrue($tag->isZts());
    $this->assertSame('base58:dev@7.3.25-zts-buster', (string)$tag);
  }

  public function testValidPeclWithUnderscoreNonZts(): void {
    $tag = Tag::fromString('apcu_bc:pecl@7.3.25-buster');

    $this->assertSame('apcu_bc', $tag->getExtName());
    $this->assertSame('pecl', $tag->getVersion());
    $this->assertSame('7.3.25', $tag->getPhpVersion());
    $this->assertSame('buster', $tag->getOsName());
    $this->assertFalse($tag->isZts());
    $this->assertSame('apcu_bc:pecl@7.3.25-buster', (string)$tag);
  }

  public function testValidPeclWithUnderscoreZts(): void {
    $tag = Tag::fromString('apcu_bc:pecl@7.3.25-zts-buster');

    $this->assertSame('apcu_bc', $tag->getExtName());
    $this->assertSame('pecl', $tag->getVersion());
    $this->assertSame('7.3.25-zts', $tag->getPhpVersion());
    $this->assertSame('buster', $tag->getOsName());
    $this->assertTrue($tag->isZts());
    $this->assertSame('apcu_bc:pecl@7.3.25-zts-buster', (string)$tag);
  }

  public function testValidDevWithUnderscoreNonZts(): void {
    $tag = Tag::fromString('apcu_bc:dev@7.3.25-buster');

    $this->assertSame('apcu_bc', $tag->getExtName());
    $this->assertSame('dev', $tag->getVersion());
    $this->assertSame('7.3.25', $tag->getPhpVersion());
    $this->assertSame('buster', $tag->getOsName());
    $this->assertFalse($tag->isZts());
    $this->assertSame('apcu_bc:dev@7.3.25-buster', (string)$tag);
  }

  public function testValidDevWithUnderscoreZts(): void {
    $tag = Tag::fromString('apcu_bc:dev@7.3.25-zts-buster');

    $this->assertSame('apcu_bc', $tag->getExtName());
    $this->assertSame('dev', $tag->getVersion());
    $this->assertSame('7.3.25-zts', $tag->getPhpVersion());
    $this->assertSame('buster', $tag->getOsName());
    $this->assertTrue($tag->isZts());
    $this->assertSame('apcu_bc:dev@7.3.25-zts-buster', (string)$tag);
  }

  public function testValidPeclWithDashNonZts(): void {
    $tag = Tag::fromString('ext-fiber:pecl@7.3.25-buster');

    $this->assertSame('ext-fiber', $tag->getExtName());
    $this->assertSame('pecl', $tag->getVersion());
    $this->assertSame('7.3.25', $tag->getPhpVersion());
    $this->assertSame('buster', $tag->getOsName());
    $this->assertFalse($tag->isZts());
    $this->assertSame('ext-fiber:pecl@7.3.25-buster', (string)$tag);
  }

  public function testValidPeclWithDashZts(): void {
    $tag = Tag::fromString('ext-fiber:pecl@7.3.25-zts-buster');

    $this->assertSame('ext-fiber', $tag->getExtName());
    $this->assertSame('pecl', $tag->getVersion());
    $this->assertSame('7.3.25-zts', $tag->getPhpVersion());
    $this->assertSame('buster', $tag->getOsName());
    $this->assertTrue($tag->isZts());
    $this->assertSame('ext-fiber:pecl@7.3.25-zts-buster', (string)$tag);
  }

  public function testValidDevWithDashNonZts(): void {
    $tag = Tag::fromString('ext-fiber:dev@7.3.25-buster');

    $this->assertSame('ext-fiber', $tag->getExtName());
    $this->assertSame('dev', $tag->getVersion());
    $this->assertSame('7.3.25', $tag->getPhpVersion());
    $this->assertSame('buster', $tag->getOsName());
    $this->assertFalse($tag->isZts());
    $this->assertSame('ext-fiber:dev@7.3.25-buster', (string)$tag);
  }

  public function testValidDevWithDashZts(): void {
    $tag = Tag::fromString('ext-fiber:dev@7.3.25-zts-buster');

    $this->assertSame('ext-fiber', $tag->getExtName());
    $this->assertSame('dev', $tag->getVersion());
    $this->assertSame('7.3.25-zts', $tag->getPhpVersion());
    $this->assertSame('buster', $tag->getOsName());
    $this->assertTrue($tag->isZts());
    $this->assertSame('ext-fiber:dev@7.3.25-zts-buster', (string)$tag);
  }

  public function testInvalidFormat1(): void {
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('Tag does not match required format!');

    $tag = Tag::fromString('');
  }

  public function testInvalidFormat2(): void {
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('Tag does not match required format!');

    $tag = Tag::fromString('ext-name@dev:7.3.25/buster');
  }

  public function testInvalidExtName(): void {
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('Tag does not match required format!');

    $tag = Tag::fromString('EXT:dev@7.3.25-buster');
  }

  public function testInvalidVersion(): void {
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('Tag does not match required format!');

    $tag = Tag::fromString('ext:xpto@7.3.25-buster');
  }

  public function testInvalidPhpVersion(): void {
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('Tag does not match required format!');

    $tag = Tag::fromString('ext:dev@7.3-buster');
  }

  public function testInvalidZtsTag(): void {
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('Tag does not match required format!');

    $tag = Tag::fromString('ext:dev@7.3.25-ztt-buster');
  }

  public function testInvalidOsName(): void {
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('Tag does not match required format!');

    $tag = Tag::fromString('ext:dev@7.3.25-BUSTER');
  }
}
