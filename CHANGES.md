#### [Version 0.2.1 Discontinued](https://github.com/ForallFramework/loader.package/tree/0.2.1-dc)
_29-Aug-2013_

* This package has once again been discontinued, as the Core package now does all it was good for.

#### [Version 0.2.1 Beta](https://github.com/ForallFramework/loader.package/tree/0.2.1-beta)
_20-Aug-2013_

* Implemented some logging here and there.
* Removed trailing white-space.

#### [Version 0.2.0 Beta](https://github.com/ForallFramework/loader.package/tree/0.2.0-beta)
_15-June-2013_

* Converted to composer.
  - Moved files to subdirectories.
  - Added `composer.json` and removed old json files.
  - Removed auto-loading of classes.
* This package has been re-purposed.
  - The loader now merely extends the very rudimentary loading system provided by the core
    package to allow for more control over the order in which the main.php code is executed.
  - Added AbstractLoader to provide an interface that can be extended in order to hook
    into the entry point loading.
  - Complete re-factoring of the Loader class, including changes to work with core 0.4.0.

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
