<?php

declare(strict_types=1);

namespace App\Tests\Unit\Services\Post\Cache;

use App\Entity\Post;
use App\Repository\PostRepository;
use App\Services\Cache\CacheKeyGenerator;
use App\Services\Cache\PostCache;
use App\Services\Locale\Locales;
use Generator;
use InvalidArgumentException;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

#[CoversClass(PostCache::class)]
#[CoversClass(CacheKeyGenerator::class)]
#[CoversClass(Locales::class)]
class PostCacheTest extends MockeryTestCase
{
    private PostRepository $repository;

    private CacheInterface $cache;

    private PostCache $postCache;

    protected function setUp(): void
    {
        $this->repository = Mockery::mock(PostRepository::class);
        $this->cache = Mockery::mock(CacheInterface::class);
        $locales = new Locales('fr|en');
        $cacheKeyGenerator = new CacheKeyGenerator();
        $this->postCache = new PostCache($this->cache, $this->repository, $cacheKeyGenerator, $locales);
    }

    public static function getCachePostsData(): Generator
    {
        $expected = [new Post(), new Post()];

        yield 'Get cached posts hits for english locale' => [$expected, 'en'];

        yield 'Get cached posts hits for french locale' => [$expected, 'fr'];
    }

    public static function getCachePostData(): Generator
    {
        yield 'Get cached post hit fore english locale' => [new Post(), 'post-1-en', 'en'];

        yield 'Get cached post hit fore french locale' => [new Post(), 'post-1-fr', 'fr'];
    }

    #[DataProvider('getCachePostsData')]
    public function testCachePostHits(array $expected, string $locale): void
    {
        $this->cache
            ->shouldReceive('get')
            ->once()
            ->andReturn($expected);

        $this->repository
            ->shouldReceive('findAllActive')
            ->never()
        ;

        $this->assertSame($expected, $this->postCache->get($locale));
    }

    #[DataProvider('getCachePostsData')]
    public function testCachePostNoHits(array $expected, string $locale): void
    {
        $this->cache
            ->shouldReceive('get')
            ->once()
            ->andReturnUsing(function ($key, $callback) {
                $item = Mockery::mock(ItemInterface::class);
                $item->shouldReceive('expiresAfter')->once();

                return $callback($item);
            });

        $this->repository
            ->shouldReceive('findAllActive')
            ->once()
            ->andReturn($expected)
        ;

        $this->assertSame($expected, $this->postCache->get($locale));
    }

    public function testCachePostsException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->cache
            ->shouldReceive('get')
            ->once()
            ->andThrow(new InvalidArgumentException());

        $this->repository
            ->shouldReceive('findAllPublished')
            ->never()
        ;

        $this->postCache->get('fr');
    }

    #[DataProvider('getCachePostData')]
    public function testCachePostHit(?Post $expected, string $slug, string $locale): void
    {
        $this->cache
            ->shouldReceive('get')
            ->once()
            ->andReturn($expected);

        $this->repository
            ->shouldReceive('findOneActive')
            ->never()
        ;

        $this->assertSame($expected, $this->postCache->getOne($locale, $slug));
    }

    #[DataProvider('getCachePostData')]
    public function testCachePostNoHit(?Post $expected, string $slug, string $locale): void
    {
        $this->cache
            ->shouldReceive('get')
            ->once()
            ->andReturnUsing(function ($key, $callback) {
                $item = Mockery::mock(ItemInterface::class);
                $item->shouldReceive('expiresAfter')->once();

                return $callback($item);
            });

        $this->repository
            ->shouldReceive('findOneActive')
            ->once()
            ->andReturn($expected)
        ;

        $this->assertSame($expected, $this->postCache->getOne($locale, $slug));
    }

    public function testCachePostException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->cache
            ->shouldReceive('get')
            ->once()
            ->andThrow(new InvalidArgumentException());

        $this->repository
            ->shouldReceive('findAllPublished')
            ->never()
        ;

        $this->postCache->getOne('fr', 'test');
    }
}
