# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## v0.3.0 - 2026-11-10

### What's Changed

* Remove security policy in favor of the org-wide default by [@pushpak1300](https://github.com/pushpak1300) in https://github.com/laravel/head/pull/19
* Potential fix for code scanning alert no. 2: Workflow does not contain permissions by [@joetannenbaum](https://github.com/joetannenbaum) in https://github.com/laravel/head/pull/20
* Potential fix for code scanning alert no. 1: Workflow does not contain permissions by [@joetannenbaum](https://github.com/joetannenbaum) in https://github.com/laravel/head/pull/21
* Resolve request scoped head state from the current container by [@MrFallen](https://github.com/MrFallen) in https://github.com/laravel/head/pull/17
* Fall back to the canonical URL for og:url by [@fkrzski](https://github.com/fkrzski) in https://github.com/laravel/head/pull/14
* Add location, image, performer, status etc to Event schema by [@usamamuneerchaudhary](https://github.com/usamamuneerchaudhary) in https://github.com/laravel/head/pull/15
* Keep default Open Graph media replaceable when defaults are defined across several calls by [@MrFallen](https://github.com/MrFallen) in https://github.com/laravel/head/pull/16

### New Contributors

* [@pushpak1300](https://github.com/pushpak1300) made their first contribution in https://github.com/laravel/head/pull/19
* [@MrFallen](https://github.com/MrFallen) made their first contribution in https://github.com/laravel/head/pull/17
* [@fkrzski](https://github.com/fkrzski) made their first contribution in https://github.com/laravel/head/pull/14
* [@usamamuneerchaudhary](https://github.com/usamamuneerchaudhary) made their first contribution in https://github.com/laravel/head/pull/15

**Full Changelog**: https://github.com/laravel/head/compare/v0.2.2...v0.3.0

## v0.2.2 - 2026-08-26

* Add event schema by [@martinbean](https://github.com/martinbean) in https://github.com/laravel/head/pull/13

## v0.2.1 - 2026-08-17

### What's Changed

* Fix Inertia middleware registration for resolved kernels by [@benbjurstrom](https://github.com/benbjurstrom) in https://github.com/laravel/head/pull/12

**Full Changelog**: https://github.com/laravel/head/compare/v0.2.0...v0.2.1

## v0.2.0 - 2026-08-12

### What's Changed

* Support Inertia SSR gateway features by [@benbjurstrom](https://github.com/benbjurstrom) in https://github.com/laravel/head/pull/8
* Fix octane with inertia by [@benbjurstrom](https://github.com/benbjurstrom) in https://github.com/laravel/head/pull/9
* Remove dead use statements in InertiaTest by [@lucasmichot](https://github.com/lucasmichot) in https://github.com/laravel/head/pull/11
* Fix link attribute injection by [@lucasmichot](https://github.com/lucasmichot) in https://github.com/laravel/head/pull/10

### New Contributors

* [@lucasmichot](https://github.com/lucasmichot) made their first contribution in https://github.com/laravel/head/pull/11

**Full Changelog**: https://github.com/laravel/head/compare/v0.1.0...v0.2.0

## v0.1.0 - 2026-07-20

### Initial Release

- Programmatically set `<head />` meta tags directly from PHP
- Set global, overridable, defaults
- `withHead` route macro

## [Unreleased]

- Adds first version
