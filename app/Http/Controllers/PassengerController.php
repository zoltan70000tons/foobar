<?

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Passenger;
use App\Models\Booking;
use App\Models\User;

class PassengerController extends Controller
{
    public function addPassenger(Request $request)
    {
        $validated = $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'user_id' => 'nullable|exists:users,id',
            'full_name' => 'required|string',
            'email' => 'required|email|unique:passengers,email',
        ]);

        if ($validated['user_id']) {
            $conflict = Passenger::where('user_id', $validated['user_id'])->exists();
            if ($conflict) {
                return response()->json(['error' => 'User already assigned to another booking'], 400);
            }
        }

        $passenger = Passenger::create([
            'booking_id' => $validated['booking_id'],
            'user_id' => $validated['user_id'] ?? null,
            'full_name' => $validated['full_name'],
            'email' => $validated['email'],
        ]);

        return response()->json($passenger, 201);
    }

    public function editPassenger(Request $request, $id)
    {
        $passenger = Passenger::findOrFail($id);

        $validated = $request->validate([
            'full_name' => 'string',
            'email' => 'email|unique:passengers,email,' . $id,
            'phone' => 'nullable|string',
        ]);

        $passenger->update($validated);

        return response()->json($passenger);
    }

    public function validatePassenger(Request $request)
    {
        $validated = $request->validate([
            'email' => 'nullable|email',
            'user_id' => 'nullable|exists:users,id',
        ]);

        $conflicts = [];
        
        if (isset($validated['email'])) {
            $emailConflict = Passenger::where('email', $validated['email'])->exists();
            if ($emailConflict) {
                $conflicts[] = 'Email already registered as a passenger';
            }
        }

        if (isset($validated['user_id'])) {
            $userConflict = Passenger::where('user_id', $validated['user_id'])->exists();
            if ($userConflict) {
                $conflicts[] = 'User is already assigned to another booking';
            }
        }

        if (!empty($conflicts)) {
            return response()->json(['conflicts' => $conflicts], 400);
        }

        return response()->json(['message' => 'No conflicts found']);
    }

    public function search(Request $request)
    {
        $query = $request->input('query');
        
        if (!$query) {
            return response()->json(['error' => 'Query is required'], 400);
        }

        $users = User::where('email', 'LIKE', "%{$query}%")
            ->select('id', 'email') 
            ->get();

        return response()->json($users);
    }
}
