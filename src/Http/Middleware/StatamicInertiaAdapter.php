<?php

namespace StatamicInertiaAdapter\StatamicInertiaAdapter\Http\Middleware;

use Closure;
use Facades\Statamic\CP\LivePreview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Statamic\Entries\Entry;
use Statamic\Facades\Data;
use Statamic\Facades\Site;
use Statamic\Structures\Page;
use Statamic\Taxonomies\LocalizedTerm;
use Statamic\Taxonomies\Taxonomy;

class StatamicInertiaAdapter
{
    /**
     * Handle an incoming request.
     *
     * Resolves the Statamic page based on the request or live preview token,
     * determines if the request should be skipped, and returns an Inertia
     * response rendering the page with its augmented data and layout.
     */
    public function handle(Request $request, Closure $next)
    {
        $page = $this->resolvePage($request);

        if ($this->shouldSkipRequest($page)) {
            return $next($request);
        }

        // Attach the page to the request for later use in shared data, avoiding unnecessary database queries.
        if ($page instanceof Page || $page instanceof Entry) {
            $request->attributes->set('page', $page);
        }

        return $this->renderPage($page);
    }

    /**
     * Resolve the Statamic page or entry based on the request.
     *
     * If a live preview token is present, return the live preview item.
     * Otherwise, resolve the page by the request URL.
     */
    private function resolvePage(Request $request): Entry|Page|Taxonomy|LocalizedTerm|null
    {
        if ($token = $request->statamicToken()) {
            return LivePreview::item($token);
        }

        return Data::findByRequestUrl($request->url());
    }

    /**
     * Render the given page as an Inertia response.
     *
     * Converts the page into an augmented array and passes it to the
     * corresponding Inertia component. Uses the page's template and layout
     * names converted to StudlyCase.
     */
    private function renderPage(Entry|Taxonomy|LocalizedTerm|Page $page): \Inertia\Response
    {
        $template = $this->formatTemplate($page->template(), $page);

        $data = ['layout' => Str::studly($page->layout())];

        if ($page instanceof Taxonomy) {
            $terms = $page->queryTerms()->where('site', Site::current())->get();

            $data['terms'] = $terms;
        }

        if ($page instanceof Page || $page instanceof Entry) {
            $data['data'] = $page->toAugmentedArray();
        } else {
            $data['data'] = $page;
        }

        return Inertia::render($template, $data);
    }

    /**
     * Determine if the given page is invalid.
     *
     * A page is considered invalid if it is not an instance of Page or Entry.
     */
    private function isInvalidPage(mixed $page): bool
    {
        return ! ($page instanceof Page || $page instanceof Entry || $page instanceof Taxonomy || $page instanceof LocalizedTerm);
    }

    /**
     * Determine if the current user is unauthorized to view the page.
     *
     * A user is unauthorized if the page is not published and the user is
     * not authenticated.
     */
    private function isUnauthorized(Entry|Page|Taxonomy|LocalizedTerm|null $page): bool
    {
        if ($page instanceof Page) {
            return ! $page->published() && ! Auth::check();
        }

        return false;
    }

    /**
     * Determine if the middleware should skip processing the request.
     *
     * Skips the request if the page is invalid or the user is unauthorized.
     */
    private function shouldSkipRequest(Entry|Page|Taxonomy|LocalizedTerm|null $page): bool
    {
        return $this->isInvalidPage($page) || $this->isUnauthorized($page);
    }

    private function formatTemplate(string $template, Entry|Page|Taxonomy|LocalizedTerm $page)
    {
        if ($page instanceof Taxonomy || $page instanceof LocalizedTerm) {
            return Str::of($template)
                ->explode('.')
                ->map(fn ($part) => Str::studly($part))
                ->implode('/');
        }

        return Str::of($template)
            ->afterLast('.')
            ->studly()
            ->value();
    }
}
