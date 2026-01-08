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
            'csrf' => fn () => self::csrf(),
            'navigations' => fn () => self::navigations(),
            'globals' => fn () => self::globals(),
            'old' => fn () => self::old(),
        ]);
    }

    protected static function navigations(): array
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

    protected static function globals(): array
    {
        $globals = [];

        foreach (GlobalSet::all() as $globalSet) {
            $handle = $globalSet->handle();
            $localized = $globalSet->in('default');
            $globals[$handle] = $localized ? $localized->data()->all() : [];
        }

        return $globals;
    }

    protected static function old(): array
    {
        return session()->getOldInput();
    }

    protected static function csrf(): string
    {
        if (! app()->bound('session')) {
            return '';
        }

        return csrf_token() ?? '';
    }
}
