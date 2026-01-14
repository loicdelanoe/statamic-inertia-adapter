# Statamic Inertia.js adapter

> ⚠️ **Warning:** This project is currently in development. APIs and features may change without notice.

# Introduction

This adapter allows you to use Inertia.js with Statamic.

# Installation

To setup the package in an existing Statamic project, run the following command:

```
composer require loicdelanoe/statamic-inertia-adapter
```

After that, you will need to set up Inertia.js. For guidance, follow the [official documentation](https://inertiajs.com/installation).

If you prefer a ready-to-go setup, you can use the [Statamic Inertia.js starter kit](https://github.com/loicdelanoe/statamic-inertia-starter). Currently, it only supports Vue.js, but it should be easy to adapt for React or Svelte.

# Usage

## Layouts and Templates

This package supports Layouts and Templates as defined in Statamic.
- Layouts must be placed in `resources/js/layouts`
- Templates must be placed in `resources/js/pages`

**⚠️ Important:**

For Statamic to recognize available layouts and templates, you must also create corresponding (empty) view files in the default `resources/views` directory.

For example, if you create a template at:

```
resources/js/pages/Home.vue
```

You must also create one of the following files:

```
resources/views/home.antlers.html
```
or
```
resources/views/home.blade.php
```
These files can remain empty, they are only needed to register the template with Statamic.

## Shared Data

The package exposes shared data using Inertia.js. For more information about Inertia’s shared data system, see the [official documentation](https://inertiajs.com/docs/v2/data-props/shared-data).

The following data is shared globally:

- **Navigations**: all menus defined in your CMS are available in the frontend.
- **Globals**: global fields or settings accessible across pages.

- **Multisite support**: if you are using Statamic Pro’s multisite feature, a variable is available to help implement a site switcher.
    - To make the site switcher more accurate, a `related_page` key is also provided. It corresponds to the equivalent page in other sites/languages, allowing you to link users to the correct localized page directly.
