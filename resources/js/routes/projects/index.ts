import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults, validateParameters } from './../../wayfinder'
import spec from './spec'
import code from './code'
/**
* @see \App\Http\Controllers\ProjectController::index
 * @see app/Http/Controllers/ProjectController.php:20
 * @route '/projects'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/projects',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\ProjectController::index
 * @see app/Http/Controllers/ProjectController.php:20
 * @route '/projects'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\ProjectController::index
 * @see app/Http/Controllers/ProjectController.php:20
 * @route '/projects'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\ProjectController::index
 * @see app/Http/Controllers/ProjectController.php:20
 * @route '/projects'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\ProjectController::store
 * @see app/Http/Controllers/ProjectController.php:42
 * @route '/projects'
 */
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/projects',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\ProjectController::store
 * @see app/Http/Controllers/ProjectController.php:42
 * @route '/projects'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\ProjectController::store
 * @see app/Http/Controllers/ProjectController.php:42
 * @route '/projects'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\ProjectController::models
 * @see app/Http/Controllers/ProjectController.php:37
 * @route '/projects/models'
 */
export const models = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: models.url(options),
    method: 'get',
})

models.definition = {
    methods: ["get","head"],
    url: '/projects/models',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\ProjectController::models
 * @see app/Http/Controllers/ProjectController.php:37
 * @route '/projects/models'
 */
models.url = (options?: RouteQueryOptions) => {
    return models.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\ProjectController::models
 * @see app/Http/Controllers/ProjectController.php:37
 * @route '/projects/models'
 */
models.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: models.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\ProjectController::models
 * @see app/Http/Controllers/ProjectController.php:37
 * @route '/projects/models'
 */
models.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: models.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\ProjectController::show
 * @see app/Http/Controllers/ProjectController.php:86
 * @route '/projects/{project}'
 */
export const show = (args: { project: number | { id: number } } | [project: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/projects/{project}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\ProjectController::show
 * @see app/Http/Controllers/ProjectController.php:86
 * @route '/projects/{project}'
 */
show.url = (args: { project: number | { id: number } } | [project: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return show.definition.url
            .replace('{project}', parsedArgs.project.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\ProjectController::show
 * @see app/Http/Controllers/ProjectController.php:86
 * @route '/projects/{project}'
 */
show.get = (args: { project: number | { id: number } } | [project: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\ProjectController::show
 * @see app/Http/Controllers/ProjectController.php:86
 * @route '/projects/{project}'
 */
show.head = (args: { project: number | { id: number } } | [project: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\ProjectController::destroy
 * @see app/Http/Controllers/ProjectController.php:107
 * @route '/projects/{project}'
 */
export const destroy = (args: { project: number | { id: number } } | [project: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/projects/{project}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\ProjectController::destroy
 * @see app/Http/Controllers/ProjectController.php:107
 * @route '/projects/{project}'
 */
destroy.url = (args: { project: number | { id: number } } | [project: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return destroy.definition.url
            .replace('{project}', parsedArgs.project.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\ProjectController::destroy
 * @see app/Http/Controllers/ProjectController.php:107
 * @route '/projects/{project}'
 */
destroy.delete = (args: { project: number | { id: number } } | [project: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

/**
* @see \App\Http\Controllers\SiteEditorController::editor
 * @see app/Http/Controllers/SiteEditorController.php:17
 * @route '/projects/{project}/editor'
 */
export const editor = (args: { project: number | { id: number } } | [project: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: editor.url(args, options),
    method: 'get',
})

editor.definition = {
    methods: ["get","head"],
    url: '/projects/{project}/editor',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\SiteEditorController::editor
 * @see app/Http/Controllers/SiteEditorController.php:17
 * @route '/projects/{project}/editor'
 */
editor.url = (args: { project: number | { id: number } } | [project: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return editor.definition.url
            .replace('{project}', parsedArgs.project.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\SiteEditorController::editor
 * @see app/Http/Controllers/SiteEditorController.php:17
 * @route '/projects/{project}/editor'
 */
editor.get = (args: { project: number | { id: number } } | [project: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: editor.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\SiteEditorController::editor
 * @see app/Http/Controllers/SiteEditorController.php:17
 * @route '/projects/{project}/editor'
 */
editor.head = (args: { project: number | { id: number } } | [project: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: editor.url(args, options),
    method: 'head',
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
const projects = {
    index: Object.assign(index, index),
store: Object.assign(store, store),
models: Object.assign(models, models),
show: Object.assign(show, show),
destroy: Object.assign(destroy, destroy),
editor: Object.assign(editor, editor),
spec: Object.assign(spec, spec),
code: Object.assign(code, code),
preview: Object.assign(preview, preview),
}

export default projects