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
use Statamic\Structures\Page;

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
        $request->attributes->set('page', $page);

        return $this->renderPage($page);

    }

    /**
     * Resolve the Statamic page or entry based on the request.
     *
     * If a live preview token is present, return the live preview item.
     * Otherwise, resolve the page by the request URL.
     */
    private function resolvePage(Request $request): Entry|Page|null
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
    private function renderPage(Entry|Page $page): \Inertia\Response
    {
        $template = Str::studly($page->template());
        $layout = Str::studly($page->layout());

        return Inertia::render(
            $template,
            [
                'data' => $page->toAugmentedArray(),
                'layout' => $layout,
            ]
        );
    }

    /**
     * Determine if the given page is invalid.
     *
     * A page is considered invalid if it is not an instance of Page or Entry.
     */
    private function isInvalidPage(mixed $page): bool
    {
        return ! ($page instanceof Page || $page instanceof Entry);
    }

    /**
     * Determine if the current user is unauthorized to view the page.
     *
     * A user is unauthorized if the page is not published and the user is
     * not authenticated.
     */
    private function isUnauthorized(Entry|Page|null $page): bool
    {
        return ! $page->published() && ! Auth::check();
    }

    /**
     * Determine if the middleware should skip processing the request.
     *
     * Skips the request if the page is invalid or the user is unauthorized.
     */
    private function shouldSkipRequest(Entry|Page|null $page): bool
    {
        return $this->isInvalidPage($page) || $this->isUnauthorized($page);
    }
}
