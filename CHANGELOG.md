# Changelog

All notable changes to this project will be documented in this file.

## [1.3.0] - 2023-09-05
- Added: form type for frontend file uploads ([#2])
- Changed: minimum php version is now 7.4 ([#2])
- Fixed: issue in CfgTagMigration ([#2])

## [1.2.0] - 2023-02-15
- Changed: implement encore contracts
- Changed: removed outdated test setup
- Fixed: download links in List item if no option set

## [1.1.0] - 2022-09-20
- Added: copyright wizard ([#1])
- Added: migration for CfgTag v3 (otherwise former tags get lost)
- Fixed: array index issue in php 8

## [1.0.3] - 2022-08-23
- Fixed: warnings with php 8

## [1.0.2] - 2022-05-05
- Fixed: symfony 5 compatibility

## [1.0.1] - 2022-05-05
- Fixed: symfony 5 compatibility

## [1.0.0] - 2022-05-05
- Added: missing license file
- Changed: minimum contao version is now 4.9
- Fixed: symfony 5 compatiblity

## [1.0.0-beta23] - 2021-08-12

- Changed: make createDownloadItems() public

## [1.0.0-beta22] - 2021-08-11

- Added: config option for sanitizing download filenames

## [1.0.0-beta21] - 2021-08-10

- Fixed: image sizes in product archives
- Fixed: massively enhanced performance for tag handling in products

## [1.0.0-beta20] - 2021-07-23

- fixed publish bug

## [1.0.0-beta19] - 2021-07-23

- fixed publish bug

## [1.0.0-beta18] - 2021-07-14

- fixed ProductContainer::deleteTagAssociations to use actual id instead of undoId

## [1.0.0-beta17] - 2021-07-12

- added author field to tl_ml_product

## [1.0.0-beta16] - 2021-04-06

- increase tl_cfg_tag name char length

## [1.0.0-beta15] - 2021-03-31

- removed filecredits (now loosely coupled dep)
- upgraded tags to v3

## [1.0.0-beta14] - 2021-03-04

- added new dca fields to tl_ml_product
- changed file field to binary(16)

## [1.0.0-beta13] - 2021-03-04

- used TranslatorInterface (for Contao 4.9 compatibility)
- fixed ctable is not an array
- made AjaxManager public

## [1.0.0-beta12] - 2021-01-11

- fixes for filecredits

## [1.0.0-beta11] - 2020-09-25

- fixed translation for title of download elements

## [1.0.0-beta10] - 2020-05-08

- changed default download order so that the original file is always first

## [1.0.0-beta9] - 2020-05-08

- added (optional) sorting mode for additional files in a `heimrichhannot/contao-list-bundle`-based download list
- fixed url generation

## [1.0.0-beta8] - 2020-04-30

- made file field unique in order to avoid deletion of image-size-based download elements
- added `BeforeCreateImageDownloadEvent`

## [1.0.0-beta7] - 2020-04-14

- added retrieve of original and all download items in `DefaultDownloadListItem`

## [1.0.0-beta6] - 2020-04-02

- fixed download deletion
- refactored item classes to have correct names
- added download list item
- added originalDownload field to tl_ml_download

## [1.0.0-beta5] - 2020-04-02

- added `additionalFiles` field

## [1.0.0-beta4] - 2020-04-01

- refactoring (DataContainer classes, removed registry since it's unnecessary, autowiring, ...)
- fixed deletion of non original files linked to an image product
- fixed php_cs fixer style

## [1.0.0-beta3] - 2020-03-27

- fixed dc->id

## [1.0.0-beta2] - 2020-03-27

- removed multifileupload dep from composer.json
- fixed type callback

## [1.0.0-beta] - 2020-03-26

- changed file fields from multifileupload to fileTree
- added multilingual support

## [0.11.0] - 2020-03-20

- added id to cfg tags table

## [0.10.0] - 2020-03-12

- added callbacks to handle cfg-tags on delete and submit
- fixed download file path for download action
- removed default licence handling
- added README

## [0.9.0] - 2020-03-03

- changed creating download entity and modifying product entity with `Database`

[#2]: https://github.com/heimrichhannot/contao-media-library-bundle/pull/2