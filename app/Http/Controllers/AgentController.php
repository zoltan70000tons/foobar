<?php
// app/Http/Controllers/AgentController.php
namespace App\Http\Controllers;

use App\Http\Requests\UpdateAvatarBadgeRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

class AgentController extends Controller {
    public function update(UpdateAvatarBadgeRequest $request, User $user): RedirectResponse {
        $detail = $user->detail;
        if (!$detail) {
            $detail = $user->detail()->create([]);
        }
        $avatar = $detail->avatar;
        if (!is_array($avatar)) {
            $avatar = json_decode($avatar ?: '[]', true) ?: [];
        }
        $avatar['image'] = $avatar['image'] ?? null;
        $badge = $request->validated()['badge'] ?? null;
        $avatar['badge'] = $badge;

        $detail->avatar = $avatar;
        $detail->save();

        return back()->with('success', 'Badge colors updated');
    }
}
