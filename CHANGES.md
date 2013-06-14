#### [Version 0.2.0 Beta](https://github.com/ForallFramework/loader.package/tree/0.2.0-beta)
_?-June-2013_

* This package has been re-purposed as an extension for composer that will enable lazy-
  loading of includes, handling load-order based on dependencies and accessing package's
  entry points.
  - Everything was converted to a composer format.
    * Moved files to subdirectories.
    * Added `composer.json` and removed old json files.
  - Removed auto-loading of classes.
  - Added Composer hooks to store of loader specific settings.


#### [Version 0.1.2 Beta](https://github.com/ForallFramework/loader.package/tree/0.1.2-beta)
_4-June-2013_

* Development discontinued.
* Bug fixes:
  - Includes don't use the right path to load from.
  - Initializing a package gives errors.

#### [Version 0.1.1 Beta](https://github.com/ForallFramework/loader.package/tree/0.1.1-beta)
_30-May-2013_

* Removed @version tags from files.
* Added the missing `loader.json` file for self-loading.
* Moved the sourceDirectory data from `forall.json` to `loader.json`.
* Fixed an issue where packages were never marked as loaded, and kept re-initializing and loading.

#### [Version 0.1.0 Beta](https://github.com/ForallFramework/loader.package/tree/0.1.0-beta)
_28-May-2013_

* First recorded version.
