# Meilisearch Integration

This project uses Meilisearch for fast, relevant search functionality with a LiveComponent-powered dropdown search (no JavaScript required).

## Overview

- **Search Engine**: Meilisearch v1.11
- **Symfony Bundle**: meilisearch/search-bundle
- **UI Component**: Symfony UX Live Component (no JavaScript needed)
- **Indexed Entities**: Post

## Architecture

1. **Meilisearch Container**: Runs as a Docker service with persistent volume
2. **Post Normalizer**: Transforms Post entities for indexing (`src/Serializer/Normalizer/PostNormalizer.php`)
3. **SearchBar LiveComponent**: Real-time dropdown search without JavaScript (`src/Twig/Components/SearchBar.php`)
4. **Indexing Command**: Custom command to index posts (`src/Command/MeilisearchIndexCommand.php`)

## Setup

### 1. Start Meilisearch Container

```bash
docker compose up -d meilisearch
```

The container will be available at `http://meilisearch:7700` (internal) and `http://localhost:7700` (external).

### 2. Create Index and Configure Settings

```bash
docker compose exec php bin/console meilisearch:create
```

This creates the index with the configured settings from `config/packages/meilisearch.yaml`.

### 3. Import Existing Data

```bash
docker compose exec php bin/console meilisearch:import --indices=posts
```

Or clear and reimport:

```bash
docker compose exec php bin/console meilisearch:clear --indices=posts
docker compose exec php bin/console meilisearch:import --indices=posts
```

## Usage

### Search Dropdown

The search dropdown is available in the header. It uses the `SearchBar` LiveComponent which:

- Updates results in real-time as you type (no JavaScript required!)
- Shows up to 5 results
- Displays post title, excerpt, and category
- Links directly to post pages
- Only shows active posts

**Example usage in code:**

```php
$results = $searchService->search(
    $entityManager,
    Post::class,
    'matcha',
    [
        'limit' => 5,
        'filter' => 'isActive = true',
    ]
);
```

### Manual Indexing

After creating or updating posts, you can manually reindex:

```bash
# Reindex all posts
docker compose exec php bin/console meilisearch:import --indices=posts

# Clear and reindex
docker compose exec php bin/console meilisearch:clear --indices=posts
docker compose exec php bin/console meilisearch:import --indices=posts
```

### Automatic Indexing

The Meilisearch bundle supports automatic indexing via Doctrine events. To enable:

Add the following to your `config/packages/meilisearch.yaml`:

```yaml
meilisearch:
  # ... existing config
  doctrineSubscribedEvents: true
```

This will automatically index posts when they are created, updated, or deleted.

## Configuration

### Environment Variables

Located in `.env`:

```env
# Meilisearch server URL (used by Symfony)
MEILISEARCH_URL=http://meilisearch:7700

# API key for authentication
MEILISEARCH_API_KEY=aSampleMasterKey

# Index prefix (includes environment)
MEILISEARCH_PREFIX=app_${APP_ENV}_

# Docker container configuration
MEILISEARCH_MASTER_KEY=aSampleMasterKey
MEILISEARCH_ENV=development
MEILISEARCH_NO_ANALYTICS=true
MEILISEARCH_PORT=7700
```

**⚠️ Important**: In production, change `MEILISEARCH_MASTER_KEY` to a secure random string!

### Indexed Fields

The following Post fields are indexed and searchable:

**Searchable** (used for full-text search):

- title
- excerpt
- metaDescription
- categoryTitle
- tags

**Filterable** (can be used in filters):

- id
- categoryId
- difficulty
- isActive
- createdAt

**Sortable** (can be used for sorting):

- createdAt
- readingTime
- cookingTime

### Customizing the Normalizer

To change what data is indexed, edit `src/Serializer/Normalizer/PostNormalizer.php`.

Example - adding a new field:

```php
return [
    // ... existing fields
    'customField' => $object->getCustomField(),
];
```

Then update `config/packages/meilisearch.yaml` to include the field in searchableAttributes or filterableAttributes.

## Troubleshooting

### Check Meilisearch Status

```bash
curl http://localhost:7700/health -H "Authorization: Bearer aSampleMasterKey"
```

### Check Index Statistics

```bash
curl http://localhost:7700/indexes/app_dev_posts/stats \
  -H "Authorization: Bearer aSampleMasterKey" | python3 -m json.tool
```

### Test Search Query

```bash
curl -X POST 'http://localhost:7700/indexes/app_dev_posts/search' \
  -H 'Authorization: Bearer aSampleMasterKey' \
  -H 'Content-Type: application/json' \
  --data-binary '{"q":"matcha","limit":5}' | python3 -m json.tool
```

### Clear Cache

If changes aren't reflected:

```bash
docker compose exec php bin/console cache:clear
```

### Rebuild Index

```bash
docker compose exec php bin/console meilisearch:delete --indices=posts
docker compose exec php bin/console meilisearch:create
docker compose exec php bin/console meilisearch:import --indices=posts
```

## Additional Resources

- [Meilisearch Documentation](https://www.meilisearch.com/docs)
- [Meilisearch Symfony Bundle Wiki](https://github.com/meilisearch/meilisearch-symfony/wiki)
- [Symfony UX Live Component](https://symfony.com/bundles/ux-live-component/current/index.html)

## Performance Considerations

- Meilisearch is designed for speed and can handle thousands of queries per second
- The Docker volume persists data between container restarts
- Index updates are atomic and don't affect search availability
- Consider enabling `doctrineSubscribedEvents` for automatic indexing in production
