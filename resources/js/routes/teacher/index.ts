import { queryParams, type RouteQueryOptions, type RouteDefinition } from './../../wayfinder'
/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/teacher'
*/
export const dashboard = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: dashboard.url(options),
    method: 'get',
})

dashboard.definition = {
    methods: ["get","head"],
    url: '/teacher',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/teacher'
*/
dashboard.url = (options?: RouteQueryOptions) => {
    return dashboard.definition.url + queryParams(options)
}

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/teacher'
*/
dashboard.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: dashboard.url(options),
    method: 'get',
})

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/teacher'
*/
dashboard.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: dashboard.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Teacher\StudentController::students
* @see app/Http/Controllers/Teacher/StudentController.php:19
* @route '/teacher/students'
*/
export const students = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: students.url(options),
    method: 'get',
})

students.definition = {
    methods: ["get","head"],
    url: '/teacher/students',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Teacher\StudentController::students
* @see app/Http/Controllers/Teacher/StudentController.php:19
* @route '/teacher/students'
*/
students.url = (options?: RouteQueryOptions) => {
    return students.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Teacher\StudentController::students
* @see app/Http/Controllers/Teacher/StudentController.php:19
* @route '/teacher/students'
*/
students.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: students.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Teacher\StudentController::students
* @see app/Http/Controllers/Teacher/StudentController.php:19
* @route '/teacher/students'
*/
students.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: students.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Teacher\EventController::calendar
* @see app/Http/Controllers/Teacher/EventController.php:19
* @route '/teacher/calendar'
*/
export const calendar = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: calendar.url(options),
    method: 'get',
})

calendar.definition = {
    methods: ["get","head"],
    url: '/teacher/calendar',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Teacher\EventController::calendar
* @see app/Http/Controllers/Teacher/EventController.php:19
* @route '/teacher/calendar'
*/
calendar.url = (options?: RouteQueryOptions) => {
    return calendar.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Teacher\EventController::calendar
* @see app/Http/Controllers/Teacher/EventController.php:19
* @route '/teacher/calendar'
*/
calendar.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: calendar.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Teacher\EventController::calendar
* @see app/Http/Controllers/Teacher/EventController.php:19
* @route '/teacher/calendar'
*/
calendar.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: calendar.url(options),
    method: 'head',
})

const teacher = {
    dashboard: Object.assign(dashboard, dashboard),
    students: Object.assign(students, students),
    calendar: Object.assign(calendar, calendar),
}

export default teacher