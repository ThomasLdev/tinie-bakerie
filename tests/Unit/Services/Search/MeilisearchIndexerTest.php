<?php

declare(strict_types=1);

namespace App\Tests\Unit\Services\Search;

use App\Services\Search\MeilisearchIndexer;
use Meilisearch\Client;
use Meilisearch\Endpoints\Indexes;
use Meilisearch\Exceptions\ApiException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * @internal
 */
#[CoversClass(MeilisearchIndexer::class)]
final class MeilisearchIndexerTest extends TestCase
{
    private MockObject&Client $client;
    private MockObject&LoggerInterface $logger;
    private MeilisearchIndexer $indexer;

    protected function setUp(): void
    {
        $this->client = $this->createMock(Client::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->indexer = new MeilisearchIndexer($this->client, $this->logger);
    }

    public function testAddDocumentsRetriesOnPrimaryKeyMismatch(): void
    {
        $indexMock = $this->createMock(Indexes::class);
        $matcher = $this->exactly(2);

        $indexMock->expects($matcher)
            ->method('addDocuments')
            ->willReturnCallback(function () use ($matcher): array {
                if ($matcher->numberOfInvocations() === 1) {
                    throw $this->createApiException(400, 'Index already has a primary key');
                }

                return ['taskUid' => 1];
            });

        $this->client->method('index')->willReturn($indexMock);
        $this->client->expects($this->once())->method('deleteIndex')->with('test_index');
        $this->logger->expects($this->once())->method('warning');

        $this->indexer->addDocuments('test_index', [['id' => 1]]);
    }

    public function testAddDocumentsRethrowsOtherExceptions(): void
    {
        $indexMock = $this->createMock(Indexes::class);
        $indexMock->method('addDocuments')
            ->willThrowException($this->createApiException(500, 'Internal server error'));

        $this->client->method('index')->willReturn($indexMock);

        $this->expectException(ApiException::class);
        $this->indexer->addDocuments('test_index', [['id' => 1]]);
    }

    public function testRemoveDocumentRethrowsOtherExceptions(): void
    {
        $indexMock = $this->createMock(Indexes::class);
        $indexMock->method('deleteDocument')
            ->willThrowException($this->createApiException(500, 'Internal server error'));

        $this->client->method('index')->willReturn($indexMock);

        $this->expectException(ApiException::class);
        $this->indexer->removeDocument('test_index', 1);
    }

    public function testDeleteIndexRethrowsOtherExceptions(): void
    {
        $this->client->method('deleteIndex')
            ->willThrowException($this->createApiException(500, 'Internal server error'));

        $this->expectException(ApiException::class);
        $this->indexer->deleteIndex('test_index');
    }

    private function createApiException(int $httpStatus, string $message): ApiException
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn($httpStatus);
        $response->method('getReasonPhrase')->willReturn($message);

        return new ApiException($response, ['message' => $message]);
    }
}
