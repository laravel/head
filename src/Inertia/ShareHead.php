<?php

declare(strict_types=1);

namespace Laravel\Head\Inertia;

use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Laravel\Head\HeadManager;
use Symfony\Component\HttpFoundation\Response;

class ShareHead
{
    public function __construct(protected HeadManager $head) {}

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // A plain callable (rather than Inertia::always) keeps the elements
        // off the wire during partial reloads; the client retains the head
        // from the last full visit.
        Inertia::share(
            $this->head->inertiaProp(),
            fn (): array => $this->head->toInertiaElements(),
        );

        return $next($request);
    }
}
