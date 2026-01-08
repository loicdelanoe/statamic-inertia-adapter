<?php

namespace StatamicInertiaAdapter\StatamicInertiaAdapter\Http\Middleware;

use Closure;
use Facades\Statamic\CP\LivePreview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Statamic\Entries\Entry;
use Statamic\Structures\Page;

class StatamicInertiaAdapter
{
    /**
     * Return an Inertia response containing the Statamic data.
     *
     * @return \Inertia\Response|mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $path = $this->normalizePath($request->path());

        $page = $request->statamicToken()
            ? LivePreview::item($request->statamicToken())
            : Entry::findByUri($path);

        if ($this->shouldSkipRequest($page)) {
            return $next($request);
        }

        $pageContent = $page->toAugmentedCollection();

        $request->attributes->set('page', $page);

        return Inertia::render(
            Str::studly($page->template()),
            [
                'data' => $pageContent,
                'layout' => Str::studly($page->layout()),
            ]
        );
    }

    /**
     * Build the path for the component based on Pages Blueprint Name
     */
    protected function buildComponentPath($entry): string
    {
        return $entry->blueprint['title'];
    }

    /**
     * Normalize the given path.
     */
    protected function normalizePath(string $path): string
    {
        if ($path === '/' || $path === '') {
            return '/';
        }

        return Str::start($path, '/');
    }

    protected function isInvalidPage($page): bool
    {
        return ! ($page instanceof Page || $page instanceof Entry);
    }

    protected function isUnauthorized($page): bool
    {
        return ! $page->published() && ! Auth::check();
    }

    protected function shouldSkipRequest($page): bool
    {
        return $this->isInvalidPage($page) || $this->isUnauthorized($page);
    }
}
