# Meilisearch Integration

This project uses Meilisearch for fast, relevant search functionality with a LiveComponent-powered dropdown search (no JavaScript required).

## Overview

- **Search Engine**: Meilisearch v1.11
- **Symfony Bundle**: meilisearch/search-bundle
- **UI Component**: Symfony UX Live Component (no JavaScript needed)
- **Indexed Entities**: Post (with locale-specific indexes)
- **Locales**: French (`fr`) and English (`en`)
- **Strategy**: One index per locale (recommended by Meilisearch)

## Architecture

1. **Meilisearch Container**: Runs as a Docker service with persistent volume
2. **Locale-Specific Indexes**: Separate indexes for each locale (`posts_fr`, `posts_en`) with `localizedAttributes` configuration
3. **Post Normalizer**: Transforms Post entities for indexing with locale context (`src/Serializer/Normalizer/PostNormalizer.php`)
4. **SearchBar LiveComponent**: Real-time, locale-aware dropdown search with `locales` parameter for proper tokenization (`src/Twig/Components/SearchBar.php`)
5. **Automatic Indexing**: Message handlers automatically index posts to ALL locale indexes when created/updated (`src/MessageHandler/IndexEntityMessageHandler.php`)
6. **Indexing Command**: Custom command to manually reindex posts per locale (`src/Command/MeilisearchIndexLocaleCommand.php`)

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

**Locale-Specific Indexing (Recommended):**

```bash
docker compose exec php bin/console meilisearch:index:locale
```

This command automatically:

- Indexes posts for all configured locales (fr, en)
- Creates separate indexes for each locale
- Only indexes posts that have translations for each locale
- Skips posts without translations for a given locale

**Alternative: Using Standard Import Commands:**

```bash
# Import French posts
docker compose exec php bin/console meilisearch:import --indices=posts_fr

# Import English posts
docker compose exec php bin/console meilisearch:import --indices=posts_en
```

**Clear and Reimport:**

```bash
# Clear all locale indexes
docker compose exec php bin/console meilisearch:clear --indices=posts_fr
docker compose exec php bin/console meilisearch:clear --indices=posts_en

# Reindex for all locales
docker compose exec php bin/console meilisearch:index:locale
```

## Usage

### Search Dropdown

The search dropdown is available in the header. It uses the `SearchBar` LiveComponent which:

- Updates results in real-time as you type (no JavaScript required!)
- **Automatically searches in the correct locale index** based on current page locale
- Shows up to 5 results
- Displays post title, excerpt, and category
- Links directly to post pages
- Only shows active posts
- **Optimized**: Single API call per search (stores count in memory)

**How Locale-Specific Search Works:**

When a user types a search query:

1. The component detects the current page locale (e.g., `/fr/...` or `/en/...`)
2. It searches only in the appropriate index (`posts_fr` or `posts_en`)
3. Results are returned only in that language
4. No cross-locale content pollution

**Example usage in code:**

```php
// Searches in locale-specific index based on current request locale
$locale = $request->getLocale(); // 'fr' or 'en'
$indexName = "posts_{$locale}";

$results = $searchService->rawSearch(
    Post::class,
    'matcha',
    [
        'limit' => 5,
        'filter' => 'isActive = true',
    ],
    $indexName
);
```

### Manual Indexing

After creating or updating posts, you can manually reindex:

```bash
# Reindex all posts for all locales (recommended)
docker compose exec php bin/console meilisearch:index:locale

# Reindex specific locale
docker compose exec php bin/console meilisearch:import --indices=posts_fr
docker compose exec php bin/console meilisearch:import --indices=posts_en

# Clear and reindex all locales
docker compose exec php bin/console meilisearch:clear --indices=posts_fr
docker compose exec php bin/console meilisearch:clear --indices=posts_en
docker compose exec php bin/console meilisearch:index:locale
```

### Automatic Indexing

**✅ Already Enabled**: Automatic indexing is active via `ModifiedEntityListener` and message handlers.

When a Post is created, updated, or deleted in EasyAdmin:

1. `ModifiedEntityListener` dispatches an async message (`IndexEntityMessage` or `RemoveEntityFromIndexMessage`)
2. Message handlers process the message and index/remove the entity from **ALL locale indexes** (`posts_fr` and `posts_en`)
3. Only posts with translations for a specific locale are indexed in that locale's index

**Files involved:**

- `src/EventSubscriber/Admin/ModifiedEntityListener.php` - Dispatches messages
- `src/MessageHandler/IndexEntityMessageHandler.php` - Indexes to all locales
- `src/MessageHandler/RemoveEntityFromIndexMessageHandler.php` - Removes from all locales

**Note**: Following Meilisearch best practices, each post is automatically indexed to BOTH locale-specific indexes (if translations exist), ensuring proper language-specific tokenization and search relevance.

## Meilisearch Best Practices Implementation

