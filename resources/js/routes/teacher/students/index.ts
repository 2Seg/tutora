import { queryParams, type RouteQueryOptions, type RouteDefinition } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\Teacher\Student\ListStudentsController::__invoke
* @see app/Http/Controllers/Teacher/Student/ListStudentsController.php:19
* @route '/teacher/students'
*/
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/teacher/students',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Teacher\Student\ListStudentsController::__invoke
* @see app/Http/Controllers/Teacher/Student/ListStudentsController.php:19
* @route '/teacher/students'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Teacher\Student\ListStudentsController::__invoke
* @see app/Http/Controllers/Teacher/Student/ListStudentsController.php:19
* @route '/teacher/students'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Teacher\Student\ListStudentsController::__invoke
* @see app/Http/Controllers/Teacher/Student/ListStudentsController.php:19
* @route '/teacher/students'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Teacher\Student\CreateStudentController::__invoke
* @see app/Http/Controllers/Teacher/Student/CreateStudentController.php:19
* @route '/teacher/students'
*/
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/teacher/students',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Teacher\Student\CreateStudentController::__invoke
* @see app/Http/Controllers/Teacher/Student/CreateStudentController.php:19
* @route '/teacher/students'
*/
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Teacher\Student\CreateStudentController::__invoke
* @see app/Http/Controllers/Teacher/Student/CreateStudentController.php:19
* @route '/teacher/students'
*/
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

const students = {
    index: Object.assign(index, index),
    store: Object.assign(store, store),
}

export default students