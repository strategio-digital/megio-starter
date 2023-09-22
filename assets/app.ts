/**
 * Copyright (c) 2022 Strategio Digital s.r.o.
 * @author Jiří Zapletal (https://strategio.dev, jz@strategio.dev)
 */

// Static files
import '@/assets/img/strategio.svg'
import '@/assets/img/favicon.svg'
import '@/assets/img/favicon.png'

// Stylesheets
import '@/assets/scss/layout.scss'

// Typescript example
import removeThis from '@/assets/ts/removeThis'
console.log(removeThis().data())

// VueJS example
import { createApp } from 'vue'
import App from '@/assets/vue/app/App.vue'
createApp(App).mount('#vue-app')