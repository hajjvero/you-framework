<?php

namespace YouOrm\Migration;

use YouOrm\Discovery\EntityDiscovery;
use YouOrm\Grammar\DDL\GrammarDDLInterface;
use YouOrm\Schema\SchemaDiff;
use YouOrm\Schema\TableDiff;

/**
 * Class MigrationGenerator
 * Responsible for generating migration SQL from schema differences.
 */
class MigrationGenerator
{
    /**
     * @param GrammarDDLInterface $grammar
     * @param EntityDiscovery $discovery
     */
    public function __construct(
        protected GrammarDDLInterface $grammar,
        protected EntityDiscovery $discovery
    ) {
    }

    /**
     * Generate migration SQL from a SchemaDiff.
     *
     * @param SchemaDiff $diff
     * @return array ['up' => string, 'down' => string]
     */
    public function generateDiff(SchemaDiff $diff): array
    {
        $up = [];
        $down = [];

        // 1. New Tables
        foreach ($diff->newTables as $table) {
            $up[] = $this->grammar->compileCreateTable($table->getName(), $table->getColumns()) . ';';
            $down[] = $this->grammar->compileDropTable($table->getName()) . ';';
        }

        // 2. Changed Tables
        foreach ($diff->changedTables as $tableDiff) {
            $this->generateTableDiffSql($tableDiff, $up, $down);
        }

        // 3. Removed Tables
        // (Optional: usually we don't auto-drop tables for safety, but here we implement it for completeness)
        foreach ($diff->removedTables as $tableName) {
            // We don't have the full table structure for the 'down' part here easily without more info
            // but for now let's just do the 'up' part
            $up[] = $this->grammar->compileDropTable($tableName) . ';';
            // $down would require the full table definition which we might not have in the diff
        }

        return [
            'up' => implode("\n", $up),
            'down' => implode("\n", $down),
        ];
    }

    protected function generateTableDiffSql(TableDiff $tableDiff, array &$up, array &$down): void
    {
        $tableName = $tableDiff->tableName;

        // Added Columns
        foreach ($tableDiff->addedColumns as $column) {
            $up[] = $this->grammar->compileAddColumn($tableName, $column) . ';';
            $down[] = $this->grammar->compileDropColumn($tableName, $column->getName()) . ';';
        }

        // Changed Columns
        foreach ($tableDiff->changedColumns as $columnDiff) {
            $up[] = $this->grammar->compileModifyColumn($tableName, $columnDiff->oldColumn, $columnDiff->newColumn) . ';';
            $down[] = $this->grammar->compileModifyColumn($tableName, $columnDiff->newColumn, $columnDiff->oldColumn) . ';';
        }

        // Removed Columns
        foreach ($tableDiff->removedColumns as $columnName) {
            $up[] = $this->grammar->compileDropColumn($tableName, $columnName) . ';';
            // $down would require the old column definition
        }
    }

}
