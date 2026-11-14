import { queryParams, type RouteQueryOptions, type RouteDefinition } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\Teacher\Calendar\CreateEventController::__invoke
* @see app/Http/Controllers/Teacher/Calendar/CreateEventController.php:22
* @route '/teacher/calendar/events'
*/
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/teacher/calendar/events',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Teacher\Calendar\CreateEventController::__invoke
* @see app/Http/Controllers/Teacher/Calendar/CreateEventController.php:22
* @route '/teacher/calendar/events'
*/
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Teacher\Calendar\CreateEventController::__invoke
* @see app/Http/Controllers/Teacher/Calendar/CreateEventController.php:22
* @route '/teacher/calendar/events'
*/
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

const events = {
    store: Object.assign(store, store),
}

export default events