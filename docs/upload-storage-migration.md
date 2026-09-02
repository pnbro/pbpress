# Upload storage migration

PBPress 7.9.2 keeps public file URLs under `/uploads/{path}` but stores new local files outside the web document root.

## Configuration

The default local upload handler requires `PB_FILE_UPLOAD_STORAGE_PATH`. Set an absolute path or a path relative to `PB_DOCUMENT_PATH` in `pb-config.php`. PBPress returns an initialization error when the value is missing or empty:

```php
define('PB_FILE_UPLOAD_STORAGE_PATH', '../private/uploads');
define('PB_FILE_UPLOAD_LEGACY_FALLBACK', true);
```

PBPress rejects storage paths inside `PB_DOCUMENT_PATH` or the web server `DOCUMENT_ROOT`. If the configured path cannot be created or written, uploads fail instead of falling back to public storage.

## Forward migration

Run the dry-run first. Replace the project path with the absolute path of the target installation.

```bash
/absolute/path/to/php /absolute/project/path/dev/migrate-uploads.php
```

Review every `PLAN` and `CONFLICT` entry, then copy without deleting the source:

```bash
/absolute/path/to/php /absolute/project/path/dev/migrate-uploads.php --apply
```

The tool preserves relative paths, compares file size and SHA-256, skips hidden and temporary upload files, and never overwrites an existing different file.

After application and browser verification, set `PB_FILE_UPLOAD_LEGACY_FALLBACK` to `false`. Move the legacy public directory to a backup outside the document root only after all files pass verification.

## Rollback preparation

Before returning to a pre-7.9.2 core, dry-run the reverse copy:

```bash
/absolute/path/to/php /absolute/project/path/dev/migrate-uploads.php --reverse
```

Then explicitly copy private-only files back to the legacy location:

```bash
/absolute/path/to/php /absolute/project/path/dev/migrate-uploads.php --reverse --apply
```

Reverse mode also refuses deletion and overwriting. Local-to-S3 or S3-to-local migration is not handled by this tool because stored records do not contain per-file backend information.
