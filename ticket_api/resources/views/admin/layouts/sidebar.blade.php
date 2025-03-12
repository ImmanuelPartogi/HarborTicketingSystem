<nav class="mt-2 px-2 space-y-1">
    <!-- Dashboard -->
    <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }} group flex items-center px-2 py-2 text-sm font-medium rounded-md">
        <i class="fas fa-tachometer-alt mr-3 h-4 w-4"></i>
        Dashboard
    </a>

    <!-- Users -->
    <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }} group flex items-center px-2 py-2 text-sm font-medium rounded-md">
        <i class="fas fa-users mr-3 h-4 w-4"></i>
        Users
    </a>

    <!-- Ferries -->
    <a href="{{ route('admin.ferries.index') }}" class="{{ request()->routeIs('admin.ferries.*') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }} group flex items-center px-2 py-2 text-sm font-medium rounded-md">
        <i class="fas fa-ship mr-3 h-4 w-4"></i>
        Ferries
    </a>

    <!-- Routes -->
    <a href="{{ route('admin.routes.index') }}" class="{{ request()->routeIs('admin.routes.*') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }} group flex items-center px-2 py-2 text-sm font-medium rounded-md">
        <i class="fas fa-route mr-3 h-4 w-4"></i>
        Routes
    </a>

    <!-- Schedules -->
    <a href="{{ route('admin.schedules.index') }}" class="{{ request()->routeIs('admin.schedules.*') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }} group flex items-center px-2 py-2 text-sm font-medium rounded-md">
        <i class="fas fa-calendar-alt mr-3 h-4 w-4"></i>
        Schedules
    </a>

    <!-- Bookings -->
    <a href="{{ route('admin.bookings.index') }}" class="{{ request()->routeIs('admin.bookings.*') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }} group flex items-center px-2 py-2 text-sm font-medium rounded-md">
        <i class="fas fa-ticket-alt mr-3 h-4 w-4"></i>
        Bookings
    </a>

    <!-- Reports Dropdown -->
    <div x-data="{ open: {{ request()->routeIs('admin.reports.*') ? 'true' : 'false' }} }">
        <button @click="open = !open" type="button" class="text-gray-300 hover:bg-gray-700 hover:text-white group w-full flex items-center px-2 py-2 text-sm font-medium rounded-md">
            <i class="fas fa-chart-bar mr-3 h-4 w-4"></i>
            <span class="flex-1">Reports</span>
            <svg class="text-gray-300 ml-3 flex-shrink-0 h-5 w-5 transform transition-colors" x-bind:class="{'rotate-90': open}" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
            </svg>
        </button>
        <div x-show="open" class="space-y-1">
            <a href="{{ route('admin.reports.daily') }}" class="{{ request()->routeIs('admin.reports.daily') ? 'bg-gray-800 text-white' : 'text-gray-400 hover:text-white' }} group flex items-center pl-10 pr-2 py-2 text-sm font-medium rounded-md">
                Daily Report
            </a>
            <a href="{{ route('admin.reports.monthly') }}" class="{{ request()->routeIs('admin.reports.monthly') ? 'bg-gray-800 text-white' : 'text-gray-400 hover:text-white' }} group flex items-center pl-10 pr-2 py-2 text-sm font-medium rounded-md">
                Monthly Report
            </a>
            <a href="{{ route('admin.reports.routes') }}" class="{{ request()->routeIs('admin.reports.routes') ? 'bg-gray-800 text-white' : 'text-gray-400 hover:text-white' }} group flex items-center pl-10 pr-2 py-2 text-sm font-medium rounded-md">
                Route Report
            </a>
            <a href="{{ route('admin.reports.occupancy') }}" class="{{ request()->routeIs('admin.reports.occupancy') ? 'bg-gray-800 text-white' : 'text-gray-400 hover:text-white' }} group flex items-center pl-10 pr-2 py-2 text-sm font-medium rounded-md">
                Occupancy Report
            </a>
        </div>
    </div>
</nav>
