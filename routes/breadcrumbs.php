<?php

use App\Models\Members;
use App\Models\User;
use Diglactic\Breadcrumbs\Breadcrumbs;
use Diglactic\Breadcrumbs\Generator as BreadcrumbTrail;
use Spatie\Permission\Models\Role;

// Home
Breadcrumbs::for('home', function (BreadcrumbTrail $trail) {
    $trail->push('Home', route('dashboard'));
});

// Home > Dashboard
Breadcrumbs::for('dashboard', function (BreadcrumbTrail $trail) {
    $trail->parent('home');
    $trail->push('Dashboard', route('dashboard'));
});

// Home > Dashboard > User Management
Breadcrumbs::for('user-management.index', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push('User Management', route('user-management.users.index'));
});

// Home > Dashboard > User Management > Users
Breadcrumbs::for('user-management.users.index', function (BreadcrumbTrail $trail) {
    $trail->parent('user-management.index');
    $trail->push('Users', route('user-management.users.index'));
});

// Home > Dashboard > User Management > Users > [User]
Breadcrumbs::for('user-management.users.show', function (BreadcrumbTrail $trail, User $user) {
    $trail->parent('user-management.users.index');
    $trail->push(ucwords($user->name), route('user-management.users.show', $user));
});

// Home > Dashboard > User Management > Roles
Breadcrumbs::for('user-management.roles.index', function (BreadcrumbTrail $trail) {
    $trail->parent('user-management.index');
    $trail->push('Roles', route('user-management.roles.index'));
});

// Home > Dashboard > User Management > Roles > [Role]
Breadcrumbs::for('user-management.roles.show', function (BreadcrumbTrail $trail, Role $role) {
    $trail->parent('user-management.roles.index');
    $trail->push(ucwords($role->name), route('user-management.roles.show', $role));
});

// Home > Dashboard > User Management > Permission
Breadcrumbs::for('user-management.permissions.index', function (BreadcrumbTrail $trail) {
    $trail->parent('user-management.index');
    $trail->push('Permissions', route('user-management.permissions.index'));
});


// Home > Dashboard > User Management > Teams
Breadcrumbs::for('user-management.teams.index', function (BreadcrumbTrail $trail) {
    $trail->parent('user-management.index');
    $trail->push('Teams', route('user-management.teams.index'));
});

// Home > Dashboard
Breadcrumbs::for('members', function (BreadcrumbTrail $trail) {
    $trail->parent('home');
    $trail->push('List Members', route('members.index'));
});

// Home > Dashboard > Members
Breadcrumbs::for('members.index', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard'); // or 'home' depending on your setup
    $trail->push('Members', route('members.index'));
});

// Home > Dashboard > Members > [Member]
Breadcrumbs::for('members.show', function (BreadcrumbTrail $trail, $member) {
    // Always fetch withTrashed in case it's deleted
    $member = \App\Models\Members::withTrashed()->findOrFail($member->id ?? $member);

    $trail->parent('members.index');
    $trail->push(ucwords($member->name), route('members.show', $member->id));
});


// Home > Dashboard > Members
Breadcrumbs::for('transactions', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard'); // or 'home' depending on your setup
    $trail->push('Transactions', route('transactions.index'));
});

// Home > Dashboard > Transactions
Breadcrumbs::for('transactions.index', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard'); // or 'home' depending on your setup
    $trail->push('Transactions', route('transactions.index'));
});

// Home > Dashboard
Breadcrumbs::for('followup', function (BreadcrumbTrail $trail) {
    $trail->parent('home');
    $trail->push('List Follow Ups', route('followup.index'));
});

// Home > Dashboard > Follow Ups
Breadcrumbs::for('followup.index', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard'); // or 'home' depending on your setup
    $trail->push('Follow Ups', route('followup.index'));
});

// Home > Dashboard > Activity Logs
Breadcrumbs::for('logs.index', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard'); // or 'home' depending on your setup
    $trail->push('Activity Logs', route('logs.index'));
});

// Home > Dashboard > Import Logs
Breadcrumbs::for('import.index', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard'); // or 'home' depending on your setup
    $trail->push('Import Logs', route('import.index'));
});

// Home > Dashboard > Import Logs
Breadcrumbs::for('summary.index', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard'); // or 'home' depending on your setup
    $trail->push('Summary Report', route('summary.index'));
});