This project follows [Meilisearch's recommended approach for multilingual datasets](https://www.meilisearch.com/docs/learn/indexing/multilingual-datasets#create-a-separate-index-for-each-language-recommended):

### ✅ One Index Per Locale

- **French Index** (`posts_fr`): Contains only French content with French-specific tokenization
- **English Index** (`posts_en`): Contains only English content with English-specific tokenization

### ✅ localizedAttributes Configuration

Each index declares its language using `localizedAttributes` in `config/packages/meilisearch.yaml`:

```yaml
settings:
  localizedAttributes:
    - attributePatterns: ['*']
      locales: ['fr'] # or ['en'] for English index
```

This tells Meilisearch which tokenizer to use for indexing documents.

### ✅ locales Parameter in Searches

Search queries pass the `locales` parameter to ensure queries are tokenized correctly:

```php
$results = $searchService->rawSearch(
    Post::class,
    $query,
    [
        'locales' => ['fr'], // Match the index locale
        // ... other params
    ],
    'posts_fr'
);
```

### Benefits

- **Better tokenization**: French text is tokenized with French rules, English with English rules
- **Improved search relevance**: Queries match documents more accurately
- **Natural data sharding**: Each language is a separate, smaller index
- **Language-specific settings**: Can apply stop words, synonyms, etc. per language

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

The following Post fields are indexed and searchable **per locale**:

**Searchable** (used for full-text search, locale-specific content):

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
- locale

**Sortable** (can be used for sorting):

- createdAt
- readingTime
- cookingTime

**Note**: Content fields (title, excerpt, etc.) contain translations specific to each index's locale.

### Customizing the Normalizer

To change what data is indexed, edit `src/Serializer/Normalizer/PostNormalizer.php`.

**Important**: The normalizer now receives locale context during indexing:

```php
// The target locale is passed in the context
$targetLocale = $context['meilisearch_locale'] ?? $this->locales->getDefaultLocale();
```

Example - adding a new locale-specific field:

```php
return [
    // ... existing fields
    'customField' => $translation->getCustomField(), // Use translation, not base entity
    'locale' => $targetLocale,
];
```

Then update `config/packages/meilisearch.yaml` to include the field in searchableAttributes or filterableAttributes for **both locale indexes** (`posts_fr` and `posts_en`).

## Troubleshooting

### Check Meilisearch Status

```bash
curl http://localhost:7700/health -H "Authorization: Bearer aSampleMasterKey"
```

### Check Index Statistics

```bash
# French index
curl http://localhost:7700/indexes/app_dev_posts_fr/stats \
  -H "Authorization: Bearer aSampleMasterKey" | python3 -m json.tool

# English index
curl http://localhost:7700/indexes/app_dev_posts_en/stats \
  -H "Authorization: Bearer aSampleMasterKey" | python3 -m json.tool
```

### Test Search Query

```bash
# Search in French index
curl -X POST 'http://localhost:7700/indexes/app_dev_posts_fr/search' \
  -H 'Authorization: Bearer aSampleMasterKey' \
  -H 'Content-Type: application/json' \
  --data-binary '{"q":"matcha","limit":5}' | python3 -m json.tool

# Search in English index
curl -X POST 'http://localhost:7700/indexes/app_dev_posts_en/search' \
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
# Delete all locale indexes
docker compose exec php bin/console meilisearch:delete --indices=posts_fr
docker compose exec php bin/console meilisearch:delete --indices=posts_en

# Recreate indexes
docker compose exec php bin/console meilisearch:create

# Reindex all locales
docker compose exec php bin/console meilisearch:index:locale
```

## Additional Resources

- [Meilisearch Documentation](https://www.meilisearch.com/docs)
- [Meilisearch Symfony Bundle Wiki](https://github.com/meilisearch/meilisearch-symfony/wiki)
- [Symfony UX Live Component](https://symfony.com/bundles/ux-live-component/current/index.html)

## Performance Considerations

- **Locale-Specific Indexes**: Each locale has its own index, reducing index size and improving search relevance
- **Memory Usage**: With separate indexes, each index is smaller (better for VPS deployments)
- **Optimized SearchBar**: Single API call per search with in-memory count caching
- Meilisearch is designed for speed and can handle thousands of queries per second
- The Docker volume persists data between container restarts
- Index updates are atomic and don't affect search availability
- Consider enabling `doctrineSubscribedEvents` for automatic indexing in production

### Memory Limits (Production)

For production deployments on VPS, set memory limits in `compose.prod.yaml`:

```yaml
meilisearch:
  deploy:
    resources:
      limits:
        memory: 512M # Adjust based on your content size
      reservations:
        memory: 256M
```

**Estimated Memory Usage:**

- Small dataset (<100 posts): 256-512 MB
- Medium dataset (100-1000 posts): 512 MB - 1 GB
- Large dataset (>1000 posts): 1-2 GB+
