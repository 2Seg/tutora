import { queryParams, type RouteQueryOptions, type RouteDefinition } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\Teacher\Calendar\ShowCalendarController::__invoke
* @see app/Http/Controllers/Teacher/Calendar/ShowCalendarController.php:22
* @route '/teacher/calendar'
*/
export const show = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/teacher/calendar',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Teacher\Calendar\ShowCalendarController::__invoke
* @see app/Http/Controllers/Teacher/Calendar/ShowCalendarController.php:22
* @route '/teacher/calendar'
*/
show.url = (options?: RouteQueryOptions) => {
    return show.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Teacher\Calendar\ShowCalendarController::__invoke
* @see app/Http/Controllers/Teacher/Calendar/ShowCalendarController.php:22
* @route '/teacher/calendar'
*/
show.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Teacher\Calendar\ShowCalendarController::__invoke
* @see app/Http/Controllers/Teacher/Calendar/ShowCalendarController.php:22
* @route '/teacher/calendar'
*/
show.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Teacher\Calendar\CreateEventController::__invoke
* @see app/Http/Controllers/Teacher/Calendar/CreateEventController.php:19
* @route '/teacher/calendar'
*/
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/teacher/calendar',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Teacher\Calendar\CreateEventController::__invoke
* @see app/Http/Controllers/Teacher/Calendar/CreateEventController.php:19
* @route '/teacher/calendar'
*/
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Teacher\Calendar\CreateEventController::__invoke
* @see app/Http/Controllers/Teacher/Calendar/CreateEventController.php:19
* @route '/teacher/calendar'
*/
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

const calendar = {
    show: Object.assign(show, show),
    store: Object.assign(store, store),
}

export default calendar