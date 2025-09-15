<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use App\Models\Event;
use Illuminate\Support\Facades\Lang;

class HandleInertiaRequests extends Middleware
{
  /**
   * The root template that is loaded on the first page visit.
   *
   * @var string
   */
  protected $rootView = 'app';

  /**
   * Determine the current asset version.
   */
  public function version(Request $request): string|null
  {
    return parent::version($request);
  }

  /**
   * Define the props that are shared by default.
   *
   * @return array<string, mixed>
   */
  public function share(Request $request): array
  {
    $organizationId = config('settings.organization_id');
    setPermissionsTeamId($organizationId);
    $user = $request->user();
    $permissions = $user ? $user->getAllPermissions()->pluck('name') : [];
    $roles = $user ? $user->getRoleNames() : [];
    $events = Event::select('id', 'name', 'code')->orderBy('name')->get();

    return [
      ...parent::share($request),
      'auth' => [
        'user' => fn() => $user ? $user->only(['id', 'name', 'email', 'email_verified_at', 'username']) : null,
        'permissions' => $permissions,
        'roles' => $roles,
      ],
      'flash' => [
        'message' => fn() => $request->session()->get('message'),
        'success' => fn() => $request->session()->get('success'),
        'error' => fn() => $request->session()->get('error'),
      ],
      'menu' => [
        'events' => fn() => $request->user() ? $events : [],
      ],
      'oauthTranslations' => fn() => [
        'Navigation' => Lang::get('oauth.Navigation'),
        'Menu' => Lang::get('oauth.Menu'),
        'Footer' => Lang::get('oauth.Footer'),
      ],
    ];
  }
}
