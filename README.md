# Cockpit Image Upload

A Cockpit addon for authenticated PNG, JPEG and WebP uploads into explicitly allowed asset folders. No frontend or database dependencies.

## Install

- Copy this repository into Cockpit as `addons/cockpit-image-upload` and reload Cockpit/PHP-FPM if needed.
- Create destination folders in Cockpit Assets. Find each folder's Cockpit ID using Cockpit's administration or database tooling; names and paths are not IDs.
- Add an `image_upload` entry to the existing `config/config.php` return array (do not replace your other configuration):

```php
'image_upload' => [
    'folders' => [
        'events' => 'COCKPIT_FOLDER_ID',
        'other-project' => 'ANOTHER_FOLDER_ID',
    ],
],
```

- Folder aliases are public request values; IDs are configured only on the server. An absent or empty allowlist denies all uploads. The allowlist applies to every key granted `assets/upload`; it does not isolate folders per key.
- Grant the API key's role `assets/upload`. Never grant this permission to the public role. Clients needing to read results also need Cockpit asset read permissions.
- Requires PHP 8.1+, Fileinfo, image inspection, writable Cockpit asset storage, and PHP/proxy body limits above 10 MiB (`post_max_size` greater than `upload_max_filesize`).

## API

- `POST /api/image-upload/upload`, with `api-key` header and multipart fields `folder` (configured alias) and `file` (one image).
- Returns `{ "asset": { ... } }` with the native Cockpit asset, or `{ "asset": { ... }, "reused": true }` for a byte-identical image in the same folder. Deduplication is best-effort and concurrent requests can create duplicates.
- Accepts PNG, JPEG and WebP, up to 10 MiB and 20 megapixels. Content and extension are checked. No arbitrary destinations, URL uploads, deletion or replacement.
- A failed response can follow successful storage. Check Assets before retrying. API keys must remain server-side.

```bash
curl -X POST 'https://your-cockpit.example/api/image-upload/upload' \
  -H "api-key: $COCKPIT_API_KEY" \
  -F 'folder=events' \
  -F 'file=@image.jpg'
```

## Verification

- Run `php -l bootstrap.php`, `php -l api/upload.post.php` and `php tests/routes.php`.
- The isolated route test checks registration, authorization and allowlist guards; it does not exercise real multipart uploads or Cockpit storage. Verify one upload, a duplicate and a rejected folder against a test installation before production use.
