<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260522120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add outbox event_id unique constraint and dispatched_at for P0 reliability';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE outbox_events ADD dispatched_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_OUTBOX_EVENT_ID ON outbox_events (event_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX UNIQ_OUTBOX_EVENT_ID');
        $this->addSql('ALTER TABLE outbox_events DROP dispatched_at');
    }
}
