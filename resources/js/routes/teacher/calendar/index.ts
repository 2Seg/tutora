import { queryParams, type RouteQueryOptions, type RouteDefinition } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\Teacher\Event\ListEventsController::__invoke
* @see app/Http/Controllers/Teacher/Event/ListEventsController.php:22
* @route '/teacher/calendar'
*/
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/teacher/calendar',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Teacher\Event\ListEventsController::__invoke
* @see app/Http/Controllers/Teacher/Event/ListEventsController.php:22
* @route '/teacher/calendar'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Teacher\Event\ListEventsController::__invoke
* @see app/Http/Controllers/Teacher/Event/ListEventsController.php:22
* @route '/teacher/calendar'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Teacher\Event\ListEventsController::__invoke
* @see app/Http/Controllers/Teacher/Event/ListEventsController.php:22
* @route '/teacher/calendar'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Teacher\Event\CreateEventController::__invoke
* @see app/Http/Controllers/Teacher/Event/CreateEventController.php:19
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
* @see \App\Http\Controllers\Teacher\Event\CreateEventController::__invoke
* @see app/Http/Controllers/Teacher/Event/CreateEventController.php:19
* @route '/teacher/calendar'
*/
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Teacher\Event\CreateEventController::__invoke
* @see app/Http/Controllers/Teacher/Event/CreateEventController.php:19
* @route '/teacher/calendar'
*/
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

const calendar = {
    index: Object.assign(index, index),
    store: Object.assign(store, store),
}

export default calendar