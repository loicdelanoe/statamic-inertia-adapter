<?php

namespace StatamicInertiaAdapter\StatamicInertiaAdapter\Support;

use Statamic\Facades\Entry;
use Statamic\Facades\GlobalSet;
use Statamic\Facades\Nav;
use Statamic\Facades\Site;

class SharedData
{
    public static function all(): array
    {
        return array_merge([
            'navigations' => fn () => self::navigations(),
            'globals' => fn () => self::globals(),
            'sites' => fn () => self::sitesWithLocalizedUrls(),
        ]);
    }

    private static function navigations(): array
    {
        return Nav::all()->mapWithKeys(function ($nav) {
            $tree = $nav->trees()->get(Site::current()->handle())->tree();

            $entryIds = collect($tree)->pluck('entry')->toArray();

            $entries = Entry::whereInId($entryIds);

            $navItem = [];

            foreach ($entries as $entry) {
                $navItem[] = [
                    'id' => $entry->id(),
                    'title' => $entry->title,
                    'url' => $entry->url(),
                ];
            }

            return [$nav->handle => $navItem];
        })->toArray();
    }

    private static function globals(): array
    {
        $globals = [];

        foreach (GlobalSet::all() as $globalSet) {
            $handle = $globalSet->handle();
            $localized = $globalSet->in('default');
            $globals[$handle] = $localized ? $localized->data()->all() : [];
        }

        return $globals;
    }

    private static function sitesWithLocalizedUrls(): array
    {
        $page = request()->attributes->get('page');

        $sites = Site::all();

        $sitesWithUrls = $sites->map(function ($site) use ($page) {
            $entry = $page->entry()->in($site->handle());

            $siteArray = $site->toArray();

            $siteArray['related_page'] = $entry->url();

            return $siteArray;
        })->toArray();

        return $sitesWithUrls;
    }
}
