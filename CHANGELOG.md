## HEAD (Unreleased)
* Update plupload to 2.1.8 trumbowyg to latest beta 8
* Configuration for rating parser moved to config_rating_parser.xml
* Fixed wrong URL for rating image in updateRateImg()
* Remove Jwplayer as it's not support video send via PHP script.
* Improve load speed for movie and person info page.
* Added microdata for movie info.
* Fixed alias processing for movies and persons.
* Fixed wrong redirect while saving if some fields was empty.
* Move scripts from <body> to the <head> on frontend.
* Removed JComments from movies and premieres list page.
* Some improvements for reviews form and editors.
* Fixed links in email send after add review.
* Fixed error with wrong release info about movie.
* Fixed error with kahtml not found when filter applied.
* Improve 'Edit trailer' layout.
* Fixed error in batch modal layout for movies.
* Update language files.
* Added support for SEF urls.
* Fixed rottentomatoes rating parser.
* Fixed wrong string build for search in database in KADatabaseHelper::transformOperands()
* Media content(like images, video, etc.) now can be restricted to numberof user sessions and speed.
* Update plupload to 2.1.8
* Update Select2 to 3.5.4
* Update BxSlider to 4.2.5
* Update jquery countdown to 2.0.2
* Update jquery more to 2.0.1
* Fixed search in grids for awards, premieres, releases in backend for movies and persons.
* Added 'Clear' button for search forms.
* Improve usability of updater for ratings from remote sites.
* Fixed errors when parsin ratings from remote sites(e.g. rottentomatoes).
* Fixed 'Save and create' task in 'country edit' template.
* New layouts.
* Change JString into String in classes.
* Removed unnecessary overrides in KAPagination class.
* Fixed wrong redirects from filters in Releases, Premieres.
* Fixed bug with tags in advanced search.
* Fixed bugs in global model.
* Fixed problem with date/time picker, when user cannot change value because input was overlapped in some situations.
* Change JString into StringHelper in all classes.
* Change JRegistry into Registry in all classes.
* Remove unused variables.
* Improve display title on some views for some materials.
* Fixed errors with undefined variables and array keys in autocomplete fields when the ID of the item isn't defined.
* Update VideoJS to 5.8.7
* Update Mediaelement.js to 2.20.1
* Fixed bug with undefined variale which may have unexpected results when the user try to delete person from 'cast & crew' table.
* Added "Links to buy".
* Added posibility to edit a music genres.
* SQL-dump was updated.
* Other fixes

## 3.0.6
* Closed https://github.com/Globulopolis/kinoarhiv/issues/41
* Fixed wrong sorting in awards table in person edit layout.
* Fixed wrong sorting in premieres/releases/awards table in movie edit layout.
* Fixed a bug when assets table still locked after removing person or movie.
* Fixed error with wrong columns on trailer save.
* Add edit video data layout.
* Update videoJS to 4.12.5
* Fixed error with tags mapping.
* Closed https://github.com/Globulopolis/kinoarhiv/issues/47
* Closed https://github.com/Globulopolis/kinoarhiv/issues/46
* Removed spinner from rating fields in advanced search.
* Closed https://github.com/Globulopolis/kinoarhiv/issues/42
* Added an option to search by metacritic rating.
* Added an options to disable ratings. User rating, movie rating from other sites.
* Added list limit for global lists. E.g. movies list or premieres list.
* Added checking for ffmpeg/ffprobe.
* Added some new filters for releases, premieres.
* Improved batch process.
* Fixed errors in batch process.
* Fixed error with empty video resolution.
* Fixed wrong aspect ratio for jwplayer and flowplayer.
* Add language for premieres. Now premieres language matches with content language.
* Add language for releases. Now releases language matches with content language.
* Fixed bug with sql dump for update
* Closed https://github.com/Globulopolis/kinoarhiv/issues/40
* Closed https://github.com/Globulopolis/kinoarhiv/issues/39
* Closed https://github.com/Globulopolis/kinoarhiv/issues/34
* Closed https://github.com/Globulopolis/kinoarhiv/issues/30
* Closed https://github.com/Globulopolis/kinoarhiv/issues/37
* Closed https://github.com/Globulopolis/kinoarhiv/issues/38
* Update jQueryUI to 1.11.4
* Fixed https://github.com/Globulopolis/kinoarhiv/issues/27
* Fixed https://github.com/Globulopolis/kinoarhiv/issues/28

## 3.0.5
* Closed https://github.com/Globulopolis/kinoarhiv/issues/21
* Fixed https://github.com/Globulopolis/kinoarhiv/issues/25
* Fixed double slash for poster image on feed page
* Update mediaelementjs to 2.16.4. Remove unnecessary files.
* Add changelog file.
* Add license file.
* Fixed JS error when Cancel batch dialog.
* Custom global method to load language file for JS scripts.
* Update VideoJS to 4.11.4
* Add language files for jQueryUI datepicker.
* Update jqueryUI to 1.11.3.
* Fix for "Dubbing actors" title when data is empty.
* Language updates and fixes.
* Fixed https://github.com/Globulopolis/kinoarhiv/issues/17
* Updated sql dump.
* Fixed installer script
* Fixed JS language files capitalization.
* Added translation loader for Countdown timer.
* Implemented https://github.com/Globulopolis/kinoarhiv/issues/18
* Fixed error in backend with jQUI Dialog.
* Fixed wrong class for tooltip in frontend.
* Update FFMPEG binaries
* Implemented https://github.com/Globulopolis/kinoarhiv/issues/16
* Fixed https://github.com/Globulopolis/kinoarhiv/issues/22
* Add custom pagination for "persons" list on frontpage.
* Fixed https://github.com/Globulopolis/kinoarhiv/issues/20
* Fixed wrong page title if wrong Itemid was set. Fixed in premieres, movies, search.
* Fixed wrong link in genres list.
* Fixed error with wrong filter state in backend for names and movies lists.
* Fixed https://github.com/Globulopolis/kinoarhiv/issues/23
* Fixed bug with wrong sql-query in awards saving.
* Removed all "store in session" actions in controllers.
* New sql dump for awards.
* New sql dump for vendors(distributors).
* Fixed attribute values in fields with ajax queries.
* Fixed https://github.com/Globulopolis/kinoarhiv/issues/10
* Update pagination class to latest version(for J3.4).
* Fixed component version number.
* Fixed a bug with ratings.
* Fixed wrong link for profile tabs.
* Update Colorbox to 1.5.14.

## 3.0.4
* Sometimes $menu isn't JRegistry object for movie or name.
* Added support for older PHP(version 5.3)
* Fixed a bug with calling element index.
* Update rottentomatoes rating parser.
* Delete QueryPath library.
* Update select2.
* Update VideoJS to latest version. Now support chapters.
* Fixed undefined variable bug in reviews.

## 3.0.3
* Beta release. No changelog.
