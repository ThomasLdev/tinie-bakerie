<?php

declare(strict_types=1);

namespace App\Doctrine;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Tools\Event\GenerateSchemaEventArgs;
use Doctrine\ORM\Tools\ToolEvents;

/**
 * Adds PostgreSQL-specific GIN trigram indexes to the schema.
 *
 * Doctrine ORM cannot represent GIN indexes with custom operator classes,
 * so we manually add them here to prevent schema:update from dropping them.
 */
#[AsDoctrineListener(event: ToolEvents::postGenerateSchema)]
final class TrigramIndexSchemaSubscriber
{
    public function postGenerateSchema(GenerateSchemaEventArgs $args): void
    {
        $schema = $args->getSchema();

        if (!$schema->hasTable('post_translation')) {
            return;
        }

        $table = $schema->getTable('post_translation');

        // Add the trigram index if it doesn't exist in the schema
        // This prevents Doctrine from trying to drop it
        if (!$table->hasIndex('post_translation_title_trgm_idx')) {
            $table->addIndex(
                ['title'],
                'post_translation_title_trgm_idx',
                options: ['comment' => 'GIN trigram index managed via migration'],
            );
        }
    }
}
