import {
    queryParams,
    type RouteQueryOptions,
    type RouteDefinition,
} from './../../wayfinder';
/**
 * @see \App\Http\Controllers\AuthController::submit
 * @see app/Http/Controllers/AuthController.php:23
 * @route '/login'
 */
export const submit = (
    options?: RouteQueryOptions,
): RouteDefinition<'post'> => ({
    url: submit.url(options),
    method: 'post',
});

submit.definition = {
    methods: ['post'],
    url: '/login',
} satisfies RouteDefinition<['post']>;

/**
 * @see \App\Http\Controllers\AuthController::submit
 * @see app/Http/Controllers/AuthController.php:23
 * @route '/login'
 */
submit.url = (options?: RouteQueryOptions) => {
    return submit.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\AuthController::submit
 * @see app/Http/Controllers/AuthController.php:23
 * @route '/login'
 */
submit.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: submit.url(options),
    method: 'post',
});

/**
 * @see \App\Http\Controllers\AuthController::logout
 * @see app/Http/Controllers/AuthController.php:35
 * @route '/logout'
 */
export const logout = (
    options?: RouteQueryOptions,
): RouteDefinition<'post'> => ({
    url: logout.url(options),
    method: 'post',
});

logout.definition = {
    methods: ['post'],
    url: '/logout',
} satisfies RouteDefinition<['post']>;

/**
 * @see \App\Http\Controllers\AuthController::logout
 * @see app/Http/Controllers/AuthController.php:35
 * @route '/logout'
 */
logout.url = (options?: RouteQueryOptions) => {
    return logout.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\AuthController::logout
 * @see app/Http/Controllers/AuthController.php:35
 * @route '/logout'
 */
logout.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: logout.url(options),
    method: 'post',
});

const login = {
    submit: Object.assign(submit, submit),
    logout: Object.assign(logout, logout),
};

export default login;
