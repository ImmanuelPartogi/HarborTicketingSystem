<nav class="space-y-2">
    <!-- Dashboard -->
    <a href="{{ route('admin.dashboard') }}"
       class="nav-item {{ request()->routeIs('admin.dashboard') ? 'bg-primary-800 text-white' : 'text-primary-100 hover:bg-primary-800 hover:text-white' }} group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200">
        <div class="nav-icon mr-3 flex-shrink-0 h-6 w-6 flex items-center justify-center rounded-md bg-primary-800 {{ request()->routeIs('admin.dashboard') ? 'bg-primary-600 text-white' : 'text-primary-300 group-hover:text-white' }} transition-colors duration-200">
            <i class="fas fa-tachometer-alt"></i>
        </div>
        Dashboard
    </a>

    <!-- Users -->
    <a href="{{ route('admin.users.index') }}"
       class="nav-item {{ request()->routeIs('admin.users.*') ? 'bg-primary-800 text-white' : 'text-primary-100 hover:bg-primary-800 hover:text-white' }} group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200">
        <div class="nav-icon mr-3 flex-shrink-0 h-6 w-6 flex items-center justify-center rounded-md bg-primary-800 {{ request()->routeIs('admin.users.*') ? 'bg-primary-600 text-white' : 'text-primary-300 group-hover:text-white' }} transition-colors duration-200">
            <i class="fas fa-users"></i>
        </div>
        Users
        @if(isset($newUsersCount) && $newUsersCount > 0)
        <span class="ml-auto inline-block py-0.5 px-2 text-xs rounded-full bg-primary-600 text-white">
            {{ $newUsersCount }}
        </span>
        @endif
    </a>

    <!-- Ferries -->
    <a href="{{ route('admin.ferries.index') }}"
       class="nav-item {{ request()->routeIs('admin.ferries.*') ? 'bg-primary-800 text-white' : 'text-primary-100 hover:bg-primary-800 hover:text-white' }} group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200">
        <div class="nav-icon mr-3 flex-shrink-0 h-6 w-6 flex items-center justify-center rounded-md bg-primary-800 {{ request()->routeIs('admin.ferries.*') ? 'bg-primary-600 text-white' : 'text-primary-300 group-hover:text-white' }} transition-colors duration-200">
            <i class="fas fa-ship"></i>
        </div>
        Ferries
    </a>

    <!-- Routes -->
    <a href="{{ route('admin.routes.index') }}"
       class="nav-item {{ request()->routeIs('admin.routes.*') ? 'bg-primary-800 text-white' : 'text-primary-100 hover:bg-primary-800 hover:text-white' }} group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200">
        <div class="nav-icon mr-3 flex-shrink-0 h-6 w-6 flex items-center justify-center rounded-md bg-primary-800 {{ request()->routeIs('admin.routes.*') ? 'bg-primary-600 text-white' : 'text-primary-300 group-hover:text-white' }} transition-colors duration-200">
            <i class="fas fa-route"></i>
        </div>
        Routes
    </a>

    <!-- Schedules -->
    <a href="{{ route('admin.schedules.index') }}"
       class="nav-item {{ request()->routeIs('admin.schedules.*') ? 'bg-primary-800 text-white' : 'text-primary-100 hover:bg-primary-800 hover:text-white' }} group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200">
        <div class="nav-icon mr-3 flex-shrink-0 h-6 w-6 flex items-center justify-center rounded-md bg-primary-800 {{ request()->routeIs('admin.schedules.*') ? 'bg-primary-600 text-white' : 'text-primary-300 group-hover:text-white' }} transition-colors duration-200">
            <i class="fas fa-calendar-alt"></i>
        </div>
        Schedules
    </a>

    <!-- Bookings -->
    <a href="{{ route('admin.bookings.index') }}"
       class="nav-item {{ request()->routeIs('admin.bookings.*') ? 'bg-primary-800 text-white' : 'text-primary-100 hover:bg-primary-800 hover:text-white' }} group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200">
        <div class="nav-icon mr-3 flex-shrink-0 h-6 w-6 flex items-center justify-center rounded-md bg-primary-800 {{ request()->routeIs('admin.bookings.*') ? 'bg-primary-600 text-white' : 'text-primary-300 group-hover:text-white' }} transition-colors duration-200">
            <i class="fas fa-ticket-alt"></i>
        </div>
        Bookings
        @if(isset($pendingBookingsCount) && $pendingBookingsCount > 0)
        <span class="ml-auto inline-block py-0.5 px-2 text-xs rounded-full bg-yellow-500 text-white animate-pulse-slow">
            {{ $pendingBookingsCount }}
        </span>
        @endif
    </a>

    <!-- Reports Dropdown -->
    <div x-data="{ open: {{ request()->routeIs('admin.reports.*') ? 'true' : 'false' }} }" class="space-y-1">
        <button @click="open = !open" type="button"
                class="nav-item w-full text-primary-100 hover:bg-primary-800 hover:text-white group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200">
            <div class="nav-icon mr-3 flex-shrink-0 h-6 w-6 flex items-center justify-center rounded-md bg-primary-800 text-primary-300 group-hover:text-white transition-colors duration-200">
                <i class="fas fa-chart-bar"></i>
            </div>
            <span class="flex-1">Reports</span>
            <svg class="text-primary-300 ml-3 flex-shrink-0 h-5 w-5 transform transition-transform duration-200"
                 x-bind:class="{'rotate-90': open}"
                 viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
            </svg>
        </button>

        <div x-show="open"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-1"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-1"
             class="ml-8 border-l-2 border-primary-700 pl-3 space-y-1">

            <a href="{{ route('admin.reports.daily') }}"
               class="nav-item group flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('admin.reports.daily') ? 'text-white bg-primary-700' : 'text-primary-200 hover:text-white hover:bg-primary-700' }} transition-all duration-200">
                <i class="far fa-calendar-check mr-3 text-primary-300 group-hover:text-white"></i>
                Daily Report
            </a>

            <a href="{{ route('admin.reports.monthly') }}"
               class="nav-item group flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('admin.reports.monthly') ? 'text-white bg-primary-700' : 'text-primary-200 hover:text-white hover:bg-primary-700' }} transition-all duration-200">
                <i class="far fa-calendar-alt mr-3 text-primary-300 group-hover:text-white"></i>
                Monthly Report
            </a>

            <a href="{{ route('admin.reports.routes') }}"
               class="nav-item group flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('admin.reports.routes') ? 'text-white bg-primary-700' : 'text-primary-200 hover:text-white hover:bg-primary-700' }} transition-all duration-200">
                <i class="fas fa-map-marked-alt mr-3 text-primary-300 group-hover:text-white"></i>
                Route Report
            </a>

            <a href="{{ route('admin.reports.occupancy') }}"
               class="nav-item group flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('admin.reports.occupancy') ? 'text-white bg-primary-700' : 'text-primary-200 hover:text-white hover:bg-primary-700' }} transition-all duration-200">
                <i class="fas fa-percentage mr-3 text-primary-300 group-hover:text-white"></i>
                Occupancy Report
            </a>
        </div>
    </div>

    <!-- Divider -->
    <div class="border-t border-primary-800 my-4"></div>

    <!-- Settings -->
    <a href="{{ route('admin.settings') }}"
       class="nav-item {{ request()->routeIs('admin.settings') || request()->routeIs('admin.settings.*') ? 'bg-primary-800 text-white' : 'text-primary-100 hover:bg-primary-800 hover:text-white' }} group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200">
        <div class="nav-icon mr-3 flex-shrink-0 h-6 w-6 flex items-center justify-center rounded-md bg-primary-800 {{ request()->routeIs('admin.settings') || request()->routeIs('admin.settings.*') ? 'bg-primary-600 text-white' : 'text-primary-300 group-hover:text-white' }} transition-colors duration-200">
            <i class="fas fa-cog"></i>
        </div>
        Settings
    </a>

    <!-- Help -->
    <a href="{{ route('admin.help') }}"
       class="nav-item {{ request()->routeIs('admin.help') || request()->routeIs('admin.help.*') ? 'bg-primary-800 text-white' : 'text-primary-100 hover:bg-primary-800 hover:text-white' }} group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200">
        <div class="nav-icon mr-3 flex-shrink-0 h-6 w-6 flex items-center justify-center rounded-md bg-primary-800 {{ request()->routeIs('admin.help') || request()->routeIs('admin.help.*') ? 'bg-primary-600 text-white' : 'text-primary-300 group-hover:text-white' }} transition-colors duration-200">
            <i class="fas fa-question-circle"></i>
        </div>
        Help
    </a>
</nav>
