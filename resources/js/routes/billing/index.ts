import { queryParams, type RouteQueryOptions, type RouteDefinition } from './../../wayfinder'
/**
* @see \App\Http\Controllers\BillingController::subscribe
 * @see app/Http/Controllers/BillingController.php:29
 * @route '/billing/subscribe'
 */
export const subscribe = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: subscribe.url(options),
    method: 'post',
})

subscribe.definition = {
    methods: ["post"],
    url: '/billing/subscribe',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\BillingController::subscribe
 * @see app/Http/Controllers/BillingController.php:29
 * @route '/billing/subscribe'
 */
subscribe.url = (options?: RouteQueryOptions) => {
    return subscribe.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\BillingController::subscribe
 * @see app/Http/Controllers/BillingController.php:29
 * @route '/billing/subscribe'
 */
subscribe.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: subscribe.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\BillingController::portal
 * @see app/Http/Controllers/BillingController.php:53
 * @route '/billing/portal'
 */
export const portal = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: portal.url(options),
    method: 'get',
})

portal.definition = {
    methods: ["get","head"],
    url: '/billing/portal',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\BillingController::portal
 * @see app/Http/Controllers/BillingController.php:53
 * @route '/billing/portal'
 */
portal.url = (options?: RouteQueryOptions) => {
    return portal.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\BillingController::portal
 * @see app/Http/Controllers/BillingController.php:53
 * @route '/billing/portal'
 */
portal.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: portal.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\BillingController::portal
 * @see app/Http/Controllers/BillingController.php:53
 * @route '/billing/portal'
 */
portal.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: portal.url(options),
    method: 'head',
})
const billing = {
    subscribe: Object.assign(subscribe, subscribe),
portal: Object.assign(portal, portal),
}

export default billing