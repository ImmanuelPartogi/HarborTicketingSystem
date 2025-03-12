<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ferry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * @method \Illuminate\Routing\Controller middleware($middleware, array $options = [])
 */
class FerryController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    /**
     * Display a listing of ferries.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $search = $request->query('search');
        $status = $request->query('status');

        $query = Ferry::query();

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        if ($status) {
            $query->where('status', $status);
        }

        $ferries = $query->orderBy('name')->paginate(10);

        return view('admin.ferries.index', compact('ferries', 'search', 'status'));
    }

    /**
     * Show the form for creating a new ferry.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('admin.ferries.create');
    }

    /**
     * Store a newly created ferry.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:ferries',
            'capacity_passenger' => 'required|integer|min:1',
            'capacity_vehicle_motorcycle' => 'required|integer|min:0',
            'capacity_vehicle_car' => 'required|integer|min:0',
            'capacity_vehicle_bus' => 'required|integer|min:0',
            'capacity_vehicle_truck' => 'required|integer|min:0',
            'status' => 'required|in:ACTIVE,MAINTENANCE,INACTIVE',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
        ]);

        try {
            $ferryData = $request->all();

            // Handle image upload
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('ferries', 'public');
                $ferryData['image'] = $imagePath;
            }

            Ferry::create($ferryData);

            return redirect()->route('admin.ferries.index')
                ->with('success', 'Ferry created successfully.');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Failed to create ferry: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified ferry.
     *
     * @param Ferry $ferry
     * @return \Illuminate\View\View
     */
    public function show(Ferry $ferry)
    {
        // Load schedules for this ferry
        $schedules = $ferry->schedules()
            ->with('route')
            ->orderBy('route_id')
            ->get();

        return view('admin.ferries.show', compact('ferry', 'schedules'));
    }

    /**
     * Show the form for editing the specified ferry.
     *
     * @param Ferry $ferry
     * @return \Illuminate\View\View
     */
    public function edit(Ferry $ferry)
    {
        return view('admin.ferries.edit', compact('ferry'));
    }

    /**
     * Update the specified ferry.
     *
     * @param Request $request
     * @param Ferry $ferry
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Ferry $ferry)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:ferries,name,' . $ferry->id,
            'capacity_passenger' => 'required|integer|min:1',
            'capacity_vehicle_motorcycle' => 'required|integer|min:0',
            'capacity_vehicle_car' => 'required|integer|min:0',
            'capacity_vehicle_bus' => 'required|integer|min:0',
            'capacity_vehicle_truck' => 'required|integer|min:0',
            'status' => 'required|in:ACTIVE,MAINTENANCE,INACTIVE',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
        ]);

        try {
            $ferryData = $request->except(['image']);

            // Handle image upload
            if ($request->hasFile('image')) {
                // Delete old image if exists
                if ($ferry->image) {
                    Storage::disk('public')->delete($ferry->image);
                }

                $imagePath = $request->file('image')->store('ferries', 'public');
                $ferryData['image'] = $imagePath;
            }

            $ferry->update($ferryData);

            return redirect()->route('admin.ferries.index')
                ->with('success', 'Ferry updated successfully.');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Failed to update ferry: ' . $e->getMessage());
        }
    }

    /**
     * Show confirmation page before deleting a ferry.
     *
     * @param Ferry $ferry
     * @return \Illuminate\View\View
     */
    public function delete(Ferry $ferry)
    {
        // Check if the ferry is used in any schedules
        $scheduleCount = $ferry->schedules()->count();

        return view('admin.ferries.delete', compact('ferry', 'scheduleCount'));
    }

    /**
     * Remove the specified ferry.
     *
     * @param Ferry $ferry
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Ferry $ferry)
    {
        try {
            // Check if the ferry is used in any schedules
            if ($ferry->schedules()->count() > 0) {
                return back()->with('error', 'Cannot delete ferry with existing schedules.');
            }

            // Delete image if exists
            if ($ferry->image) {
                Storage::disk('public')->delete($ferry->image);
            }

            $ferry->delete();

            return redirect()->route('admin.ferries.index')
                ->with('success', 'Ferry deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete ferry: ' . $e->getMessage());
        }
    }

    /**
     * Update ferry status.
     *
     * @param Request $request
     * @param Ferry $ferry
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateStatus(Request $request, Ferry $ferry)
    {
        $request->validate([
            'status' => 'required|in:ACTIVE,MAINTENANCE,INACTIVE',
        ]);

        try {
            $ferry->update([
                'status' => $request->status,
            ]);

            return redirect()->route('admin.ferries.show', $ferry)
                ->with('success', 'Ferry status updated to ' . $request->status);
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update ferry status: ' . $e->getMessage());
        }
    }
}
