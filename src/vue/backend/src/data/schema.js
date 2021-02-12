import { schema } from 'normalizr'
import { navigationModelTranslation as settings } from '../settings'

const subpagesEntity = new schema.Entity(
    'routes',
    {},
    {
        idAttribute: 'itemId'
    }
)

const pagesEntity = new schema.Entity(
    'routes',
    { routes: [subpagesEntity] },
    {
        idAttribute: 'itemId'
    }
)

const categoriesEntity = new schema.Entity(
    settings.categories.key,
    { routes: [pagesEntity] },
    {
        idAttribute: 'itemId'
    }
)

export default [categoriesEntity]
