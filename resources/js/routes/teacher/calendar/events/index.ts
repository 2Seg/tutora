import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults } from './../../../../wayfinder'
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

/**
* @see \App\Http\Controllers\Teacher\Calendar\UpdateEventController::__invoke
* @see app/Http/Controllers/Teacher/Calendar/UpdateEventController.php:22
* @route '/teacher/calendar/events/{event}'
*/
export const update = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

update.definition = {
    methods: ["patch"],
    url: '/teacher/calendar/events/{event}',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\Teacher\Calendar\UpdateEventController::__invoke
* @see app/Http/Controllers/Teacher/Calendar/UpdateEventController.php:22
* @route '/teacher/calendar/events/{event}'
*/
update.url = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { event: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { event: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            event: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        event: typeof args.event === 'object'
        ? args.event.id
        : args.event,
    }

    return update.definition.url
            .replace('{event}', parsedArgs.event.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Teacher\Calendar\UpdateEventController::__invoke
* @see app/Http/Controllers/Teacher/Calendar/UpdateEventController.php:22
* @route '/teacher/calendar/events/{event}'
*/
update.patch = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

/**
* @see \App\Http\Controllers\Teacher\Calendar\DeleteEventController::__invoke
* @see app/Http/Controllers/Teacher/Calendar/DeleteEventController.php:21
* @route '/teacher/calendar/events/{event}'
*/
export const deleteMethod = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: deleteMethod.url(args, options),
    method: 'delete',
})

deleteMethod.definition = {
    methods: ["delete"],
    url: '/teacher/calendar/events/{event}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Teacher\Calendar\DeleteEventController::__invoke
* @see app/Http/Controllers/Teacher/Calendar/DeleteEventController.php:21
* @route '/teacher/calendar/events/{event}'
*/
deleteMethod.url = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { event: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { event: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            event: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        event: typeof args.event === 'object'
        ? args.event.id
        : args.event,
    }

    return deleteMethod.definition.url
            .replace('{event}', parsedArgs.event.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Teacher\Calendar\DeleteEventController::__invoke
* @see app/Http/Controllers/Teacher/Calendar/DeleteEventController.php:21
* @route '/teacher/calendar/events/{event}'
*/
deleteMethod.delete = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: deleteMethod.url(args, options),
    method: 'delete',
})

const events = {
    store: Object.assign(store, store),
    update: Object.assign(update, update),
    delete: Object.assign(deleteMethod, deleteMethod),
}

export default events