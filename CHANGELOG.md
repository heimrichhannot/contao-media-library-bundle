# Changelog
All notable changes to this project will be documented in this file.

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
