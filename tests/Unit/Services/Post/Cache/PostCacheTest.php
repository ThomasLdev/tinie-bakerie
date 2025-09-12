<?php

declare(strict_types=1);

namespace App\Tests\Unit\Services\Post\Cache;

use App\Entity\Post;
use App\Repository\PostRepository;
use App\Services\Cache\PostCache;
use Exception;
use Generator;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

#[CoversClass(PostCache::class)]
class PostCacheTest extends MockeryTestCase
{
    protected function setUp(): void
    {
        $this->repository = Mockery::mock(PostRepository::class);
        $this->cache = Mockery::mock(CacheInterface::class);
        $this->postCache = new PostCache($this->cache, $this->repository);
    }

    public static function getLocalizedCachedPostsData(): Generator
    {
        $validResponse = [new Post(), new Post()];

        yield 'Get cached posts hits for english locale' => [
            $validResponse,
            'en',
            null,
        ];

        yield 'Get cached posts hits for french locale' => [
            $validResponse,
            'fr',
            null,
        ];

        yield 'Get cached posts exception for english locale' => [
            $validResponse,
            'en',
            new Exception(),
        ];

        yield 'Get cached posts exception for french locale' => [
            $validResponse,
            'fr',
            new Exception(),
        ];
    }

    public static function getLocalizedCachedPostData(): Generator
    {
        yield 'Get cached post hit fore english locale' => [
            new Post(),
            'post-1-en',
            'en',
            null,
        ];

        yield 'Get cached post hit fore french locale' => [
            new Post(),
            'post-1-fr',
            'fr',
            null,
        ];

        yield 'Get cached post exception fore english locale' => [
            new Post(),
            'post-1-en',
            'en',
            new Exception(),
        ];

        yield 'Get cached post exception fore french locale' => [
            new Post(),
            'post-1-fr',
            'fr',
            new Exception(),
        ];
    }

    #[DataProvider('getLocalizedCachedPostsData')]
    public function testGetLocalizedCachedPosts(array $expected, string $locale, ?Exception $exception): void
    {
        if ($exception instanceof Exception) {
            $this->cache
                ->shouldReceive('get')
                ->once()
                ->with('posts_index_'.$locale, Mockery::any())
                ->andThrow($exception);
        } else {
            $this->cache
                ->shouldReceive('get')
                ->once()
                ->with('posts_index_'.$locale, Mockery::on(static function ($callback) use ($expected) {
                    $item = Mockery::mock(ItemInterface::class);
                    $item->shouldReceive('expiresAfter')->once();

                    $result = $callback($item);

                    return $result === $expected;
                }))
                ->andReturn($expected);
        }

        $this->repository
            ->shouldReceive('findAllPublished')
            ->once()
            ->andReturn($expected);

        $this->assertSame($expected, $this->postCache->getLocalizedCachedPosts($locale));
    }

    #[DataProvider('getLocalizedCachedPostData')]
    public function testGetLocalizedCachedPost(?Post $expected, string $slug, string $locale, ?Exception $exception): void
    {
        if ($exception instanceof Exception) {
            $this->cache
                ->shouldReceive('get')
                ->once()
                ->with(sprintf('posts_show_%s_%s', $locale, $slug), Mockery::any())
                ->andThrow($exception);
        } else {
            $this->cache
                ->shouldReceive('get')
                ->once()
                ->with(
                    sprintf('posts_show_%s_%s', $locale, $slug),
                    Mockery::on(static function ($callback) use ($expected) {
                        $item = Mockery::mock(ItemInterface::class);
                        $item->shouldReceive('expiresAfter')->once();

                        $result = $callback($item);

                        return $result === $expected;
                    }))
                ->andReturn($expected);
        }

        $this->repository
            ->shouldReceive('findOnePublishedBySlug')
            ->once()
            ->with($slug)
            ->andReturn($expected);

        $this->assertSame($expected, $this->postCache->getLocalizedCachedPost($locale, $slug));
    }
}
