/**
 * Copyright (c) 2022 Strategio Digital s.r.o.
 * @author Jiří Zapletal (https://strategio.dev, jz@strategio.dev)
 */

// Core app
import { createApp } from 'vue'
import App from '@/saas/App.vue'
import { createSaas } from '@/saas/createSaas'

// Customizable globals
import navbar from '@/saas/globals/navbar'
import routes from '@/saas/globals/routes'
import modals from '@/saas/globals/datagrid/modals'
import columns from '@/saas/globals/datagrid/columns'
import actions from '@/saas/globals/datagrid/actions'
import summaries from '@/saas/globals/collection/summaries'

// Custom routes
// const exclude = ['Users']
// const customRoutes = routes.filter(route => !exclude.includes(route.name as string))
// customRoutes.push(
//     {
//         path: '/users',
//         name: 'Users',
//         component: () => import(/* webpackChunkName: "users" */ '@/assets/vue/saas/views/users/Users.vue')
//     },
//     {
//         path: '/users/:id',
//         name: 'UserDetail',
//         component: () => import(/* webpackChunkName: "users" */ '@/assets/vue/saas/views/users/Detail.vue')
//     }
// )

// Custom navbar
// navbar.items.push(
//     { title: 'Uživatelé', activePrefix: '/users', icon: 'mdi-account-multiple', route: { name:  'Users' } }
// )

const app: HTMLElement | null = document.getElementById('app-saas')

if (app) {
    const appPath = app.dataset.appPath as string
    const appVersions = JSON.parse(app.dataset.appVersions as string);

    const saas = createSaas({
        root: appPath,
        versions: appVersions,
        routes,
        navbar,
        datagrid: {
            modals,
            columns,
            actions,
        },
        collection: {
            summaries
        }
    })

    createApp(App).use(saas).mount(app)
}