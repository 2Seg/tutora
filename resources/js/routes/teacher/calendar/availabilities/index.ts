import { queryParams, type RouteQueryOptions, type RouteDefinition } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\Teacher\Calendar\UpdateAvailabilitiesController::__invoke
* @see app/Http/Controllers/Teacher/Calendar/UpdateAvailabilitiesController.php:19
* @route '/teacher/calendar/availabilities'
*/
export const update = (options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(options),
    method: 'put',
})

update.definition = {
    methods: ["put"],
    url: '/teacher/calendar/availabilities',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\Teacher\Calendar\UpdateAvailabilitiesController::__invoke
* @see app/Http/Controllers/Teacher/Calendar/UpdateAvailabilitiesController.php:19
* @route '/teacher/calendar/availabilities'
*/
update.url = (options?: RouteQueryOptions) => {
    return update.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Teacher\Calendar\UpdateAvailabilitiesController::__invoke
* @see app/Http/Controllers/Teacher/Calendar/UpdateAvailabilitiesController.php:19
* @route '/teacher/calendar/availabilities'
*/
update.put = (options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(options),
    method: 'put',
})

const availabilities = {
    update: Object.assign(update, update),
}

export default availabilities