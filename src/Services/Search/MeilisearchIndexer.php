<?php

declare(strict_types=1);

namespace App\Services\Search;

use Meilisearch\Client;
use Meilisearch\Exceptions\ApiException;
use Psr\Log\LoggerInterface;

final readonly class MeilisearchIndexer
{
    private const string PRIMARY_KEY = 'id';

    public function __construct(
        private Client $client,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * @param array<string, mixed> $document
     */
    public function addDocument(string $indexName, array $document): void
    {
        $this->addDocuments($indexName, [$document]);
    }

    /**
     * @param array<int, array<string, mixed>> $documents
     */
    public function addDocuments(string $indexName, array $documents): void
    {
        try {
            $this->client->index($indexName)->addDocuments($documents, self::PRIMARY_KEY);
        } catch (ApiException $e) {
            if ($this->isPrimaryKeyMismatch($e)) {
                $this->logger->warning('Index has incompatible primary key, recreating index', [
                    'index' => $indexName,
                ]);
                $this->deleteIndex($indexName);
                $this->client->index($indexName)->addDocuments($documents, self::PRIMARY_KEY);

                return;
            }

            throw $e;
        }
    }

    public function removeDocument(string $indexName, int|string $documentId): void
    {
        try {
            $this->client->index($indexName)->deleteDocument($documentId);
        } catch (ApiException $e) {
            if ($e->httpStatus === 404) {
                return;
            }

            throw $e;
        }
    }

    public function deleteIndex(string $indexName): void
    {
        try {
            $this->client->deleteIndex($indexName);
        } catch (ApiException $e) {
            if ($e->httpStatus === 404) {
                return;
            }

            throw $e;
        }
    }

    private function isPrimaryKeyMismatch(ApiException $e): bool
    {
        return $e->httpStatus === 400 && str_contains($e->getMessage(), 'primary key');
    }
}
