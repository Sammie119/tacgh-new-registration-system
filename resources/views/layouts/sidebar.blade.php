@php
    use App\Enums\RolesEnum;
@endphp

<!-- ======= Sidebar ======= -->
<aside id="sidebar" class="sidebar">

    <ul class="sidebar-nav" id="sidebar-nav">

        <x-menu-main
            title="Dashboard"
            route="dashboard"
            type="single"
            icon="bi bi-grid"
        />

        <li class="nav-heading">Menu</li>

        @if(use_roles_sidebar(RolesEnum::ROOMALLOCATOR) || use_roles_sidebar(RolesEnum::SYSTEMADMIN) || use_roles_sidebar(RolesEnum::SUPERADMIN))
            <x-menu-main
                title="All Registrants"
                route="all_registrant"
                type="single"
                icon="bi bi-people"
            />

            <x-menu-main
                title="Room Allocation"
                route="allocate_room"
                type="single"
                icon="ri-hotel-bed-fill fs-5"
            />
        @endif

        @if(use_roles_sidebar(RolesEnum::FINANCE) || use_roles_sidebar(RolesEnum::SYSTEMADMIN) || use_roles_sidebar(RolesEnum::SUPERADMIN))
            <x-menu-main
                title="Finance"
                type="multi"
                icon="bi bi-cash-coin"
                id="finance-nav"
                :routes_array="['payments', 'financial_entries', 'financial_report']"
            >
                <x-menu-item route="payments" title="Online Payment" />

                <x-menu-item route="financial_entries" title="Financial Entries" />

                <x-menu-item route="financial_report" title="Financial Report" />

            </x-menu-main>
        @endif

        @if(use_roles_sidebar(RolesEnum::SYSTEMDEVELOPER) || use_roles_sidebar(RolesEnum::SYSTEMADMIN) || use_roles_sidebar(RolesEnum::SUPERADMIN))
            <x-menu-main
                title="Forms"
                route="forms"
                type="single"
                icon="bi bi-journal-text"
            />

            <x-menu-main
                title="System Admin"
                type="multi"
                icon="bi bi-gear-fill"
                id="sys-admin-nav"
                :routes_array="['users', 'venues', 'events', 'roles', 'permissions', 'categories', 'accommodations', 'room', 'downloads']"
            >
                @if(use_roles_sidebar(RolesEnum::SYSTEMDEVELOPER))
                    <x-menu-item route="users" title="User Management" />

                    <x-menu-item route="venues" title="Venue Setup" />

                    <x-menu-item route="events" title="Events" />

                    <x-menu-item route="categories" title="Lookups" />
                @endif

                <x-menu-item route="downloads" title="Downloads" />

                @if(use_roles_sidebar(RolesEnum::SYSTEMDEVELOPER))

                    <x-menu-item route="roles" title="Roles" />

                    <x-menu-item route="permissions" title="Permissions" />
                @endif

            </x-menu-main>
        @endif

    </ul>

</aside><!-- End Sidebar-->
