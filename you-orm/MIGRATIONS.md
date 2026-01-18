# YouORM Migrations

YouORM provides a robust migration system to manage your database schema evolution. It allows you to generate migration files based on changes in your entities and execute them via the command line.

## Prerequisites

Ensure your database connection and paths are correctly configured in your project configuration (usually in `config/database.php` or `.env`):

```php
// Example configuration in config/database.php
return [
    'driver' => env('DB_DRIVER', 'mysql'),
    'migrations_path' => 'migrations', // Path relative to project root
    'migrations_table' => 'migrations', // Table to track executed migrations
    'entities_path' => 'src/Entity', // Path where entities are located
];
```

## Available Commands

The migration system is integrated with `YouConsole`. You can access it using the `bin/console` script.

### 1. Generating a Migration

The `make:migration` command compares your current database schema with the schema defined in your Entity classes (`#[Table]`, `#[Column]`, etc.) and generates a PHP migration file.

```bash
  php you make:migration
```

This will create a new file in your configured `migrations_path` (e.g., `migrations/Version_2026_01_18_123456_789012.php`).

### 2. Running Migrations

To apply all pending migrations to your database, use the `orm:migrate` command:

```bash
  php you orm:migrate
```

### 3. Rolling Back Migrations

To undo the last executed migration, use the `--down` (or `-d`) option:

```bash
  php you orm:migrate --down
```

### 4. Migrating to a Specific Version

You can migrate to a specific version by providing the version identifier (the filename without `.php`):

```bash
# Migrate up to a specific version
 php you orm:migrate Version_2026_01_18_123456_789012 --up

# Roll back to a specific version
  php you orm:migrate Version_2026_01_18_123456_789012 --down
```

## Defining Entities

Entities are simple PHP classes mapped to database tables using PHP 8.1+ attributes.

### Basic Entity Example

```php
<?php

namespace App\Entity;

use YouOrm\Schema\Attribute\Table;
use YouOrm\Schema\Attribute\Column;
use YouOrm\Schema\Type\ColumnType;

#[Table(name: 'users')]
class User
{
    #[Column(name: 'id', type: ColumnType::INTEGER, primaryKey: true, autoIncrement: true)]
    private ?int $id = null;

    #[Column(name: 'username', type: ColumnType::STRING, length: 100, unique: true)]
    private string $username;

    #[Column(name: 'email', type: ColumnType::STRING, nullable: false)]
    private string $email;

    #[Column(name: 'bio', type: ColumnType::TEXT, nullable: true)]
    private ?string $bio = null;

    #[Column(name: 'created_at', type: ColumnType::DATETIME)]
    private \DateTime $createdAt;

    // Getters and Setters...
}
```

### Available Column Types

You can find all available types in `YouOrm\Schema\Type\ColumnType`:
- **Numeric**: `SMALLINT`, `INTEGER`, `BIGINT`, `DECIMAL`, `FLOAT`
- **String**: `STRING` (VARCHAR), `TEXT`, `UUID`
- **Boolean**: `BOOLEAN`
- **Temporal**: `DATE`, `DATETIME`, `TIME`
- **Other**: `JSON`, `ARRAY`, `BLOB`

## Declaring Relations

YouORM supports standard database relationships: **ManyToOne**, **OneToMany**, and **ManyToMany**.

### ManyToOne and OneToMany (One-To-Many)

This is the most common relationship. For example, a `Post` belongs to one `User`, and a `User` can have many `Posts`.

**Post Entity (The Owning Side):**
```php
#[ManyToOne(targetEntity: User::class, inversedBy: 'posts')]
#[JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
private User $user;
```

**User Entity (The Inverse Side):**
```php
#[OneToMany(targetEntity: Post::class, mappedBy: 'user')]
private array $posts = [];
```

### ManyToMany (Many-To-Many)

For example, a `Post` can have many `Tags`, and a `Tag` can be assigned to many `Posts`.

**Post Entity:**
```php
#[ManyToMany(targetEntity: Tag::class, inversedBy: 'posts')]
#[JoinTable(name: 'posts_tags')]
#[JoinColumn(name: 'post_id', referencedColumnName: 'id')]
// Inverse join column is deduced or can be specified in JoinTable
private array $tags = [];
```

**Tag Entity:**
```php
#[ManyToMany(targetEntity: Post::class, mappedBy: 'tags')]
private array $posts = [];
```

## Migration File Structure

A generated migration file looks like this:

```php
<?php

use YouOrm\Migration\AbstractMigration;

class Version_2026_01_18_123456_789012 extends AbstractMigration
{
    /**
     * Applied changes
     */
    public function up(): void
    {
        $this->execute("CREATE TABLE users (id INT AUTO_INCREMENT PRIMARY KEY, username VARCHAR(255) NOT NULL);");
    }

    /**
     * Revert changes
     */
    public function down(): void
    {
        $this->execute("DROP TABLE users;");
    }
}
```

- **`up()`**: Contains the SQL to apply the changes.
- **`down()`**: Contains the SQL to revert the changes made in `up()`.
- **`$this->execute(string $sql)`**: A helper method to run SQL statements.

## Best Practices

- Always review the generated migration file before running it.
- Never manually modify the `migrations` table in your database unless you know exactly what you are doing.
- Commit your migration files to your version control system (e.g., Git).
