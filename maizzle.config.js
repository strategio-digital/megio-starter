/** @type {import('@maizzle/framework').Config} */

export default {
    build: {
        content: ['view/**/*.mail.latte'],
        output: {
            path: 'temp/latte-mail',
            extension: 'latte'
        },
    },
    components: {
        folders: [
            'view/mail/layout',
            'view/mail/component',
        ],
        tagPrefix: 'x-',
        fileExtension: 'html',
    },
    css: {
        tailwind: {
            config: './maizzle.tailwind.js',
        }
    },
    inlineCSS: true,
    removeUnusedCSS: true,
    shorthandCSS: true,
    prettify: true,
}
