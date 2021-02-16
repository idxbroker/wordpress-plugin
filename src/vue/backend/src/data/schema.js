import { schema } from 'normalizr'

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
    'routes',
    { routes: [pagesEntity] },
    {
        idAttribute: 'itemId'
    }
)

export default [categoriesEntity]
