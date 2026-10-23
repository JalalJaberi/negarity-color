# Release ZIP

## Versioning

The root `VERSION` file records the project version: currently `1.0.0`.
For each release, update `VERSION` and the version mentioned in `README.md`,
commit and push those changes together with the release workflow, then publish
a GitHub Release with the matching tag (`v1.0.0` for this release) targeting that
commit. GitHub can create the tag when you publish the release.

Composer derives its version from Git tags; keep the `version` field absent
from `composer.json`. The ZIP includes `VERSION` so an extracted package can be
identified without Git metadata.

For the first release:

1. Commit and push the workflow, packager, documentation, and `VERSION` file.
2. Open the repository's **Releases → Draft a new release** page on GitHub.
3. Create tag **`v1.0.0`**, targeting the commit you pushed, and publish the release.
4. Wait for **Actions → Release runtime ZIP** to succeed.
5. Download **`negarity-color-v1.0.0.zip`** from the release's **Assets**.

## Packaging

Publish a GitHub Release (including a prerelease) to build and attach
`<repository>-<tag>.zip`, for example `negarity-color-v1.2.3.zip`.
The workflow must be committed before creating the release tag. A tag push alone
does not publish an asset. To rebuild an existing release, use **Actions → Release
runtime ZIP → Run workflow** and enter its tag; this replaces the same named ZIP.

The ZIP has one stable `negarity-color/` directory. It contains an explicit runtime
file list, required assets and any license files present. Tests, docs, examples,
agent configuration, Git metadata, source maps and build tools are excluded.
The packager fails if required files or block metadata references are missing.

Composer installs locked production dependencies with an optimized autoloader;
`vendor/` is included. PHP and required system extensions remain host requirements.
For direct embedding, require `<package>/vendor/autoload.php`. The Composer manifest
and lockfile are included for consumers integrating through Composer.

For a local package after installing dependencies/building assets:

```sh
python3 .github/package-release.py v1.2.3 negarity-color
```

Output: `release-assets/`. GitHub Actions needs no extra secret for uploading;
it uses the repository token with `contents: write`.
