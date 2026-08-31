import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults, validateParameters } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\SiteEditorController::edit
 * @see app/Http/Controllers/SiteEditorController.php:17
 * @route '/projects/{project}/editor'
 */
export const edit = (args: { project: number | { id: number } } | [project: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})

edit.definition = {
    methods: ["get","head"],
    url: '/projects/{project}/editor',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\SiteEditorController::edit
 * @see app/Http/Controllers/SiteEditorController.php:17
 * @route '/projects/{project}/editor'
 */
edit.url = (args: { project: number | { id: number } } | [project: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { project: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { project: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    project: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        project: typeof args.project === 'object'
                ? args.project.id
                : args.project,
                }

    return edit.definition.url
            .replace('{project}', parsedArgs.project.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\SiteEditorController::edit
 * @see app/Http/Controllers/SiteEditorController.php:17
 * @route '/projects/{project}/editor'
 */
edit.get = (args: { project: number | { id: number } } | [project: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\SiteEditorController::edit
 * @see app/Http/Controllers/SiteEditorController.php:17
 * @route '/projects/{project}/editor'
 */
edit.head = (args: { project: number | { id: number } } | [project: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: edit.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\SiteEditorController::updateSpec
 * @see app/Http/Controllers/SiteEditorController.php:34
 * @route '/projects/{project}/spec'
 */
export const updateSpec = (args: { project: number | { id: number } } | [project: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: updateSpec.url(args, options),
    method: 'put',
})

updateSpec.definition = {
    methods: ["put"],
    url: '/projects/{project}/spec',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\SiteEditorController::updateSpec
 * @see app/Http/Controllers/SiteEditorController.php:34
 * @route '/projects/{project}/spec'
 */
updateSpec.url = (args: { project: number | { id: number } } | [project: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { project: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { project: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    project: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        project: typeof args.project === 'object'
                ? args.project.id
                : args.project,
                }

    return updateSpec.definition.url
            .replace('{project}', parsedArgs.project.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\SiteEditorController::updateSpec
 * @see app/Http/Controllers/SiteEditorController.php:34
 * @route '/projects/{project}/spec'
 */
updateSpec.put = (args: { project: number | { id: number } } | [project: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: updateSpec.url(args, options),
    method: 'put',
})

/**
* @see \App\Http\Controllers\SiteEditorController::updateCode
 * @see app/Http/Controllers/SiteEditorController.php:76
 * @route '/projects/{project}/code'
 */
export const updateCode = (args: { project: number | { id: number } } | [project: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: updateCode.url(args, options),
    method: 'put',
})

updateCode.definition = {
    methods: ["put"],
    url: '/projects/{project}/code',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\SiteEditorController::updateCode
 * @see app/Http/Controllers/SiteEditorController.php:76
 * @route '/projects/{project}/code'
 */
updateCode.url = (args: { project: number | { id: number } } | [project: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { project: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { project: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    project: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        project: typeof args.project === 'object'
                ? args.project.id
                : args.project,
                }

    return updateCode.definition.url
            .replace('{project}', parsedArgs.project.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\SiteEditorController::updateCode
 * @see app/Http/Controllers/SiteEditorController.php:76
 * @route '/projects/{project}/code'
 */
updateCode.put = (args: { project: number | { id: number } } | [project: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: updateCode.url(args, options),
    method: 'put',
})

/**
* @see \App\Http\Controllers\SiteEditorController::preview
 * @see app/Http/Controllers/SiteEditorController.php:118
 * @route '/preview/{project}/{path?}'
 */
export const preview = (args: { project: number | { id: number }, path?: string | number } | [project: number | { id: number }, path: string | number ], options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: preview.url(args, options),
    method: 'get',
})

preview.definition = {
    methods: ["get","head"],
    url: '/preview/{project}/{path?}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\SiteEditorController::preview
 * @see app/Http/Controllers/SiteEditorController.php:118
 * @route '/preview/{project}/{path?}'
 */
preview.url = (args: { project: number | { id: number }, path?: string | number } | [project: number | { id: number }, path: string | number ], options?: RouteQueryOptions) => {
    if (Array.isArray(args)) {
        args = {
                    project: args[0],
                    path: args[1],
                }
    }

    args = applyUrlDefaults(args)

    validateParameters(args, [
            "path",
        ])

    const parsedArgs = {
                        project: typeof args.project === 'object'
                ? args.project.id
                : args.project,
                                path: args.path,
                }

    return preview.definition.url
            .replace('{project}', parsedArgs.project.toString())
            .replace('{path?}', parsedArgs.path?.toString() ?? '')
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\SiteEditorController::preview
 * @see app/Http/Controllers/SiteEditorController.php:118
 * @route '/preview/{project}/{path?}'
 */
preview.get = (args: { project: number | { id: number }, path?: string | number } | [project: number | { id: number }, path: string | number ], options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: preview.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\SiteEditorController::preview
 * @see app/Http/Controllers/SiteEditorController.php:118
 * @route '/preview/{project}/{path?}'
 */
preview.head = (args: { project: number | { id: number }, path?: string | number } | [project: number | { id: number }, path: string | number ], options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: preview.url(args, options),
    method: 'head',
})
const SiteEditorController = { edit, updateSpec, updateCode, preview }

export default SiteEditorController